<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/WildFile/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\WildFile;

class Header {
	public static function type(string $str) : void {
		header('Content-Type: '.$str);
	}
	public static function size(int $size) : void{
		header('Content-Length: '.$size);
	}
	public static function filename(string $filename,bool $download = false) : void {
		$download = $download ? 'attachment' : 'inline';
		header("Content-Disposition: ".$download."; filename*=UTF-8''".rawurlencode($filename));
	}
	public static function expires(\DateTime|\DateTimeImmutable|null $datetime = null) {
		if(!$datetime) {
			$datetime = new \DateTimeImmutable('1 month');
		}
		$datetime->setTimezone(new \DateTimeZone('UTC'));
		$seconds = (new \DateTimeImmutable())->diff($datetime)->format('%a') * 86400;
		header('Expires: '.$datetime->format('D, d M Y H:i:s \G\M\T'));
		header('Cache-Control: max-age='.$seconds);
		header_remove('Pragma');
	}
}
