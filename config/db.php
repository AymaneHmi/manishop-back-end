<?php

$envFilePath = __DIR__ . '/../.env';

if (file_exists($envFilePath)) {
    $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        list($name, $value) = explode('=', $line, 2);
        putenv("$name=$value");
    }
}

$server = getenv('SERVER');
$user = getenv('USER');
$password = getenv('PASSWORD');
$db_name = getenv('DB_NAME');

$conn = mysqli_connect($server, $user, $password, $db_name);

if (!$conn) {
    die('Could not connect: ' . mysql_error());
}
$api_token = getenv('API_TOKEN');