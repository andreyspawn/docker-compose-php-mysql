<?php

const MYSQL_SERVER = "mysql";
const MYSQL_SERVER_LOCALHOST = "localhost";
const MYSQL_USER = "root";
const MYSQL_PASSWORD = 'password';
const MYSQL_DB = 'test';

use React\EventLoop\Factory;
use React\EventLoop\Loop;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Http\Server;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Socket\Server as SocketServer;

// $sql = "CREATE TABLE MyGuests (
//     id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     firstname VARCHAR(30) NOT NULL,
//     lastname VARCHAR(30) NOT NULL,
//     email VARCHAR(50),
//     reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
//     )";

// $sql = "CREATE TABLE messages (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY)";
// $sql = "SHOW DATABASES";

// $mysql = new mysqli();
// $mysqlConnection = $mysql->connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB);
// var_dump($mysqlConnection);

// if ($mysqlConnection->connect_error) {
//     echo "Connection failed: " . $conn->connect_error;
//   }
//   else {
//       echo "Connected successfully";
//   }


require __DIR__.'/vendor/autoload.php';

$dbh = new PDO('mysql:host=mysql;dbname=test', 'root', 'password');
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

$key_msg = "time:".time();
$msg = "messaage text:".rand();

$loop = Factory::create();

$server = new Server($loop,

function(ServerRequestInterface $request) use ($dbh,$preparedQuery)
{
    $key_msg = "time:".time();
    $msg = "messaage text:".rand();
    $preparedQuery->bindParam(':key_msg', $key_msg);
    $preparedQuery->bindParam(':msg', $msg);
    $preparedQuery->execute();
    return new Response(
        200,
        ['Content-Type' => 'application/json'],
        json_encode(["simple_text" => "New message!!!"]) 
    );
});

$socket = new SocketServer('0.0.0.0:9000', $loop);
$server->listen($socket);
echo "Server ReactPHP HTTP Server have to started".PHP_EOL;
echo 'Working on ' . str_replace('tcp:','http:',$socket->getAddress())."\n";

$loop->run();