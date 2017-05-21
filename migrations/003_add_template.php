<?php
class add_template extends NGCMigration{
	public function up(){
		$this->db->query("CREATE TABLE ngc_template(
				text TEXT
			)");
		$this->db->query("INSERT INTO ngc_template VALUES ('<p>Plantilla por defécto</p>')");
	}
}