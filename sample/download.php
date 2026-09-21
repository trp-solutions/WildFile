<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/WildFile/blob/main/LICENSE
*/
declare(strict_types=1);
require_once('include.php');

$wf = new \TRP\WildFile\WildFile($mysqli,STORAGE,'files');

$file = $wf->get((int) $_GET['file_id'],['mime','name','size']);

\TRP\WildFile\Header::type($file->mime);
\TRP\WildFile\Header::size((int) $file->size);
\TRP\WildFile\Header::filename($file->name,true);
\TRP\WildFile\Header::expires();

$file->output();
