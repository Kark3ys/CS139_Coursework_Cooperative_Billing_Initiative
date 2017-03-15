<?php
require 'database.php';
require 'generalNotificationFuncs.php';
$uid = $_POST["uid"];
$bid = $_POST["bid"];
$paid = $_POST["paid"];
if (!empty($bid)) {
	$db = new Database();
	$ownerID = getBillOwner($db, $bid);
	
	$stmt = $db->prepare("UPDATE billContributors 
		SET paid = :paid
		WHERE userID = :uid AND billID = :bid");
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$stmt->bindValue(":paid", $paid, SQLITE3_INTEGER);
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$stmt->execute();
	
	if($uid == $ownerID) {
		$stmt = $db->prepare("UPDATE billContributors 
			SET recieved = :paid 
			WHERE userID = :uid AND billID = :bid");
		$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
		$stmt->bindValue(":paid", $paid, SQLITE3_INTEGER);
		$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
		$stmt->execute();
		$retArray['upRec'] = 1;
	} else {
		$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:oid, :type)");
		if ($paid != 0) $stmt->bindValue(":type", 9, SQLITE3_INTEGER);
		else $stmt->bindValue(":type", 18, SQLITE3_INTEGER);
		$stmt->bindValue(":oid", $ownerID, SQLITE3_INTEGER);
		$stmt->execute();
		$liid = $db->lastInsertRowID();
		$retArray['upRec'] = 0;
		notiLumpBill($db, $liid, $bid);
		notiLumpUser($db, $liid, $uid);
	}
	$output = json_encode($retArray);
	echo $output;
	exit();
}
?>
<?php include "wrongTurn.php"; ?>