<?php
$uploaddir = '/var/www/uploads/';
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
