<?php
$uploaddir = '/var/www/uploads/';
session_name('SID');
session_start([
    'cookie_lifetime' => 86400,
    'use_strict_mode' => true,
]);

header('Content-Type: application/json; charset=UTF-8');

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
if (file_exists(__DIR__ . '/func.php')) {
    require_once __DIR__ . '/func.php';
}

if(!isset($_SERVER['HTTP_USER_AGENT']) || !isset($_SERVER['REMOTE_ADDR'])) {
    http_response_code(400);
    die(json_encode([
        'code' => 400,
        'message' => 'Bad Request(400): Missing required headers.',
    ]));
}
if(('POST' !== $_SERVER['REQUEST_METHOD'])) {
    http_response_code(400);
    die(json_encode([
        'code' => 400,
        'message' => 'Bad Request(400): Invalid request method.',
    ]));
}

$uploadkey = 'file_pdf';

if(! is_uploaded_file($_FILES[$uploadkey]['tmp_name'])){
	echo json_encode([
		'code'=> 1,
		'message' => 'Bad Request(400): File has not uploaded.',
	]);
	exit(1);
}

$uploadfile = $_FILES[$uploadkey];
var_dump($uploadfile);
