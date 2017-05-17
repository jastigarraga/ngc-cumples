<?php
abstract class NGCMigration{
	protected $db;
	public abstract function up();
	public function __construct($wpdb){
		$this->db = $wpdb;
	}
}
class NGCMigrationManager{
	private $db;
	private function list_migrations(){
		$files = scandir(plugin_dir_path(__FILE__) . DIRECTORY_SEPARATOR . "migrations");
		$migrations =[];
		$v = 0;
		foreach ($files as $f) {
			if($f[0] !== "."){
				$version = substr($f,0,3);
				$name = str_replace(".php", "",substr($f,4));
				array_push($migrations,[
						"version"=>$version*1,
						"name"=>$name,
						"file"=>$f
					]);
				$v = $version*1 > $v*1? $version*1 : $v*1;
			}
		}
		$database_version = $this->db->get_var("SELECT MAX(ver_id) FROM ngc_migrations",0,0);
		if(!isset($database_version) || !$database_version){
			$database_version = 0;
		}
		if($database_version < $v){
			while(count($migrations)>0 && $migrations[0]["version"] <= $database_version){
				array_shift($migratons);
			}
			return $migrations;
		}
		return [];
	}
	private function up($migrations){
		foreach ($migrations as $migration) {
			$path =   "migrations" . DIRECTORY_SEPARATOR . $migration["file"];
			require_once plugin_dir_path(__FILE__) . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR . $migration["file"];
			$m = new $migration["name"]($this->db);
			$m->up();
			$this->db->query("INSERT INTO ngc_migrations VALUES (".$migration["version"].",'".$migration["name"]."')");
		}
	}
	public function migrate(){
		$migrations = $this->list_migrations();
		if(count($migrations)>0){
			$this->up($migrations);
		}
	}
	public function __construct($wpdb){
		$this->db = $wpdb;
	}
}