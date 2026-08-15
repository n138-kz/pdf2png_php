<?php
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
    http_response_code(400);
    echo json_encode([
        'code'=> 400,
        'message' => 'Bad Request(400): File has not uploaded.',
    ]);
    exit(1);
}

$uploadfile = $_FILES[$uploadkey];
$uploadfile['error'] = ['code'=>$uploadfile['error'],'detail'=>'',];
switch($uploadfile['error']['code']){
    case UPLOAD_ERR_OK:
    $uploadfile['error']['detail']='UPLOAD_ERR_OK';break;
    case UPLOAD_ERR_INI_SIZE:
    $uploadfile['error']['detail']='UPLOAD_ERR_INI_SIZE';break;
    case UPLOAD_ERR_FORM_SIZE:
    $uploadfile['error']['detail']='UPLOAD_ERR_FORM_SIZE';break;
    case UPLOAD_ERR_PARTIAL:
    $uploadfile['error']['detail']='UPLOAD_ERR_PARTIAL';break;
    case UPLOAD_ERR_NO_FILE:
    $uploadfile['error']['detail']='UPLOAD_ERR_NO_FILE';break;
    case UPLOAD_ERR_NO_TMP_DIR:
    $uploadfile['error']['detail']='UPLOAD_ERR_NO_TMP_DIR';break;
    case UPLOAD_ERR_CANT_WRITE:
    $uploadfile['error']['detail']='UPLOAD_ERR_CANT_WRITE';break;
    case UPLOAD_ERR_EXTENSION:
    $uploadfile['error']['detail']='UPLOAD_ERR_EXTENSION';break;
    default: break;
}

if($uploadfile['error']['code']!==UPLOAD_ERR_OK){
    http_response_code(400);
    echo json_encode([
        'code'=> 400,
        'message' => 'Bad Request(400): Upload Fail: ' . $uploadfile['error']['detail'],
    ]);
    exit(1);
}

if( is_array($uploadfile)){
echo json_encode($uploadfile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}else{
header('Content-Type: text/plain; charset=UTF-8');
var_dump($uploadfile);
}
