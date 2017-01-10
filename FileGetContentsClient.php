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
    **  @return Twilio\Http\Response Response for a successful request
    */   
    public function request($method, $url, $params=array(), $data=array(), 
        $headers=array(), $user=null, $password=null, $timeout=null){


    }

    /*
    **
    **  @return array Array of options that's consumable by file_get_contents
    */
    public function buildOptionsArray($method, $params=array(), $data=array(), 
        $headers=array(), $username=null, $password=null, $timeout=null){

        $options=array();

        $options['headers']=array();
        // PHP defaults to HTTP 1.0 for HTTP Contexts — Twilio uses 1.1 w/ CurlClient
        $options['protocol_version']=1.1;

        $options['method']=strtoupper(trim($method));
        $options['timeout']=($timeout)?$timeout:self::DEFAULT_HTTP_TIMEOUT;

        // Add each header to headers array in Header: Value format
        foreach($headers as $k=>$v)
            $options['headers'][]="$k: $v";

        // Add Authorization header if username AND password are present
        if($username && $password)
            $options['headers'][] = 'Authorization: Basic '
                .base64_encode("$username:$password");

        

        return $options;
    }

    /*
    **  Builds a query string for fields, as required by POST bodies and GET URLs
    **  
    **  @return string Query string with all fields appropriately encoded
    */
    public function buildQueryString($params){
        // Funny enough, despite calling it queryParts, this will handle multipart
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

?>