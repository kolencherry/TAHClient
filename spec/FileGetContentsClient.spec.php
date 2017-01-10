<?php
// Yeah, that's right, there's tests for this sample code
require 'FileGetContentsClient.php';
use TAHClient\FileGetContentsClient;

describe('FileGetContentsClient', function(){
    beforeAll(function(){
        $this->client=new FileGetContentsClient();
        $this->prefix='https://httpbin.org/';
    });

    describe('::buildOptionsArray', function(){
        it('should build a correct array of Headers', function(){
            $headers=array(
                'X-Patrick-Header' => 'Kolencherry',
                'X-Forwarded-By' => '1.2.3.4'
            );
            $res=$this->client->buildOptionsArray(null, array(), $headers);
            expect($res['headers'])->toContain('X-Patrick-Header: Kolencherry', 
                'X-Forwarded-By: 1.2.3.4');
        });

        it('should correctly generate a basic Auth header', function(){
            $res=$this->client->buildOptionsArray(null, array(), array(), 
                'patrick', 'kolencherry');

            expect($res['headers'])->toContain(
                'Authorization: Basic cGF0cmljazprb2xlbmNoZXJyeQ==');
        });

        it('should correctly override the default HTTP timeout', function(){
            $res=$this->client->buildOptionsArray(null, array(), array(),
                null, null, 120);

            expect($res['timeout'])->toEqual(120);
        });

        it('should correctly set the method for a request', function(){
            expect($this->client->buildOptionsArray('GET')['method'])->toEqual('GET');
            expect($this->client->buildOptionsArray('PoSt')['method'])->toEqual('POST');
            expect($this->client->buildOptionsArray('PUT  ')['method'])->toEqual('PUT');
            expect($this->client->buildOptionsArray(' head ')['method'])->toEqual('HEAD');
        });

        it('should correctly set the encoded content for POST data', function(){
            // PHP preserves order as declared for Arrays
            $data=array(
                'To'=>'client:patrick',
                'From'=>'+15128675309'
            );

            $res=$this->client->buildOptionsArray('POST', $data);
            expect($res['content'])->toEqual('To=client%3Apatrick&From=%2B15128675309');
        });

        it('should correctly set the encoded content for PUT data', function(){
            // PHP preserves order as declared for Arrays
            $data=array(
                'To'=>'client:patrick',
                'From'=>'+15128675309'
            );

            $res=$this->client->buildOptionsArray('PUT', $data);
            expect($res['content'])->toEqual('To=client%3Apatrick&From=%2B15128675309');
        });
    });

    describe('::buildQueryString', function(){
        it('should return a Falsy value for an empty array', function(){
            expect($this->client->buildQueryString(array()))->toBeFalsy();
        });

        it('should return a Falsy value for an non-array', function(){
            expect($this->client->buildQueryString(123))->toBeFalsy();
            expect($this->client->buildQueryString('abd'))->toBeFalsy();
            expect($this->client->buildQueryString(true))->toBeFalsy();
        });

        it('should return a urlencoded query string for unsafe characters', function(){
            $params=array('url_parameter' => '=,/+&"123‰patrick'); // I herd u liek utf-8

            expect($this->client->buildQueryString($params))
                ->toEqual('url_parameter=%3D%2C%2F%2B%26%22123%E2%80%B0patrick');

            // Handle the test-case of an unsafe parameter name
            $params=array(',/+&"123‰patrick='=>'url_parameter');
            expect($this->client->buildQueryString($params))
                ->toEqual('%2C%2F%2B%26%22123%E2%80%B0patrick%3D=url_parameter');
        });

        it('should return a query string for params with multiple values', function(){
            $params=array('url_parameter'=>array('patrick', 'kolencherry'));

            expect($this->client->buildQueryString($params))
                ->toEqual('url_parameter=patrick&url_parameter=kolencherry');
        });
    });

    // ToDo: expand tests to cover more request types
    describe('::request', function(){
        it('should return a Response object', function(){

        });

        it('should make a GET request', function(){

        });
    });
})
?>