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

if(! isset($_POST) || ! isset($_FILES)){
    http_response_code(400);
    echo json_encode([
        'code'=> 400,
        'message' => 'Bad Request(400): Mismatch a request type.',
    ]);
    exit(1);
}

$uploadkey = 'file_pdf';
$outputdir = sys_get_temp_dir() . '/' . bin2hex(random_bytes(32));
$outputdir = sys_get_temp_dir() . '/' . str_replace('php', '', basename($_FILES[$uploadkey]['tmp_name']));

if(! mkdir($outputdir)){
    http_response_code(500);
    echo json_encode([
        'code'=> 500,
        'message' => 'Internal Server Error(500): Unable create the output directory',
    ]);
    exit(1);
}

if(! isset($_FILES[$uploadkey])){
    http_response_code(400);
    echo json_encode([
        'code'=> 400,
        'message' => 'Bad Request(400): Empty Request.',
    ]);
    exit(1);
}

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

if($uploadfile['type']!=='application/pdf'){
    http_response_code(400);
    echo json_encode([
        'code'=> 400,
        'message' => 'Bad Request(400): Upload file is not pdf file.',
    ]);
    exit(1);
}

try{
    $imagick = new Imagick();
    $imagick->setResolution(96,96);
    $imagick->readImage($uploadfile['tmp_name']);
    $page_count = $imagick->getimagescene();
    for($i = 0; $i <= $page_count; $i++) {
        // 背景色を白に設定
        $imagick->setimageindex($i);
        $imagick->setImageBackgroundColor('#ffffff');
        $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
    }
    $imagick->setImageFormat('webp');
    $imagick->writeImages("{$outputdir}/_.webp", false);
    $imagick->destroy();
}catch(\ImagickException $e){
    http_response_code(500);
    echo json_encode([
        'code'=> 500,
        'message' => "Internal Server Error(500): {$e->getMessage()}",
    ]);
    exit(1);
}catch(\Exception $e){
    http_response_code(500);
    echo json_encode([
        'code'=> 500,
        'message' => "Internal Server Error(500): {$e->getMessage()}",
    ]);
    exit(1);
}

try{
    $zip = new \ZipArchive();
    $filename = "{$outputdir}.zip";
    if($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE){
        http_response_code(500);
        echo json_encode([
            'code'=> 500,
            'message' => "Internal Server Error(500): {$e->getMessage()}",
        ]);
        exit(1);
    }
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($outputdir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($files as $name => $file) {
        // ディレクトリはスキップ（ファイルのみを対象にする）
        if (!$file->isDir()) {
            // ファイルの絶対パス
            $filePath = $file->getRealPath();

            // ZIP内での相対パスを計算（元のフォルダ構造を維持するため）
            $relativePath = substr($filePath, strlen($outputdir) + 1);

            // ZIPにファイルを追加
            $zip->addFile($filePath, $relativePath);
        }
    }
    $zip->close();
}catch(\Exception $e){
    http_response_code(500);
    echo json_encode([
        'code'=> 500,
        'message' => "Internal Server Error(500): {$e->getMessage()}",
    ]);
    exit(1);
}

if(! rmdir($outputdir)){
    http_response_code(500);
    echo json_encode([
        'code'=> 500,
        'message' => 'Internal Server Error(500): Unable cleanup the output directory',
    ]);
    exit(1);
}

if( is_array($uploadfile)){
    echo json_encode([
        'uploadfile'=>$uploadfile,
        'outputdir'=>$outputdir,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}else{
    header('Content-Type: text/plain; charset=UTF-8');
    var_dump($uploadfile);
}
