<?php header('Content-type: application/json');

require_once "connection.php";

echo json_encode(matchCriteria());

function aQuery($select,$append,$gets=null){
	global $pdo;

	if($gets!==null){
		$sql=array();
		$paramArray=array();
		$something = false;
		$a = "";



		if((isset($gets['dategte']))||(isset($gets['datelte']))){
			$something = true;
		}

		if((isset($gets['durationgte']))||(isset($gets['durationlte']))){
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
			if(($key == "dategte")||($key == "datelte")){

				$k = "datetime";

				if($key == "dategte"){
					$sql[] = $k." >= :".$key;
				}
				else{
					$sql[] = $k." < :".$key;
				}
			}
			elseif(($key == "durationgte")||($key == "durationlte")){

				$k = "duration";

				if($key == "durationgte"){
					$sql[] = $k." >= :".$key;
				}
				else{
					$sql[] = $k." < :".$key;
				}
			}
			elseif($key == "containsurls"){
				$key = "regexurl";
				$sql[] = "description REGEXP :".$key;
				$value = "(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}";
			}
			elseif($key == "containstwitter"){
				$key = "regextwitter";
				$sql[] = "description REGEXP :".$key;
				$value = "[@]+[A-Za-z0-9_]+";
			}
			else{
				$sql[] = $key." = :".$key;
			}
			$paramArray[':'.$key] = $value;
			$something = true;
		}

		if(strpos($select,"WHERE")===FALSE){
			$a = " WHERE";
		}
		else{
			$a = " AND";
		}

		$insert = $select.$a.join(' AND ',$sql);

		if(isset($terms)){
			$a = stringSearch("description",$terms,$paramArray,"or");
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
			$w=true;
			if(isset($terms)){
				$w = "or";
			}
			$a = stringSearch("tags",$tags,$paramArray,"or");
			$insert.=$a[0];
			$paramArray = $a[1];
		}

		$insert.=' '.$append;

		$insert = str_replace("WHERE AND", "WHERE", $insert);
		$insert = str_replace("AND AND", "AND", $insert);
		$insert = str_replace("AND OR", "AND", $insert);
		$insert = str_replace("WHERE GROUP", "GROUP", $insert);
		$insert = str_replace("AND GROUP", "GROUP", $insert);

		
		$ready = $pdo->prepare($insert);

		$result = $ready->execute($paramArray);
		$result = $ready->fetchAll();

	}
	else{
	    $insert = $select.' '.$append;

	    $ready = $pdo->prepare($insert);
	    $ready->execute();

	    $result = $ready->fetchAll();
	}

    return $result;
}

function getActivityByWeekDay($gets=null){

	global $pdo;

	$select = 'SELECT WEEKDAY(datetime) AS weekday, SUM(duration) AS duration_total FROM logs';
	$append ='GROUP BY WEEKDAY(datetime)';

	$result = aQuery($select,$append,$gets);
    return $result;
}

function getAllTags($gets=null){
	global $pdo;
	$ready = $pdo->prepare("SELECT tag FROM tags ORDER BY tag ASC");
	$ready->execute();
	$result = $ready->fetchAll();
	$r = array();
	foreach ($result as $key => $value) {
		if(!in_array($value["tag"], $r)){
			$r[]=utf8_encode($value["tag"]);
		}
	}
	return $r;
}

function getMostUsedTags($gets=null){
	$select = "SELECT tags.ID AS ID, tags.tag AS tag, COUNT(tag_id) AS tag_total FROM tags INNER JOIN log_tags ON tags.ID = log_tags.tag_id";
	$append = "GROUP BY tag ORDER BY tag_total DESC";
	$result = aQuery($select,$append,$gets);
	return $result;
}

function getAverageMoodPerDuration($gets=null){

	//SELECT duration, COUNT(mood) AS mediciones, AVG(mood) AS avg_mood FROM logs WHERE duration >= 60 GROUP BY duration

	$select = 'SELECT duration, COUNT(mood) AS mediciones, AVG(mood) AS avg_mood FROM logs WHERE duration >= 60';
	$append ='GROUP BY duration';

	$result = aQuery($select,$append,$gets);
    return $result;
}

