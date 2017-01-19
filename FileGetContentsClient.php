<?php
namespace TAHClient;

use Twilio\Http\Client;
use Twilio\Http\Response;

/*
**  FileGetContentsClient
**
**  Reference implementation of Twilio's Client interface that uses file_get_contents
**  instead of using cURL.
**
*/
class FileGetContentsClient implements Client {
    const DEFAULT_HTTP_TIMEOUT=60;

    /*
    **  Request method, as required by the Client interface.  
    **
    **  @param string $method The HTTP method to use for the request
    **  @param string $url The URL for the request
    **  @param mixed[] $params (Optional) An array of URL parameters. Nullable
    **  @param mixed[] $data (Optional) An array for the request content. Nullable
    **  @param string[] $headers (Optional) An array for the request headers. Nullable
    **  @param string $useranme (Optional) The username for basic auth. Nullable
    **  @param string $password (Optional) The password for basic auth. Nullable
    **  @param float $timeout (Optional) The HTTP timeout in seconds — defaults to 60s
    **  
    **  @throws ErrorException if file_get_contents fails
    **
    **  @return Twilio\Http\Response Response for a successful request
    */   
    public function request($method, $url, $params=array(), $data=array(), 
        $headers=array(), $username=null, $password=null, $timeout=null){

        // Handle null cases to ignore
        $params=(is_null($params))?array():$params;
        $data=(is_null($data))?array():$data;
        $headers=(is_null($headers))?array():$headers;

        // Build an HTTP Context
        $context=stream_context_create(array('http'=>$this->buildOptionsArray($method, 
            $data, $headers, $username, $password, $timeout)));

        // If we don't have an empty $params object, we build a query string for it
        $url.=(empty($params) && !is_array($params))?''
            :'?'.$this->buildQueryString($params);

        $res=file_get_contents($url, FALSE, $context);

        if($res===FALSE)
            throw new \ErrorException('file_get_contents failed');

        // This is why I don't like file_get_contents — thankfully it's locally scoped
        $rawHeaders=$http_response_header;
        $headers=array();

        // The first entry of $http_response_header will always be the status code. This
        // extracts the numerical status code from there. We have to store this in a temp
        // variable because PHP 5.3.x doesnt support ()[] for array access.
        $sc=explode(" ","$rawHeaders[0] ");
        $statusCode=$sc[1];
        unset($rawHeaders[0]);

        // $http_response_header returns a numerical indexed array of headers broken out
        // by newline per value. We have to turn it into a header=>value array
        foreach($rawHeaders as $headerLine){
            // Only blow up the first : to avoid unnecessary explosions
            list($key, $value)=explode(':', $headerLine, 2);
            $headers[$key]=$value;
        }

        return new Response($statusCode, $res, $headers);

    }

    /*
    **  Builds the HTTP options array, as expected by stream_context_create
    **
    **  @param string $method The HTTP method to use for the request
    **  @param mixed[] $data (Optional) An array for the request content
    **  @param string[] $headers (Optional) An array for the request headers
    **  @param string $useranme (Optional) The username for a request with basic auth
    **  @param string $password (Optional) The password for a request with basic auth
    **  @param float $timeout (Optional) The HTTP timeout in seconds — defaults to 60s
    **
    **  @return array Array of options that's consumable by stream_context_create
    */
    public function buildOptionsArray($method, $data=array(), $headers=array(), 
        $username=null, $password=null, $timeout=null){

        $options=array();

        $options['header']=array();
        // PHP defaults to HTTP 1.0 for HTTP Contexts — Twilio uses 1.1 w/ CurlClient
        $options['protocol_version']=1.1;
        // Passes on the HTTP error to the user, rather than throwing an Exception
        $options['ignore_errors']=TRUE;

        $options['method']=strtoupper(trim($method));
        $options['timeout']=($timeout)?$timeout:self::DEFAULT_HTTP_TIMEOUT;

        // Add each header to headers array in Header: Value format
        foreach($headers as $k=>$v)
            $options['header'][]="$k: $v";

        // Add Authorization header if username AND password are present
        if($username && $password)
            $options['header'][] = 'Authorization: Basic '
                .base64_encode("$username:$password");

        // PHP 5.3 has an issue file_get_contents doesn't close the connection on its own,
        // so we have send a Connection: close header to force the connection to close
        if(version_compare(PHP_VERSION, '5.4.0', '<'))
            $options['header'][]='Connection: close';

        // This has the potential to have implications for memory usage for big files
        if($options['method']=='POST' || $options['method']=='PUT')
            $options['content']=$this->buildQueryString($data);

        return $options;
    }

    /*
    **  Builds a query string for fields, as required by PUT/POST bodies and URIs
    **
    **  @param mixed[] $params An array with the fields that need to be encoded
    **  
    **  @return string Query string with all fields appropriately encoded. Can be empty()
    */
    public function buildQueryString($params){
        $queryParts=array();

        // Takes care of the case of Truthy params as input to function
        if(!is_array($params))
            return "";

        foreach($params as $key=>$value){
            // Duplicate key handling. For paramDupe=array(1,2), Twilio requires 
            // paramDupe=1&paramDupe=2, instead of paramDupe=1%2C2
            if(is_array($value)){
                foreach($value as $dupeParam)
                    $queryParts[]=urlencode((string)$key).'='
                        .urlencode((string)$dupeParam);
            }else
                $queryParts[]=urlencode((string)$key).'='.urlencode((string)$value);
        }

        return implode('&', $queryParts);
    }
}