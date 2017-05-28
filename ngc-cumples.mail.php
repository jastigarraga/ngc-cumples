<?php

Class NGC_Mail_Manager {
	private static function replace($string,$customer){
		$replaces = [
			"{{0}}"=>$customer->name,
			"{{1}}"=>$customer->surname1,
			"{{2}}"=>$customer->surname2
		];
		foreach($replaces as $key => $value){
			$string = str_replace($key, $value, $string);
		}
		return $string;
	}
	public static function run($db){
		require_once plugin_dir_path(__FILE__) . "PHPMailer/PHPMailerAutoload.php";
		$mailconf = $db->get_results("SELECT * FROM ngc_config WHERE _key LIKE 'mail_%'");
		$template = $db->get_var("SELECT text FROM ngc_template LIMIT 1");
		$subject = $db->get_var("SELECT value FROM ngc_config WHERE _key = 'subject'");
		$customers = $db->get_results("SELECT * FROM ngc_customer WHERE (last_sent < STR_TO_DATE(DATE_FORMAT(NOW(),'%d/%m/%Y'),'%d/%m/%Y') OR last_sent IS NULL
				) AND DATE_FORMAT(date,'%d/%m') = DATE_FORMAT(NOW(),'%d/%m')");
		$mail = [];
		foreach ($mailconf as $entry){
			$mail[$entry->_key] = $entry->value;
		}
		$msgs_sent = 0;
		foreach($customers as $customer){
			$mail["mail_subj"] = NGC_Mail_Manager::replace($subject, $customer);
			$mail["mail_body"] = NGC_Mail_Manager::replace($template, $customer);
			$mail["mail_to"] = $customer->email;
			$mail["mail_to_name"] = $customer->name;
			$sent = NGC_Mail_Manager::send($mail);
			if($sent){
				$msgs_sent +=1;
				$db->query("UPDATE ngc_customer SET last_sent = NOW()
							WHERE idCustomer = ".$customer->idCustomer);
			}
		}
		return $msgs_sent;

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
		$m->AddAddress($mail["mail_to"],$mail["mail_to_name"]);
		$m->Subject = $mail["mail_subj"];
		$m->MsgHTML($mail["mail_body"]);
		return ($m->Send()?1:0);
	}
}