<?php
class NGC_Cron_Entry{
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
}
class NGC_Cron{
	private $content;
	private $path;
	public $entry;
	const INIT = "#<NGC-CRONTAB>", END ="#</NGC-CRONTAB>";
	public function __construct(){
		$this->entry = new NGC_Cron_Entry();
	}
	public function open($path){
		$this->path = $path;
		$this->content = null;
		if(file_exists($path)){
			$flag_found = 0;
			$this->content =[];
			$file = fopen($path, "r");
			while(($line = fgets($file)) !== false){
				if($flag_found !== 1){
					if($flag_found !== 2){
						if(strpos($line,NGC_Cron::INIT) !== false){
							$flag_found = 1;
						}else{
							array_push($this->content,$line);
						}
					}else{
						array_push($this->content,$line);
					}
				}else{
					if(strpos($line,NGC_Cron::END) !== false){
							$flag_found = 2;
					}else{
						$this->entry->parse($line);
					}	
				}
			}
			fclose($file);
		}
	}
	public function isValid(){
		return isset($this->content) && $this->content !== null;
	}
	public function apply(){
		
	}
	public function render(){
		$result ="";
		foreach ($this->content as $line) {
			$result .= $line;
		}
		$result .= NGC_Cron::INIT . PHP_EOL;
		$result .= $this->entry->render();
		$result .= NGC_Cron::END;
		return $result;
	}
}
