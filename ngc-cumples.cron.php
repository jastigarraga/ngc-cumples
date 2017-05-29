<?php
class NGC_Cron {
	public $path,$entry, $content;
	public function get_path(){
		return $this->path;
	}
	public function __construct($path){
		$this->path = $path;
		exec("crontab -l | grep -v \"$path\" > crontab.tmp ");
		exec("crontab -l | grep \"$path\" > crontab.tmp1 ");
		$file = fopen("crontab.tmp","r");
		$this->content = explode("\n",fread($file,filesize("crontab.tmp")));
		fclose($file);
		$file = fopen("crontab.tmp1","r");
		$this->entry = new NGC_Cron_Entry($this,fread($file,filesize("crontab.tmp1")));
		fclose($file);
		exec("rm crontab.tmp*");
	}
	public function apply(){
		exec("touch crontab.tmp");
		foreach($this->content as $cont){
			if(trim($cont) !== ""){
				exec("echo \"$cont\n\" >> crontab.tmp");
			}
		}
		exec("echo \"" . $this->entry->render() . "\" >> crontab.tmp");
		exec("crontab crontab.tmp");
		exec("rm crontab.tmp");
	}
}
class NGC_Cron_Entry {
	public $cron,$duration,$interval,$h,$m;
	public function render(){
		$h = $this->h;
		$m = $this->m;
		$d = isset($this->duration) && $this->duration!=0?"-".$this->duration:"";
		$i = isset($this->interval) && $this->interval!=1?"/".$this->interval:"";
		$p = $this->cron->path;
		return "$m$i $h$d * * * $p";
	}
	public function __construct($cron,$string){
		$this->cron = $cron;
		$params = explode (" ",$string,6);
		$p = explode("/",$params[0]);
		$this->m = $p[0];
		$this->interval = isset($p[1])?$p[1]:0;
		$p = explode("-",$params[1]);
		$this->h = $p[0];
		$this->duration = isset($p[1])?$p[1]:1;
	}
	public function get(){
		return [
			"h"=>$this->h,
			"m"=>$this->m,
			"interval"=>$this->interval,
			"duration"=>$this->duration
		];
	}
}