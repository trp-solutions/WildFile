<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/WildFile/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\WildFile;

class WildFile {
	private string $table;
	private string $dir;
	private \mysqli $dbconn;
	private string $storage;
	private string $idfield = 'id';
	private array $callback = [];

	public const NAME = 1;
	public const SIZE = 2;
	public const MIME = 3;
	public const CHECKSUM = 4;

	public function __construct(\mysqli $dbconn,string $storage,string $table,?string $dir = null) {
		if(!is_int($dbconn->thread_id)) {
			$this->exception('No DB Connection');
		}
		$this->dbconn = $dbconn;
		if(!is_dir($storage)) {
			$this->exception('Invalid directory');
		}
		$this->storage = $storage;
		$table = explode('.',$table);
		$this->dir = empty($dir) ? str_replace('`','',end($table)) : $dir;
		foreach($table as &$value) {
			if(strpos($value,'`')===false) $value = '`'.$value.'`';
		}
		$this->table = implode('.',$table);
	}
	public function set_idfield(string $idfield) : void {
		$this->idfield = $idfield;
	}
	public function set_callback(string $type, ?callable $function = null) : void {
		if($function) {
			$this->callback[$type] = $function;
		}
		else {
			unset($this->callback[$type]);
		}
	}
	public function store_string(string $string,array $field = [],?string $checksum_input = null) : void{
		$checksum = hash('sha256',$string);
		if(func_num_args()===3) $this->checksum_check($checksum,$checksum_input);
		$this->auto_value($field, [
			self::SIZE => strlen($string),
			self::CHECKSUM => $checksum
		]);
		$id = $this->db_store($field);
		$this->validate_id($id);
		$path = $this->create_path($id);
		$filename = $this->filename($id);
		if(file_put_contents($path.$filename,$string)===false) {
			$this->exception('Error store_string: '.$path);
		}
		$this->checksum_store($path,$filename,$checksum);
		$this->log('store_string: '.$id.'|'.$path.$filename);
	}
	public function store_post(array $FILES,array $field = [],?array $checksum_input = null){
		if(!isset($FILES['tmp_name']) || !is_array($FILES['tmp_name'])) {
			$this->exception('Invalid post array');
		}
		if(empty($FILES['tmp_name'][0])) {
			$this->exception('No files uploaded');
		}
		foreach($FILES['error'] as $key => $error) {
			$this->callback_execute('store',$FILES['tmp_name'][$key]);
			if($error!==UPLOAD_ERR_OK) continue;
			$checksum = hash_file('sha256',$FILES['tmp_name'][$key]);
			if(func_num_args()===3) $this->checksum_check($checksum,$checksum_input[$FILES['name'][$key]]);
			$this->auto_value($field, [
				self::NAME => $FILES['name'][$key],
				self::SIZE => $FILES['size'][$key],
				self::MIME => $FILES['type'][$key],
				self::CHECKSUM => $checksum
			]);
			$id = $this->db_store($field);
			$this->validate_id($id);
			$path = $this->create_path($id);
			$filename = $this->filename($id);
			move_uploaded_file($FILES['tmp_name'][$key],$path.$filename);
			$this->checksum_store($path,$filename,$checksum);
			$this->log('store_post: '.$id.'|'.$path.$filename);
		}
	}
	public function store_file_copy(string $uri, array $field = [], ?string $checksum_input = null) : void {
		$this->store_file_internal(true, $uri, $field, $checksum_input);
	}
	public function store_file_move(string $uri, array $field = [], ?string $checksum_input = null) : void {
		$this->store_file_internal(false, $uri, $field, $checksum_input);
	}
	private function store_file_internal(bool $copy, string $uri, array $field = [], ?string $checksum_input = null) : int {
		$checksum = hash_file('sha256',$uri);
		if(func_num_args()===3){
			$this->checksum_check($checksum,$checksum_input);
		}
		$this->auto_value($field, [
			self::CHECKSUM => $checksum,
			self::SIZE => filesize($uri),
		]);
		$id = $this->db_store($field);
		$this->validate_id($id);
		$path = $this->create_path($id);
		$filename = $this->filename($id);
		if($copy){
			if(copy($uri, $path.$filename)===false) {
				$this->exception('Error store_file: '.$path);
			}
		} else {
			if(rename($uri, $path.$filename)===false) {
				$this->exception('Error store_file: '.$path);
			}
		}
		$this->checksum_store($path,$filename,$checksum);
		$this->log('store_file: '.$id.'|'.$path.$filename);
		return $id;
	}
	private function auto_value(array &$field, array $auto) : void {
		foreach($field as &$value) {
			if(isset($value['auto']) && isset($auto[$value['auto']])) {
				$value['value'] = $auto[$value['auto']];
			}
		}
	}
	private function db_store(array $dbfield) : int {
		$fieldset = $this->fieldset($dbfield);
		$sql = "INSERT INTO $this->table SET $fieldset";
		$this->db_query($sql);
		return $this->dbconn->insert_id;
	}
	private function checksum_store(string $path,string $filename,string $checksum) : void {
		$content = 'SHA256 ('.$filename.') = '.$checksum.PHP_EOL;
		if(file_put_contents($path.$filename.'.sha256', $content)===false) {
			$this->exception('Error checksum_store: '.$path.$filename.'.sha256');
		}
	}
	private function checksum_check(string $checksum,string $checksum_input) : void {
		if($checksum!==$checksum_input) {
			$this->exception('Error checksum_check');
		}
	}
	public function replace_string(int $id,string $string,array $field = [],?string $checksum_input = null) : void {
		$checksum = hash('sha256',$string);
		if(func_num_args()===4) $this->checksum_check($checksum,$checksum_input);
		$this->auto_value($field, [
			self::SIZE => strlen($string),
			self::CHECKSUM => $checksum,
		]);
		$this->validate_id($id);
		$this->db_replace($id,$field);
		$path = $this->create_path($id);
		$filename = $this->filename($id);
		if(file_put_contents($path.$filename,$string)===false) {
			$this->exception('Error store_string: '.$path);
		}
		$this->checksum_store($path,$filename,$checksum);
		$this->log('replace_string: '.$id.'|'.$path.$filename);
	}
	public function replace_post(int $id,array $FILES,array $field = [],?array $checksum_input = null) : void {
		if($FILES['error']!==UPLOAD_ERR_OK) $this->exception('Upload error');
		$this->callback_execute('store',$FILES['tmp_name']);
		$checksum = hash_file('sha256',$FILES['tmp_name']);
		if(func_num_args()===4) $this->checksum_check($checksum,$checksum_input[$FILES['name']]);
		$this->auto_value($field, [
			self::NAME => $FILES['name'],
			self::SIZE => $FILES['size'],
			self::MIME => $FILES['type'],
			self::CHECKSUM => $checksum
		]);
		$this->validate_id($id);
		$this->db_replace($id,$field);
		$path = $this->create_path($id);
		$filename = $this->filename($id);
		move_uploaded_file($FILES['tmp_name'],$path.$filename);
		$this->checksum_store($path,$filename,$checksum);
		$this->log('replace_post: '.$id.'|'.$path.$filename);
	}
	private function db_replace(int $id,array $dbfield) : void {
		if(empty($dbfield)) return;
		$fieldset = $this->fieldset($dbfield);
		$sql = "UPDATE $this->table SET $fieldset WHERE `$this->idfield`='$id'";
		$this->db_query($sql);
	}
	public function get(int $id,array $field = []) : Out {
		$this->validate_id($id);
		$path = $this->get_path($id);
		$filename = $this->filename($id);
		$out = new Out($path.$filename);
		if($field) {
			$this->db_get($out,$id,$field);
		}
		return $out;
	}
	private function db_get(Out $out,int $id,array $dbfield) : void {
		$field = [];
		foreach($dbfield as $var) {
			$field[] = '`'.$this->dbconn->real_escape_string($var).'`';
		}
		$field = implode(',',$field);
		$sql = "SELECT $field FROM $this->table WHERE `$this->idfield`='$id'";
		$query = $this->db_query($sql);
		if($rs = $query->fetch_assoc()) {
			foreach($rs as $key => $value) {
				$out->$key = $value;
			}
		}
	}
	public function delete(int|array $array) : void {
		if(!is_array($array)) $array = [$array];
		foreach($array as $id) {
			$this->validate_id($id);
			$path = $this->get_path($id);
			$filename = $this->filename($id);
			$this->file_delete($path.$filename);
			$this->db_delete($id);
			$this->log('delete: '.$id.'|'.$path.$filename);
		}
	}
	private function db_delete(int $id) : void {
		$sql = "DELETE FROM $this->table WHERE `$this->idfield`='$id'";
		$this->db_query($sql);
	}
	private function file_delete(string $file) : void {
		if(file_exists($file)){
			if(file_exists($file.'.sha256')){
				if(!unlink($file.'.sha256')) {
					$this->exception('Error unlink checksum: '.$file.'.sha256');
				}
			}
			if(!unlink($file)) {
				$this->exception('Error unlink: '.$file);
			}
		}
	}
	public function evict(array|int $array,array $field = []) : void {
		if(!is_array($array)) $array = [$array];
		foreach($array as $id) {
			$this->validate_id($id);
			$path = $this->get_path($id);
			$filename = $this->filename($id);
			$this->file_delete($path.$filename);
			$this->db_replace($id,$field);
			$this->log('evict: '.$id.'|'.$path.$filename);
		}
	}
	public function zip() : Zip {
		return new Zip($this);
	}
	private function db_query(string $sql) : bool|\mysqli_result {
		$query = $this->dbconn->query($sql);
		if($this->dbconn->errno) {
			$this->exception('SQL Error: '.$this->dbconn->error);
		}
		return $query;
	}
	private function fieldset(array $dbfield) : string {
		$fieldset = [];
		foreach($dbfield as $key => $var) {
			if(!isset($var['value'])) $this->exception('Missing value: '.json_encode([$key=>$var]));
			$field = '`'.$this->dbconn->real_escape_string((string) $key).'`=';
			if(isset($var['noescape']) && $var['noescape']===true) {
				$field .= $var['value'];
			}
			else {
				$field .= "'".$this->dbconn->real_escape_string((string) $var['value'])."'";
			}
			$fieldset[] = $field;
		}
		return implode(',',$fieldset);
	}
	private function get_path(int $id) : string {
		$storage = $this->storage.DIRECTORY_SEPARATOR;
		$folder = $this->folder($id);
		return $storage.$folder;
	}
	private function create_path(int $id) : string {
		$storage = $this->storage.DIRECTORY_SEPARATOR;
		$folder = $this->folder($id);
		if(!is_dir($storage.$folder)) {
			$folder_arr = explode(DIRECTORY_SEPARATOR, $folder);
			$dir='';
			foreach($folder_arr as $part) {
				$dir .= $part.DIRECTORY_SEPARATOR;
				if(!is_dir($storage.$dir) && strlen($storage.$dir)>0) {
					if(!mkdir($storage.$dir)) {
						$this->exception('Error mkdir: '.$storage.$dir);
					}
				}
			}
		}
		return $storage.$folder;
	}
	private function filename(int $id) : string {
		return $id.'.bin';
	}
	private function validate_id(int $id) : void {
		if(!$id) {
			$this->exception('Invalid fileid');
		}
	}
	private function folder(int $id) : string {
		$parts = [];
		$parts[] = $this->dir;
		$str = (string) $id;
		while(strlen($str) > 2) {
			$parts[] = substr($str, -2);
			$str = substr($str, 0, -2);
		}
		return implode(DIRECTORY_SEPARATOR, $parts).DIRECTORY_SEPARATOR;
	}
	private function callback_execute(string $type,string $param) : void {
		if(!empty($this->callback[$type])) {
			$this->callback[$type]($param);
		}
	}
	protected function exception(string $message) : void {
		$this->log($message,LOG_ERR);
		throw new \Exception($message);
	}
	protected function log(string $message,int $priority = LOG_INFO) : void {
		syslog($priority,$message);
	}
}
