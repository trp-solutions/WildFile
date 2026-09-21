<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/WildFile/blob/main/LICENSE
*/
declare(strict_types=1);
require_once('include.php');

$wf = new \TRP\WildFile\WildFile($mysqli,STORAGE,'`wildfile`.`files`','thumbnail');

$fields = [];
$fields['thumbnail'] = ['auto'=>\TRP\WildFile\WildFile::NAME];

$wf->replace_post((int) $_POST['thumbnail_id'],$_FILES['thumbnail'],$fields,$_POST['thumbnail_checksum']);

header('Location: .');
