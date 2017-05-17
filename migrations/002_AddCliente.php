<?php
class AddCliente extends NGCMigration{
	public function up(){
		$this->db->query("CREATE TABLE ngc_customer(
				idCustomer INT UNSIGNED AUTO_INCREMENT,
				name VARCHAR(40),
				surname1 VARCHAR(40),
				surname2 VARCHAR(40),
				email VARCHAR(100),
				date DATE,
				CONSTRAINT pk_ngc_customer PRIMARY KEY(idCustomer)
			)");
	}
}