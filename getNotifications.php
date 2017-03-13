<?php
require 'database.php';
$db = new Database();
$uid = $_POST["uid"];

if (!empty($uid)) {
	$stmt = $db->prepare("SELECT COUNT(notiID) FROM notifications WHERE userID = :uid AND checked != 1");
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$extra = $result->fetchArray();
	$num = $extra[0];
	echo $num;
	exit();
}
?>
<?php include "wrongTurn.php"; ?>