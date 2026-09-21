<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/WildFile/blob/main/LICENSE
*/
declare(strict_types=1);
require_once('include.php');

$wf = new \TRP\WildFile\WildFile($mysqli,STORAGE,'files','thumbnail');
$file = $wf->get((int) $_GET['thumbnail_id']);

\TRP\WildFile\Header::type('image/svg+xml');
\TRP\WildFile\Header::expires();

$file->output();