function getTagsByWeekDay($gets=null){

	/*

	SELECT tags.ID AS tag_id, tags.tag AS tag_name, WEEKDAY(logs.datetime), COUNT(log_tags.tag_id) AS tag_total
	FROM(
		tags
		INNER JOIN
		log_tags
		ON
			tags.ID = log_tags.tag_id
	)
	INNER JOIN
	logs
	ON
		logs.ID = log_tags.log_id
	GROUP BY WEEKDAY(logs.datetime), tag_id

	*/

	$select = 'SELECT tags.ID AS tag_id, tags.tag AS tag_name, WEEKDAY(logs.datetime), COUNT(log_tags.tag_id) AS tag_total FROM( tags INNER JOIN log_tags ON tags.ID = log_tags.tag_id ) INNER JOIN logs ON logs.ID = log_tags.log_id';
	$append ='GROUP BY WEEKDAY(logs.datetime), tag_id';

	$result = aQuery($select,$append,$gets);
    return $result;
}

function getActivityByMonthDay($gets=null){

	global $pdo;

	$select = 'SELECT MONTHDAY(datetime) AS monthday, SUM(duration) AS duration_total FROM logs';
	$append ='GROUP BY MONTHDAY(datetime)';

	$result = aQuery($select,$append,$gets);
    return $result;
}

function getActivityByMonth($gets=null){

	global $pdo;

	$select = 'SELECT MONTH(datetime) AS month, SUM(duration) AS duration_total FROM logs';
	$append ='GROUP BY MONTH(datetime)';

	$result = aQuery($select,$append,$gets);
    return $result;
}

function getMonthTotals($gets=null){

	$select = 'SELECT DAY(datetime) AS monthday, DATE(datetime) as date, SUM(duration) AS duration_total FROM logs WHERE YEAR(datetime) = YEAR(CURDATE()) AND MONTH(datetime) = MONTH(CURDATE())';
	$append = 'GROUP BY YEAR(datetime), MONTH(datetime), DAY(datetime)';

	$result = aQuery($select,$append,$gets);
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

		$p[':'.strtolower($field).$key] = '%'.$value.'%';
		//$p[':'.strtolower($field).$key] = '%'.mb_ereg_replace("[\.]","",$value).'%';

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

	if(isset($gets['getalltags'])){
		unset($gets['getalltags']);
		return getAllTags($gets);
	}

	if(isset($gets['activitybymonthday'])){
		unset($gets['activitybymonthday']);
		return getActivityByMonthDay($gets);
	}

	if(isset($gets['activitybymonth'])){
		unset($gets['activitybymonth']);
		return getActivityByMonth($gets);
	}

	if(isset($gets['activitybyweekday'])){
		unset($gets['activitybyweekday']);
		return getActivityByWeekDay($gets);
	}

	if(isset($gets['tagsbyweekday'])){
		unset($gets['tagsbyweekday']);
		return getTagsByWeekDay($gets);
	}

	if(isset($gets['daytotals'])){
		unset($gets['daytotals']);
		return getMonthTotals($gets);
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
		if(($key == "dategte")||($key == "datelte")){

			$k = "datetime";

			if($key == "dategte"){
				$sql[] = $k." >= :".$key;
			}
			else{
				$sql[] = $k." <= :".$key;
			}

		}
		elseif(($key == "durationgte")||($key == "durationlte")){

			$k = "duration";

			if($key == "durationgte"){
				$sql[] = $k." >= :".$key;
			}
			else{
				$sql[] = $k." <= :".$key;
			}
		}
		elseif($key == "containsurls"){
			$key = "regexurl";
			$sql[] = "description REGEXP :".$key;
			$value = "(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}";
		}
		elseif($key == "containstwitter"){
			$key = "regextwitter";
			$sql[] = "description REGEXP :".$key;
			$value = "[@]+[A-Za-z0-9_]+";
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