<?php

Class NGC_Mail_Manager {
	public static function run($db){
		require_once plugin_dir_path(__FILE__) . "PHPMailer/PHPMailerAutoload.php";

	}
	public static function check($config){
		require_once plugin_dir_path(__FILE__) . "PHPMailer/PHPMailerAutoload.php";
		$config["mail_subj"] = "NGC Mensaje de prueba";
		$config["mail_body"] = "El mensaje ha sido enviado con éxito";
		$config["mail_to"] = $config["mail_from"];
		return NGC_Mail_Manager::send($config);
	}
	private static function send($mail){
		$m = new PHPMailer();
		$m->isSMTP();
		$m->SMTPAuth = true;
		$m->Host = $mail["mail_smtp"];
		$m->Port = $mail["mail_port"];
		$m->Username = $mail["mail_user"];
		$m->Password = $mail["mail_pass"];
		$m->SMTPSecure = $mail["mail_secur"];
		$m->SetFrom($mail["mail_from"],$mail["mail_name"]);
		$m->AddAddress($mail["mail_to"],$mail["mail_name"]);
		$m->Subject = $mail["mail_subj"];
		$m->MsgHTML($mail["mail_body"]);
		return ($m->Send()?1:0);
	}
}