<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/WildFile/blob/main/LICENSE
*/
declare(strict_types=1);
require_once('include.php');

$wf = new \TRP\WildFile\WildFile($mysqli,STORAGE,'files');

$fields = [];

if($_GET['type']=='server') {
	$fields['name'] = ['value'=>'server.json'];
	$fields['mime'] = ['value'=>'application/json'];
	$string = json_encode($_SERVER, JSON_PRETTY_PRINT);
}
elseif($_GET['type']=='phpversion') {
	$fields['name'] = ['value'=>'version.txt'];
	$fields['mime'] = ['value'=>'text/plain'];
	$string = phpversion();
}

// For demonstration
$verify = hash('sha256',$string);

$fields['size'] = ['auto'=>\TRP\WildFile\WildFile::SIZE];
$fields['checksum'] = ['auto'=>\TRP\WildFile\WildFile::CHECKSUM];
$fields['address'] = ['value'=>$_SERVER['REMOTE_ADDR']];
$fields['created'] = ['value'=>'NOW()','noescape'=>true];

$wf->store_string($string,$fields,$verify);

header('Location: .');
