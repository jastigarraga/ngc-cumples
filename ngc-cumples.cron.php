<?php
class NGC_Cron_Entry{
	public function __construct($path){
		$this->path = $path;
	}
	public $minute,$hour,$path;
	public function render(){
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
		
	}
}
class NGC_Cron{
	private $content=[];
	private $path;
	public $entry;
	public function __construct($path){
		$reult = [];
		$this->entry = new NGC_Cron_Entry($path);
		$this->path == $path;
		shell_exec("crontab -l",$result);
		foreach($this->content as $entry){
			$p = explode(" * * * ",$path);
			if(isset($p[1]) && $path == $p){
				$entry->parse($entry);
			}else{
				array_push($result, $entry);
			}
		}
		$this->content = $result;
	}
	public function apply(){
		exec("touch crontab.tmp");
		exec("echo <<EOT >> crontab.tmp");
		foreach($this->content as $e){
			exec("echo \"$e\"");
		}
		exec("echo \"".$this->entry->render()."\"");
		exec("EOT");
		exec("crontab crontab.tmp");
		exe("rm crontab.tmp");
	}
}
