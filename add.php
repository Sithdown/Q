<?php header('Content-type: application/json');

session_start();
$showprivate = false;
$userid=0;
if(isset($_SESSION['userid'])){
    $userid = $_SESSION['userid'];
    $username = $_SESSION['username'];
    $uname = " - ".$username;
    $showprivate = true;
}

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

	$insert = "INSERT INTO logs (`ID`,`datetime`,`duration`,`description`,`tags`,`mood`,`weather`,`private`,`userid`) VALUES (NULL, :datetime, :duration, :description, :tags, :mood, :weather, :private, :userid)";

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
	if(!isset($object["private"])){
		$object["private"] = false;
	}
	$result = array(
		":datetime" => $object["datetime"],
		":duration" => $object["duration"],
		":description" => utf8_decode($object["description"]),
		":tags" => utf8_decode($object["tags"]),
		":mood" => $object["mood"],
		":weather" => $object["weather"],
		":private" => $object["private"],
		":userid" => $object["userid"]
	);

	return $result;
}