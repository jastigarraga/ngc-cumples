<?php
global $wpdb;	
$n =  'ngc-api/v1';
register_rest_route($n,"/MailTemplate",[
		"methods"=>"GET",
		"callback"=>function($req){
			global $wpdb;
			die(json_encode($wpdb->get_results("SELECT * FROM ngc_template LIMIT 1")));
		}
	]);
register_rest_route($n,"/MailTemplateUpdate",[
		"methods"=>"POST",
		"callback"=>function($req){
			global $wpdb;
			$params = $req->get_params();
			$template = esc_sql($params["template"]);
			$wpdb->query("UPDATE ngc_template SET text = '$template'");
			die(json_encode($wpdb->get_results("SELECT * FROM ngc_template LIMIT 1")));
		}
	]);
register_rest_route($n,"/Clientes",[
		"methods"=>"GET",
		"callback"=>function($req){
			global $wpdb;
			$result = [];			
			$params = $req->get_params();
			$filter = json_decode($params["filter"]);
			$sql = "SELECT * FROM ngc_customer";
			$where = " WHERE ";
			$addWhere = function($property,$where,$filter){		
				if(isset($filter->$property) && $filter->$property != ""){
					$where .= ($where!=" WHERE "?" AND ":"")."LOWER($property) LIKE LOWER('%" . esc_sql($filter->$property)."%')";
				}
				return $where;
			};
			$filters = ["name","surname1","surname2","email"];
			foreach($filters as $f){
				$where  = $addWhere($f,$where,$filter);
			}
			if($where != " WHERE "){
				$sql .= $where;
			}
			if(isset($params["pageSize"]) && $params["pageSize"] != "-1"){
				$sql .= " LIMIT " .($params["pageSize"] * ($params["page"] - 1).",".$params["pageSize"]);
			}
			$result["total"] = $wpdb->get_var("SELECT COUNT(*) FROM ngc_customer" . ($where!=" WHERE "?$where:""));
			$result["data"] = $wpdb->get_results($sql);
			die(json_encode($result));
		}
	]);
function sanitize_and_validate($input,$ruleset){
	$result = [];
	foreach ($ruleset as $name => $rules) {
		if(isset($rules["sanitize"])){
			$result[$name] = esc_sql($rules["sanitize"]((isset($input[$name])?$input[$name]:null)));
		}else{
			$result[$name] = esc_sql((isset($input[$name])?$input[$name]:null));
		}
		if(isset($rules["validate"]) && !$rules["validate"]($result[$name])){
			$error_message = $rules["validate"]($result[$name]);
			if($error_message !== null){
				throw new Exception($error_message);
			}
		}
	}
	return $result;
}
function ngc_r($n,$lmin,$lmax,$email=false){
	return [
			"sanitize"=>function($e){
					return htmlspecialchars($e,ENT_QUOTES);
				},
			"validate"=>function($e) use($n,$lmin,$lmax,$email){
				if(strlen($e)>$lmax){
					return "El campo $n no puede tener mas de $lmax carácteres";
				}
				if(strlen($e)<$lmin){
					return "El campo $n no puede tener menos de $lmin carácteres";
				}
				if($email && !	filter_var($e, FILTER_VALIDATE_EMAIL)){
					return "El campo $n no es una dirección de correo válida";
				}
			}
		];
}
function get_customer($input){
	$customer = sanitize_and_validate($input,[
			"idCustomer"=>[
				"sanitize"=>function($e=null){
					if(isset($e) && $e !== null){
						return (int)$e;
					}
					return 0;
				},
				"validate"=>function($e){
					if($e !=(int)$e){
						return "El campo id debe de ser un número";
					}
				}
			],
			"name"=>ngc_r("nombre",2,40),
			"surname1"=>ngc_r("primer apellido",2,40),
			"surname2"=>ngc_r("segundo apellido",2,40),
			"email"=>ngc_r("email",2,40,true),
			"date"=>[
				"sanitize"=>function($e){
					return filter_var($e,FILTER_SANITIZE_FULL_SPECIAL_CHARS);
				},
				"validate"=>function($e){
					if($e != date($e)){
						return "El campo fecha ha de ser una fecha válida";
					}
				}
			]
		]);
	return $customer;
}
register_rest_route($n,"/ClientesUpdate",[
		"methods"=>"POST",
		"callback"=>function($req){
			global $wpdb;
			try {
				$data = $req->get_params();

				$ctr = get_customer($data["new"]);
				$ctrOld = get_customer($data["old"]);
				$wpdb->query("UPDATE ngc_customer SET name='".$ctr["name"]."', surname1='".$ctr["surname1"]."',
					surname2='".$ctr["surname2"]."',email='".$ctr["email"]."',date='".$ctr["date"]."' WHERE idCustomer=".$ctr["idCustomer"]);
				die(json_encode($wpdb->get_results("SELECT * FROM ngc_customer WHERE idCustomer=".$ctr["idCustomer"])));
			}
			catch (Exception $ex){
				http_response_code(500);
				die(json_encode($ex));
			}
			die();
		}
	]);
register_rest_route($n,"/ClientesInsert",[
		"methods"=>"POST",
		"callback"=>function($req){
			global $wpdb;
			try{
				$ctr = get_customer($req->get_params());
				$wpdb->query("BEGIN");
				$wpdb->query("INSERT INTO ngc_customer (name,surname1,surname2,email,date) VALUES ('".$ctr["name"]."',
					'".$ctr["surname1"]."','".$ctr["surname2"]."','".$ctr["email"]."','".$ctr["date"]."')");
				echo json_encode($wpdb->get_results("SELECT * FROM ngc_customer WHERE idCustomer = LAST_INSERT_ID()"));
				if($wpdb->last_error !== ''){
    				$wpdb->print_error();
    				$wpdb->query("ROLLBACK");
				}
				$wpdb->query("COMMIT");
    			die();
			}catch(Exception $ex){
				http_response_code(500);
				die(json_encode($ex));
			}
			die();
		}
	]);
register_rest_route($n,"/ClientesDelete",[
		"methods"=>"POST",
		"callback"=>function($req){
			global $wpdb;
			try{
			$ctr = get_customer($req->get_params());
			if(isset($ctr["idCustomer"])){
				$id = $ctr["idCustomer"];
				$wpdb->query("DELETE FROM ngc_customer WHERE idCustomer=$id");
				if(isset($wpdb->last_error) && $wpdb->last_error){
					http_response_code(500);
					die("No se pudo eliminar: " . $wpdb->last_error);
				}
				http_response_code(200);
				die("{\"msg\":\"Ok\"}");
			}
			}catch(Exception $ex){
				http_response_code(500);
				die($ex->get_message());
			}
		}
	]);