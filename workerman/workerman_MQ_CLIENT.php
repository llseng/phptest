<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * socket队列
 */
class workerman_mq_client
{
    private $resource;

    private $id;

    private $msgreg = "/^MQ_MSG:(\w+)=([\s\S]+)$/";

    private $tmp = "";

    public function __construct($resource, $id = "queue")
    {
        if( !is_resource( $resource ) ) {
            throw new \Exception("Not a resource.", 1);
        }
        $this->resource = $resource;
        $this->id = $id;
    }

    public function getResource() {
        return $this->resource;
    }

    public function setId( $id ) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function push( $msg ) {
        $data = "MQ_PUSH:". $this->getId(). "=". (string)$msg;
        
        $this->send_msg( $data );

        return $this->get_msg();
    }

    public function pull() {
        $data = "MQ_PULL:". $this->getId(). "=1";
        
        $this->send_msg( $data );

        return $this->deal_mag();
    }

    public function len() {
        $data = "MQ_LEN:". $this->getId(). "=1";

        $this->send_msg( $data );

        return (int)$this->deal_mag();
    }

    private function send_msg( $data ) {
        if( fwrite( $this->getResource(), $data."\n" ) === false ) {
            throw new \Exception("Data write fail.", 1);
        }
        return true;
    }

    private function get_msg(){
        while( strpos( $this->tmp, "\n" ) === false ) {

            $result = fread( $this->getResource(), 65535 );

            if( $result !== "\n" && empty($result) ) {
                throw new \Exception("Data read fail.", 1);
            }

            $this->tmp .= $result;
        }

        $pos = strpos( $this->tmp, "\n" );
        $result = substr( $this->tmp, 0, $pos );
        $this->tmp = substr( $this->tmp, $pos+1 );

        return trim( $result );
    }

    private function deal_mag() {
        $result = $this->get_msg();

        $match = [];
        if( !preg_match($this->msgreg, $result, $match) ) {
            return $result;
        }

        return $match[2];
    }

}
