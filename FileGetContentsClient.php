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
    public function buildOptionsArray($method, $url, $params=array(), $data=array(), 
        $headers=array(), $username=null, $password=null, $timeout=null){

        $options=array();

        $options['method']=strtoupper(trim($method));
        $options['timeout']=($timeout)?$timeout:self::DEFAULT_HTTP_TIMEOUT;
        $options['headers']=array();

        // Add each header to headers array in Header: Value format
        foreach($headers as $k=>$v){
            $options['headers'][]="$k: $v";
        }

        // Add Authorization header if username AND password are present
        if($username && $password){
            $options['headers'][] = 'Authorization: Basic '
                .base64_encode("$username:$password");
        }

        return $options;
    }

    /*
    **  Builds a query string for fields required for a POST request
    **  
    **  @return string Query string with all fields appropriately encoded
    */
    public function buildPostQueryString(){

    }
}

?>