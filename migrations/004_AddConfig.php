<?php
class AddConfig extends NGCMigration{
	public function up(){
		$this->db->query("CREATE TABLE ngc_config(
					_key VARCHAR(10) PRIMARY KEY,
					value TEXT
				)");
		$this->db->query("INSERT INTO ngc_config VALUES ('subject','Felicidades {{0}}!	')");
	}
}