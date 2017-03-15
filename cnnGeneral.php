<?php
require 'database.php';
require 'generalNotificationFuncs.php';
$uid = $_POST["uid"];
$gid = $_POST["gid"];
$bid = $_POST["gid"];
if (!empty($uid) || !empty($gid) || !empty($bid)) {
	$db = new Database();
	if (!empty($bid)) {
		$stmt = $db->prepare("SELECT name FROM bills WHERE billID = :bid");
		$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	} elseif(!empty($uid)) {
		$stmt = $db->prepare("SELECT realname FROM users WHERE userID = :uid");
		$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	} elseif(!empty($gid)) {
		$stmt = $db->prepare("SELECT name FROM groups WHERE groupID = :gid");
		$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
	}
	$result = $stmt->execute();
	$temp = $result->fetchArray();
	echo $temp[0];
	exit();
}
?>
<?php include "wrongTurn.php";?>