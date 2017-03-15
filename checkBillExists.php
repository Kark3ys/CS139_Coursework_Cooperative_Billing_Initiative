<?php
require 'database.php';
$bid = $_POST["bid"];
$uid = $_POST["uid"];
if (!empty($bid)) {
	$db = new Database();
	$stmt = $db->prepare("SELECT billID FROM billContributors 
		WHERE billID = :bid AND userID = :uid");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$temp = $result->fetchArray();
	if ($temp) echo 1; else echo 0;
	exit();
}
?>
<?php include "wrongTurn.php" ?>