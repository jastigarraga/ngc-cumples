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
				$name = substr($f,3);
				array_push($migrations,[
						"version"=>$version,
						"name"=>$name,
						"file"=>$f
					]);
				$v = $version > $v? $version : $v;
			}
		}
		var_dump($migrations);
		$database_version = $this->db->get_var("SELECT MAX(ver_id) FROM ngc_migrations",0,0);
		if($database_version==null){
			$database_verion = 0;
		}
		var_dump($database_version);
		if($database_version > $v){
			while(count($migrations)>0 && $migrations[0]["version"] <= $database_version){
				array_shift($migratons);
			}
			return $migrations;
		}
		return [];
	}
	private function up($migrations){
		foreach ($migrations as $migration) {
			require_once $migration["file"];
			$m = new $migration["name"]($this->db);
			$m->up();
			$this->db->query("INSERT INTO ngc_migrations (".$migration["version"].",'".$migration["name"]."')");
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