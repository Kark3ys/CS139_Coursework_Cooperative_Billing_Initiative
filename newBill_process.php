<?php
session_start();
error_reporting(E_ALL);
ini_set(“display_errors”, 1);
require 'database.php';
require 'security.php';
$db = new Database();
$name = h($_POST["name"]);
$total = floatval($_POST["total"]);
$type = h($_POST["type"]);
date_default_timezone_set('UTC');
$due = h($_POST["due"]);
if (!empty($_POST["name"])) {
	$stmt = $db->prepare("INSERT INTO bills(name, total, typeID, dueTS)
		VALUES (:name, :total, :type, :due);");
	$stmt->bindValue(":name", $name, SQLITE3_TEXT);
	$stmt->bindValue(":total", $total, SQLITE3_FLOAT);
	$stmt->bindValue(":type", $type, SQLITE3_INTEGER);
	$stmt->bindValue(":due", $due, SQLITE3_TEXT);
	$stmt->execute();
	$id = $db->lastInsertRowID();
	$stmt = $db->prepare("INSERT INTO billContributors(billID, userID, owner, ammount)
		VALUES (:bid, :uid, 1, :total)");
	$stmt->bindValue(":total", $total, SQLITE3_FLOAT);
	$stmt->bindValue(":bid", $id, SQLITE3_INTEGER);
	$stmt->bindValue(":uid", $_SESSION["uid"], SQLITE3_INTEGER);
	$stmt->execute();
	header("Location:viewBill.php?bid=".$id);
}
?>
<?php include "wrongTurn.php"; ?>
