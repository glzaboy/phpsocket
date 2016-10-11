#!/bin/php
<?php
$addr = 'localhost';
$port = 18520;
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (!is_resource($socket)) {
    echo 'Unable to create socket: ' . socket_strerror(socket_last_error()) . PHP_EOL;
}
if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo 'Unable to set option on socket: ' . socket_strerror(socket_last_error()) . PHP_EOL;
}
if (!socket_bind($socket, $addr, $port)) {
    echo 'Unable to bind socket: ' . socket_strerror(socket_last_error()) . PHP_EOL;
}
if (!socket_listen($socket)) {
    echo "unable to listen socket ", socket_strerror(socket_last_error()) . PHP_EOL;
}
// socket_set_nonblock($socket);
$sockcontrol = array(
    $socket
);
$sockdata = array();
// $sockwrite=array();
while (TRUE) {
    $control = $sockcontrol;
//	echo '控:', var_dump ( $control );
    $e = $w = null;
    if (socket_select($control, $w, $e, 0, 500) > 0) {
        $sockdata [] = $newsock = socket_accept($socket);
        // socket_set_nonblock($newsock);
    }
    if (count($sockdata) == 0) {
//		echo "没有客服连接";
        continue;
    }
    $rsdata = $sockdata;
    $wsdata = $sockdata;
    $esdata = $sockdata;

    if (socket_select($rsdata, $wsdata, $esdata, 500) <= 0) {
        continue;
    }
//    if (socket_select ( $rsdata, $wsdata, $esdata, 500 ) === false) {
//        continue;
//    }
    echo '读:' . var_dump($rsdata);
    echo '写:' . var_dump($wsdata);
    echo '异:' . var_dump($esdata);
    if (is_array($esdata)) {
        foreach ($esdata as $sock) {
            $key = array_search($sock, $sockdata);
            echo "关闭socket" . $key;
            unset ($sockdata [$key]);
            socket_close($sock);
        }
    }
    if (is_array($wsdata)) {
        foreach ($wsdata as $sock) {
            continue;
            $key = array_search($sock, $sockdata);
            unset ($sockdata [$key]);
            socket_close($sock);
        }
    }
    if (is_array($rsdata)) {
        foreach ($rsdata as $sock) {
            $data = socket_read($sock, 1024, PHP_NORMAL_READ);
            if ($data === '') {
                $key = array_search($sock, $sockdata);
                unset ($sockdata [$key]);
                socket_close($sock);
                continue;
            } elseif (strlen($data)==0) {
                $key = array_search($sock, $sockdata);
                unset ($sockdata [$key]);
                socket_close($sock);
                continue;
            }


            $data = trim($data);
            if (strtolower($data) == 'exit') {
                socket_write($sock, "BYE\n");
                $key = array_search($sock, $sockdata);
                unset ($sockdata [$key]);
                socket_close($sock);
                continue;
            }
            if ($data) {
                socket_write($sock, date("H:i:s")."you input :" . $data . "\n please input next command:");
            }
        }
    }
}
socket_close($socket);