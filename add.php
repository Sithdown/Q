<?php header('Content-type: application/json');

$user = "";
$pass = "";

echo json_encode(add());

function add() {
	global $user, $pass;
	$pdo = new PDO('mysql:host=localhost;dbname=q', $user, $pass);
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