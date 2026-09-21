<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/WildFile/blob/main/LICENSE
*/
declare(strict_types=1);
require_once('include.php');

$wf = new \TRP\WildFile\WildFile($mysqli,STORAGE,'files');

$fields = [];
$fields['name'] = ['auto' => \TRP\WildFile\WildFile::NAME];
$fields['size'] = ['auto' => \TRP\WildFile\WildFile::SIZE];
$fields['mime'] = ['auto' => \TRP\WildFile\WildFile::MIME];
$fields['checksum'] = ['auto' => \TRP\WildFile\WildFile::CHECKSUM];
$fields['address'] = ['value'=>$_SERVER['REMOTE_ADDR']];
$fields['created'] = ['value'=>'NOW()','noescape'=>true];

$sizecheck = function($tmp_name) {
	$info = getimagesize($tmp_name);
	if($info[0]!==256 || $info[1]!==256 || $info['mime']!=='image/jpeg') {
		throw new \Exception('Wrong format! - Must be 256x256px image/jpeg');
	}
};

//$wf->set_callback('store',$sizecheck);
$wf->store_post($_FILES['fileupload'],$fields,$_POST['fileupload_checksum']);

header('Location: .');
