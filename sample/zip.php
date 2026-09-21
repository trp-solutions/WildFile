<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/WildFile/blob/main/LICENSE
*/
declare(strict_types=1);
require_once('include.php');

if(empty($_POST['zip'])) {
	echo 'No files selected!';
	exit;
}

$wf = new \TRP\WildFile\WildFile($mysqli,STORAGE,'files');

$zip = $wf->zip();
foreach($_POST['zip'] as $id) {
	$zip->add((int) $id);
}
$zip->close();

\TRP\WildFile\Header::type($zip->type);
\TRP\WildFile\Header::size((int) $zip->size);
\TRP\WildFile\Header::filename('wf-download.zip',true);

$zip->output();
$zip->unlink();
