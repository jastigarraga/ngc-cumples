<?php
class FirstMigration extends NGCMigration {
	public function up(){
		$this->db("CREATE TABLE ngc_migrations(
				ver_id INT PRIMARY KEY,
				name varchar(40)
			)");
	}
}