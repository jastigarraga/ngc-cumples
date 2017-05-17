<?php
global $wpdb;	
$n =  'ngc-api/v1';
register_rest_route($n,"/Clientes",[
		"methods"=>"GET",
		"callback"=>function(){
			global $wpdb;
			echo json_encode($wpdb->get_results("SELECT * FROM ngc_customer"));
			die();
		}
	]);