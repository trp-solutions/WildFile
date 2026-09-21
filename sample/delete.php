<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/WildFile/blob/main/LICENSE
*/
declare(strict_types=1);
require_once('include.php');

$wf = new \TRP\WildFile\WildFile($mysqli,STORAGE,'files','thumbnail');

$fields = [];
$fields['thumbnail'] = ['value'=>'NULL','noescape'=>true];
$wf->evict((int) $_GET['file_id'],$fields);

$wf = new \TRP\WildFile\WildFile($mysqli,STORAGE,'files');
$wf->delete((int) $_GET['file_id']);

header('Location: .');
