<?php
require 'database.php';
$bid = $_POST["bid"];
if (!empty($bid)) {
	$db = new Database();
	$stmt = $db->prepare("SELECT billID FROM bills WHERE billID = :bid");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$temp = $result->fetchArray();
	if ($temp) echo 1; else echo 0;
	exit();
}
?>
<?php include "wrongTurn.php" ?>