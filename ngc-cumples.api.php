<?php
$n =  'ngc-api/v1';
register_rest_route($n."/Templates","/Editor",[
		"methods"=>"GET",
		"callback"=>function(){
			readfile(plugin_dir_path(__FILE__)."templates/Editor.html");
			die();
		}
	]);
register_rest_route($n."/Templates","/Clientes",[
		"methods"=>"GET",
		"callback"=>function(){
			readfile(plugin_dir_path(__FILE__)."templates/Clientes.html");
			die();
		}
	]);
register_rest_route($n."/Templates","/Reemplazos",[
		"methods"=>"GET",
		"callback"=>function(){
			readfile(plugin_dir_path(__FILE__)."templates/Reemplazos.html");
			die();
		}
	]);
register_rest_route($n."/Templates","/WYSIWYGEditor",[
		"methods"=>"GET",
		"callback"=>function(){
			readfile(plugin_dir_path(__FILE__)."templates/WYSIWYGEditor.html");
			die();
		}
	]);
register_rest_route($n."/Templates","/Grid.html",[
		"methods"=>"GET",
		"callback"=>function(){
			readfile(plugin_dir_path(__FILE__)."templates/Grid.html");
			die();
		}
	]);