<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PATCH');
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/manishop/config/db.php';
include $absolutePath;

$postdata = json_decode(file_get_contents("php://input"), true);