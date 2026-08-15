<?php
$uploaddir = '/var/www/uploads/';
$uploadkey = 'file_pdf';

if(! is_uploaded($_FILES[$uploadkey]['name'])){
	echo json_encode([
		'code'=> 1,
		'message' => 'Bad Request(400): File has not uploaded.',
	]);
	exit(1);
}

$uploadfile = $uploaddir . basename($_FILES[$uploadkey]['name']);
