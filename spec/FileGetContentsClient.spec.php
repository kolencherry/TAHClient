<?php
require 'FileGetContentsClient.php';
use TAHClient\FileGetContentsClient;
// Yeah, that's right, there's tests

describe('FileGetContentsClient', function(){
    beforeAll(function(){
        $this->client=new FileGetContentsClient();
    });

    describe('::buildOptionsArray', function(){
        it('should build a correct array of Headers', function(){
            $headers=array(
                'X-Patrick-Header' => 'Kolencherry',
                'X-Forwarded-By' => '1.2.3.4'
            );
            $res=$this->client->buildOptionsArray(null, null, array(), array(), $headers);
            expect($res['headers'])->toContain('X-Patrick-Header: Kolencherry', 
                'X-Forwarded-By: 1.2.3.4');
        });

        it('should correctly generate a basic Auth header', function(){
            $res=$this->client->buildOptionsArray(null, null, array(), array(), array(), 
                'patrick', 'kolencherry');

            expect($res['headers'])->toContain(
                'Authorization: Basic cGF0cmljazprb2xlbmNoZXJyeQ==');
        });
    });
})
?>