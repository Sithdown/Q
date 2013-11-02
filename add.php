<?php header('Content-type: application/json');

require_once "connection.php";

echo json_encode(add());

function add() {
	global $pdo;
	$sql=array();
	$paramArray=array();

	if(isset($_POST)){
		$posts = $_POST;
	}
	if($posts==null){
		$posts = $_GET;
	}

	$insert = "INSERT INTO logs (`ID`,`datetime`,`duration`,`description`,`tags`,`mood`,`weather`) VALUES (NULL, :datetime, :duration, :description, :tags, :mood, :weather)";

	$ready = $pdo->prepare($insert);
	$result = $ready->execute(prepareData($posts,$insert));
	$lastid = $pdo->lastInsertId();

	if($result){
		$tags = explode(",",utf8_decode($posts["tags"]));
		foreach ($tags as $key => $tag) {
			if(($tag!="")&&($tag!=null)){
				$ins = "SELECT ID FROM tags WHERE tag = :tag";
				$rdy = $pdo->prepare($ins);
				$rdy->execute(array(":tag"=>$tag));
				$rst = $rdy->fetchAll();
				if($rst!=null){ // TAG ALREADY EXISTS
					$tagID = $rst[0]["ID"];
				}
				else{ //NEW TAG
					$ins = "INSERT INTO tags (`ID`,`tag`) VALUES (NULL, :tag)";
					$rdy = $pdo->prepare($ins);
					$rdy->execute(array("tag"=>$tag));
					$tagID = $pdo->lastInsertId();
				}
				$ins = "INSERT INTO log_tags (`ID`,`tag_id`,`log_id`) VALUES (NULL, :tagid, :logid)";
				$rdy = $pdo->prepare($ins);
				$rdy->execute(array("tagid"=>$tagID,"logid"=>$lastid));
			}
		}
	}

	return $result;
}

function prepareData($object,$ins){
	$result = array(
		":datetime" => $object["datetime"],
		":duration" => $object["duration"],
		":description" => utf8_decode($object["description"]),
		":tags" => utf8_decode($object["tags"]),
		":mood" => $object["mood"],
	);

	return $result;
}