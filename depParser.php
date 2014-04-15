<?
/**
* DEP-file parser
* DEP files are created by "snapshot" LaTeX package
* This is a simple parser for DEP files. Look for usage example above
* License: GNU GPL 3
* Author: Sergiy Lilikovych
* Date: 16.04.2014
* NTU "KhPI"
**/

define('FILTER_NONE',-1);
define('FILTER_NAME',0);
define('FILTER_TYPE',1);
define('FILTER_VERSION',2);
class TDEPRecord{
	private $type, $name, $version;
	
	function set_name($data){
		$this->name = $data;
		return $this;
	}
	
	function set_type($data){
		$this->type = $data;
		return $this;
	}
	
	function set_version($data){
		$this->version = $data;
		return $this;
	}
	
	function get_name(){
		return $this->name;
	}
	
	function get_type(){
		return $this->type;
	}
	
	function get_version(){
		return $this->version;
	}
	
	public function get($filter=FILTER_NONE){
		switch($filter){
			case FILTER_NAME:
				return $this->get_name();
				break;
			case FILTER_TYPE:
				return $this->get_type();
				break;
			case FILTER_VERSION:
				return $this->get_version();
				break;
			case FILTER_TYPE:
			default:
				return array(FILTER_NAME=>$this->get_name(),
								FILTER_TYPE=>$this->get_type(),
								FILTER_VERSION=>$this->get_version());
		}
	}
	
	function TDEPRecord($type, $name, $version){
		$this->set_name($name)->set_type($type)->set_version($version);
	}
}
class TDEPFile{
	private $records = array();
	public $unique = false;//set it to TRUE, to allow only unique DEP-records
	
	public function parse($str){
		$regex = "/\\*{(.*?)}.*?{(.*?)}.*?{(.*?)}/";
		$matches = array();
		preg_match($regex,$str,$matches);
		if(count($matches)!==4){
			return false;
		}
		
		if($this->unique){
			$signature = md5(strtolower($matches[1].$matches[2].$matches[3]));//signature to check if record unique
			if(!isset($this->records[$signature])){
				$this->pushRecord($signature,$matches);
			}else{
				return false;
			}
		}else{
				$this->pushRecord(count($this->records),$matches);
		}
		return count($matches);
	}
	
	private function pushRecord($sign,$matches){
			$this->records[$sign] = new TDEPRecord($matches[1],$matches[2],$matches[3]);
			return true;
	}
	
	public static function from_array($array){
		$dep = new TDEPFile();
		$dep->records = $array;
		return $dep;
	}
	
	public function get_records(){
		return $this->records;
	}
	
	public function filter_by($filter,$search=null){
		$results = array();
		foreach($this->records as $sign=>$record){
				if(!is_null($search) and stristr($record->get($filter),$search)!==false){
					$results[$sign] = $record;
				}
		}
		return TDEPFile::from_array($results);
	}
	
}
/**
* USAGE EXAMPLE
* Reads DEP-file line by line and pushes records into TDEPFile instance.
* Then filters records by type, looks for 'package' records.
**/
//1. Read file and push records
$f = file('./report.dep');
$dep = new TDEPFile();
foreach($f as $line){
	$dep->parse($line);
}
//2. Filter records and print them
$filtered = $dep->filter_by(FILTER_TYPE,'package')->get_records(); //multiple filtering allowed, e.g.: filter_by(...)->filter_by(...)->get_records() because filter_by returns an instance of TDEPFile
foreach($filtered as $record){
	print_r($record->get());
	print '<br>';
}