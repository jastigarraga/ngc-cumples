<?php

class AddColumnSent extends NGCMigration{
	public function up(){
		$this->db->query("ALTER TABLE ngc_customer ADD COLUMN last_sent DATE");
	}
}