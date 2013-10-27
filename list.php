<?php header('Content-type: application/json');

require_once "connection.php";

echo json_encode(matchCriteria());

function getDayTotal($gets=null){

    global $pdo;

	if($gets!==null){
		$sql=array();
		$paramArray=array();
		$something = false;
		$a = "";

		if((isset($gets['dategte']))||(isset($gets['datelt']))){
			$something = true;
		}

		if((isset($gets['durationgte']))||(isset($gets['durationlt']))){
			$something = true;
		}

		if(isset($gets['tags'])){
			$tags = explode(",", $gets['tags']);
			unset($gets['tags']);
		}

		if(isset($gets['terms'])){
			$terms = explode(",", $gets['terms']);
			unset($gets['terms']);
		}

		if(isset($gets['description'])){
			$description = explode(",", $gets['description']);
			unset($gets['description']);
		}

		foreach ($gets as $key => $value) {
			if(($key == "dategte")||($key == "datelt")){

				$k = "datetime";

				if($key == "dategte"){
					$sql[] = $k." >= :".$key;
				}
				else{
					$sql[] = $k." < :".$key;
				}
			}
			elseif(($key == "durationgte")||($key == "durationlt")){

				$k = "duration";

				if($key == "durationgte"){
					$sql[] = $k." >= :".$key;
				}
				else{
					$sql[] = $k." < :".$key;
				}
			}
			else{
				$sql[] = $key." = :".$key;
			}
			$paramArray[':'.$key] = $value;
			$something = true;
		}

		if($something){
			$a ="AND ";
		}

		$insert = 'SELECT DAY(datetime) AS monthday, DATE(datetime) as date, SUM(duration) AS duration_total FROM logs WHERE YEAR(datetime) = YEAR(CURDATE()) AND MONTH(datetime) = MONTH(CURDATE()) '.$a.join(' AND ',$sql);

		if(isset($terms)){
			$a = stringSearch("description",$terms,$paramArray,true);
			$insert.=$a[0];
			$paramArray = $a[1];

			$a = stringSearch("tags",$terms,$paramArray,"or");
			$insert.=$a[0];
			$paramArray = $a[1];
		}

		if(isset($description)){
			$a = stringSearch("description",$description,$paramArray);
			$insert.=$a[0];
			$paramArray = $a[1];
		}

		if(isset($tags)){
			$w=true;
			if(isset($terms)){
				$w = "or";
			}
			$a = stringSearch("tags",$tags,$paramArray,$w);
			$insert.=$a[0];
			$paramArray = $a[1];
		}

		$insert.=' GROUP BY YEAR(datetime), MONTH(datetime), DAY(datetime)';

		$ready = $pdo->prepare($insert);

		$result = $ready->execute($paramArray);
		$result = $ready->fetchAll();

	}
	else{
	    $insert = 'SELECT DAY(datetime) AS monthday, DATE(datetime) as date, SUM(duration) AS duration_total FROM logs WHERE YEAR(datetime) = YEAR(CURDATE()) AND MONTH(datetime) = MONTH(CURDATE()) GROUP BY YEAR(datetime), MONTH(datetime), DAY(datetime)';	

	    $ready = $pdo->prepare($insert);
	    $ready->execute();

	    $result = $ready->fetchAll();
	}
    
    return $result;
}

function stringSearch($field="", $array=array(), $p=array(),$startand=false){
	$r="";
	if($startand===true){
		$r.=" AND ";
	}
	elseif($startand=="or"){
		$r.=" OR (";
	}
	else{
		if(count($p)==0){
			$r.=" WHERE (";
		}
		else{
			$r.=" AND (";
		}
	}

	mb_internal_encoding("UTF-8");
	mb_regex_encoding("UTF-8");

	foreach ($array as $key => $value) {
		if($key!=0){
			$r.=" OR ";
		}
		$r.=$field." LIKE :".strtolower($field).$key;

		$p[':'.strtolower($field).$key] = '%'.mb_ereg_replace("[\.]","",$value).'%';

	}
	if($startand!==true){
		$r.=")";
	}

	$a = array($r,$p);

	return $a;
}

function matchCriteria() {
	global $pdo;
	$sql=array();
	$paramArray=array();
	$something = false;
	$a = "";

	$gets = $_GET;

	if(isset($gets['daytotals'])){
		unset($gets['daytotals']);
		return getDayTotal($gets);
	}

	if((isset($gets['dategte']))||(isset($gets['datelt']))){
		$something = true;
	}

	if((isset($gets['durationgte']))||(isset($gets['durationlt']))){
		$something = true;
	}

	if(isset($gets['tags'])){
		$tags = explode(",", $gets['tags']);
		unset($gets['tags']);
	}

	if(isset($gets['terms'])){
		$terms = explode(",", $gets['terms']);
		unset($gets['terms']);
	}

	if(isset($gets['description'])){
		$description = explode(",", $gets['description']);
		unset($gets['description']);
	}

	foreach ($gets as $key => $value) {
		if(($key == "dategte")||($key == "datelt")){

			$k = "datetime";

			if($key == "dategte"){
				$sql[] = $k." >= :".$key;
			}
			else{
				$sql[] = $k." < :".$key;
			}

		}
		elseif(($key == "durationgte")||($key == "durationlt")){

			$k = "duration";

			if($key == "durationgte"){
				$sql[] = $k." >= :".$key;
			}
			else{
				$sql[] = $k." < :".$key;
			}
		}
		else{
			$sql[] = $key." = :".$key;
		}
		$paramArray[':'.$key] = $value;
		$something = true;
	}

	if($something){
		$a ="AND ";
	}

	$insert = 'SELECT * FROM logs'.(count($paramArray)>0 ? ' WHERE '.join(' AND ',$sql) : '');

	if(isset($terms)){
		$a = stringSearch("description",$terms,$paramArray);
		$insert.=$a[0];
		$paramArray = $a[1];

		$a = stringSearch("tags",$terms,$paramArray,"or");
		$insert.=$a[0];
		$paramArray = $a[1];
	}

	if(isset($description)){
		$a = stringSearch("description",$description,$paramArray,"or");
		$insert.=$a[0];
		$paramArray = $a[1];
	}

	if(isset($tags)){
		$w=false;
		if(isset($terms)){
			$w = "or";
		}
		$a = stringSearch("tags",$tags,$paramArray,$w);
		$insert.=$a[0];
		$paramArray = $a[1];
	}

	$insert.=' ORDER BY datetime DESC';

	$ready = $pdo->prepare($insert);

	$result = $ready->execute($paramArray);
	if($result) {
		$obj = array();
		while($r = $ready->fetch()){
			$obj[] = $r;
			$obj[count($obj)-1]["description"] = utf8_encode($obj[count($obj)-1]["description"]);
			$obj[count($obj)-1]["tags"] = utf8_encode($obj[count($obj)-1]["tags"]);
			$obj[count($obj)-1]["tags"] = explode(",", $obj[count($obj)-1]["tags"]);
		}
		return $obj;

	}
	return false;
}