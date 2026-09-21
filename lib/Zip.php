<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/WildFile/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\WildFile;

class Zip extends Out {
	private WildFile $wf;
	private \ZipArchive $archive;

	public function __construct(WildFile $wf) {
		$this->wf = $wf;
		$file = tempnam(sys_get_temp_dir(), 'wfzip_');
		$this->archive = new \ZipArchive();
		$result = $this->archive->open($file, \ZipArchive::OVERWRITE);
		if(!$result) {
			throw new \Exception('Error open ZipArchive: '.$file);
		}
		$this->type = 'application/zip';
		$this->file = $file;
	}
	public function add(int $id,?string $name = null) : void {
		$file = $this->wf->get($id,$name ? [] : ['name']);
		$result = $this->archive->addFile($file->get_path(),$name ? $name : $file->name);
		if(!$result) {
			throw new \Exception('Error addFile ZipArchive: '.$file->get_path());
		}
	}
	public function close() : void {
		$result = $this->archive->close();
		if(!$result) {
			throw new \Exception('Error close ZipArchive');
		}
		$this->size = (string) filesize($this->file);
	}
	public function unlink() : void {
		unlink($this->file);
	}
}
