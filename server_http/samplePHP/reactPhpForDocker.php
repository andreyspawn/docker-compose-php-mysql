<?php

const MYSQL_SERVER = "mysql:host=mysql";
const MYSQL_SERVER_LOCALHOST = "localhost";
const MYSQL_USER = "root";
const MYSQL_PASSWORD = 'password';
const MYSQL_DB = 'dbname=test';

use React\EventLoop\Loop;
use React\Http\HttpServer;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Socket\SocketServer;

require __DIR__.'/vendor/autoload.php';

$dbh = new PDO(MYSQL_SERVER.';'.MYSQL_DB, 'root', 'password');
$sql = "CREATE TABLE messages (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_msg VARCHAR(30) NOT NULL, 
    msg VARCHAR(255))";
$dbs = $dbh->query($sql);

$preparedQuery = $dbh->prepare("INSERT INTO messages (key_msg, msg) VALUES (:key_msg, :msg)");
$key_msg = "time: Start time 000000";
$msg = "messaage text: Start message";
echo $key_msg;

$preparedQuery->bindParam(':key_msg', $key_msg);
$preparedQuery->bindParam(':msg', $msg);
$preparedQuery->execute();

// $key_msg = "time:".time();
// $msg = "messaage text:".rand();

$loop = Loop::get();

$server = new HttpServer(
    $loop,
    function (ServerRequestInterface $request) use ($dbh, $preparedQuery) {
        $browAgent = ":".implode("/",$request->getHeader('user-agent'));
        $params = $request->getQueryParams();
        $key_msg = $params['key'] ? $params['key'].time() : 'Key is not set!';
        $msg = isset($params['msg']) ? $params['msg'].$browAgent : 'Message is null';
        $preparedQuery->bindParam(':key_msg', $key_msg);
        $preparedQuery->bindParam(':msg', $msg);
        $preparedQuery->execute();
        echo $params['key'].PHP_EOL;
        echo $params['msg'].PHP_EOL;
        echo $browAgent.PHP_EOL;
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(["simple_text" => "New message!!!"])
        );
    }
);

$socket = new SocketServer(isset($argv[1]) ? $argv[1] : '0.0.0.0:9000');
$server->listen($socket);
echo "Server ReactPHP HTTP Server have to started".PHP_EOL;
echo 'Working on ' . str_replace('tcp:', 'http:', $socket->getAddress())."\n";

$loop->run();
