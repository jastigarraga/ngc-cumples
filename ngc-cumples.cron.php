<?php
class NGC_Cron_Entry{
	public function __construct($path){
		$this->path = $path;
	}
	public $minute,$hour,$path;
	public function render($try=1,$interval=20){
		return $this->minute . ' '. $this->hour .' * * * ' . $this->path;

	}
	public function parse($line){
		$params = explode(" * * * ", $line);
		if(count($params) === 2){
			$this->path = $params[1];
			$params = explode(" ",$params[0]);
			if(count($params) === 2){
				$this->minute = $params[0];
				$this->hour = $params[1];
			}
		}
		return false;
	}
	public function get(){
		return [
			"h"=>(isset($this->hour)?$this->hour:null),
			"m"=>(isset($this->minute)?$this->minute:null)
		];
	}
}
class NGC_Cron{
	private $content=[];
	private $path;
	public $entry,$tries = 1,$interval = 20;
	public function __construct($path){
		$result = [];
		$this->path = $path;
		exec("crontab -l",$this->content);
		foreach($this->content as $entry){
			$p = explode(" * * * ",$path);
			if(isset($p[1]) && $path == $p && !isset($this->entry)){
				$this->entry = new NGC_Cron_Entry($path);
				$this->entry->parse($entry);
			}else{
				array_push($result, $entry);
			}
		}
		$this->content = $result;
	}
	public function apply(){
		exec("touch crontab.tmp");
		die(shell_exec("ls"));
	}
}
