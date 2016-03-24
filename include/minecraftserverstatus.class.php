<?php

/**
 * Minecraft Server Status Query
 * @author Julian Spravil <julian.spr@t-online.de> https://github.com/FunnyItsElmo
 * @license Free to use but dont remove the author, license and copyright
 * @copyright © 2013 Julian Spravil
 */
class MinecraftServerStatus {

    private $timeout;

    public function __construct($timeout = 2) {
        $this->timeout = $timeout;
    }

    public function getStatus($host = '127.0.0.1', $port = 25565) {

        if (substr_count($host , '.') != 4) $host = gethostbyname($host);

        $serverdata = [];

        $socket = $this->connect($host, $port);

        if(!$socket) {
            return false;
        }

        $start = microtime(true);

        $handshake = pack('cccca*', hexdec(strlen($host)), 0, 0x04, strlen($host), $host).pack('nc', $port, 0x01);

        socket_send($socket, $handshake, strlen($handshake), 0); //give the server a high five
        socket_send($socket, "\x01\x00", 2, 0);
        socket_read( $socket, 1 );

        $ping = round((microtime(true)-$start)*1000); //calculate the high five duration

        $packetlength = $this->read_packet_length($socket);

        if($packetlength < 10) {
            return false;
        }

        socket_read($socket, 1);

        $packetlength = $this->read_packet_length($socket);

        $data = socket_read($socket, $packetlength, PHP_NORMAL_READ);

        if(!$data) {
            return false;
        }

        $data = json_decode($data);

        $serverdata['ping'] = $ping;
        $serverdata['raw_data'] = $data;

        /*
        $serverdata['version'] = $data->version->name;
        $serverdata['protocol'] = $data->version->protocol;
        $serverdata['players'] = $data->players->online;
        $serverdata['maxplayers'] = $data->players->max;

        $motd = $data->description;
        $motd = preg_replace("/(§.)/", "",$motd);
        $motd = preg_replace("/[^[:alnum:][:punct:] ]/", "", $motd);

        $serverdata['motd'] = $motd;
        $serverdata['motd_raw'] = $data->description;
        $serverdata['favicon'] = $data->favicon;
        $serverdata['ping'] = $ping;
        */


        $this->disconnect($socket);

        return $serverdata;

    }

    private function connect($host, $port) {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!@socket_connect($socket, $host, $port)) {
    		$this->disconnect($socket);
    		return false;
	    }
        return $socket;
    }

    private function disconnect($socket) {
        if($socket != null) {
            socket_close($socket);
        }
    }

    private function read_packet_length($socket) {
        $a = 0;
        $b = 0;
        while(true) {
            $c = socket_read($socket, 1);
            if(!$c) {
                return 0;
            }
            $c = Ord($c);
            $a |= ($c & 0x7F) << $b++ * 7;
            if( $b > 5 ) {
                return false;
            }
            if(($c & 0x80) != 128) {
                break;
            }
        }
        return $a;
    }
}