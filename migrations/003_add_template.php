<?php
class add_template extends NGCMigration{
	public function up(){
		$this->db->query("CREATE TABLE ngc_template(
				text TEXT
			)");
		$this->db->query("INSERT INTO ngc_template VALUES ('<p><font size=\"5\">Felicidades {{0}} {{1}} {{2}}!</font></p><p>Éste mensaje ha sido enviado automáticamente.</p>')");
	}
}