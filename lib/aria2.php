<?php

class aria2{
    private $server;
    private $ch;
    function __construct(){
        $host = config('leech.aria2_ip');
        $port = config('leech.aria2_port');
        $route = config('leech.aria2_route');
        $server= "$host:$port/$route";
        $this->server = $server;
        $this->ch = curl_init($server);
        curl_setopt_array($this->ch,array(
            CURLOPT_POST=>true,
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_HEADER=>false,
        ));
    }
    function __destruct(){
        curl_close($this->ch);
    }
    private function req($data){
        curl_setopt($this->ch,CURLOPT_POSTFIELDS,$data);
        return curl_exec($this->ch);
    }
    function __call($name,$arg){
        $data = array(
            'jsonrpc'=>'2.0',
            'id'=>'1',
            'method'=>'aria2.'.$name,
            'params'=>$arg,
        );
        $data = json_encode($data);
        return json_decode($this->req($data),1);
    }
}