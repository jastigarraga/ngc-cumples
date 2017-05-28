<?php
class Add_NGC_Session extends NGCMigration{
    public function up(){
        $this->db->query("CREATE TABLE ngc_session(
          session_id VARCHAR(32),
          time TIMESTAMP,
          CONSTRAINT PK_ngc_session PRIMARY KEY (session_id)
        )");
    }
}