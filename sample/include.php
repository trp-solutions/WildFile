<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/WildFile/blob/main/LICENSE
*/
declare(strict_types=1);
require_once('../../HealDocument/lib/HealDocument.php'); // https://github.com/trp-solutions/HealDocument
require_once('../lib/ChunkedUpload.php');
require_once('../lib/Header.php');
require_once('../lib/Out.php');
require_once('../lib/WildFile.php');
require_once('../lib/Zip.php');

define('STORAGE',__DIR__.'/../storage');

$mysqli = new mysqli('localhost','wildfile','Pa55w0rd','wildfile');
$mysqli->set_charset('utf8mb4');
