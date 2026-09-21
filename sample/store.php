<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/WildFile/blob/main/LICENSE
*/
declare(strict_types=1);
require_once('include.php');

$wf = new \TRP\WildFile\WildFile($mysqli,STORAGE,'files');

$fields = [];

$file = __DIR__."/../LICENSE";

$fields['name'] = ['value'=>'LICENSE.txt'];
$fields['mime'] = ['value'=>'text/plain'];
$fields['size'] = ['auto'=>\TRP\WildFile\WildFile::SIZE];
$fields['checksum'] = ['auto'=>\TRP\WildFile\WildFile::CHECKSUM];
$fields['address'] = ['value'=>'255.255.255.0'];
$fields['created'] = ['value'=>'NOW()','noescape'=>true];

$wf->store_file_copy($file,$fields);

header('Location: .');
