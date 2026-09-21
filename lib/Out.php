<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/WildFile/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\WildFile;

class Out {
	protected string $file;
	protected array $property = [];
	public function __construct(string $file){
		$this->file = $file;
	}
	public function __toString() : string {
		return file_get_contents($this->file);
	}
	public function output() : void {
		$handle = fopen($this->file,'r');
		while (!feof($handle)) {
			echo fgets($handle, 4096);
		}
		fclose($handle);
	}
	public function get_path() : string {
		return $this->file;
	}
	public function __set(string $name, string $value) : void {
		$this->property[$name] = $value;
	}
	public function __get(string $name) : string {
		return $this->property[$name];
	}
}
