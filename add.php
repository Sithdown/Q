<?php header('Content-type: application/json');

require_once "connection.php";

echo json_encode(add());

function add() {
	global $pdo;
	$sql=array();
	$paramArray=array();

	$posts = $_POST;

	$insert = "INSERT INTO logs (`ID`,`datetime`,`duration`,`type`,`description`,`tags`,`mood`) VALUES (NULL, :datetime, :duration, :type, :description, :tags, :mood)";

	$ready = $pdo->prepare($insert);
	$result = $ready->execute(prepareData($posts,$insert));

	return $result;
}

function prepareData($object,$ins){
	$result = array(
		":datetime" => $object["datetime"],
		":duration" => $object["duration"],
		":type" => $object["type"],
		":description" => utf8_decode($object["description"]),
		":tags" => utf8_decode($object["tags"]),
		":mood" => $object["mood"],
	);

	return $result;
}