<?php
function getGroupOwner($db, $gid) {
	$stmt = $db->prepare("SELECT userID FROM groupUserRel
	WHERE groupID = :gid AND owner = 1");
	$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$temp = $result->fetchArray();
	return $temp[0];
}

function getBillOwner($db, $bid) {
	$stmt = $db->prepare("SELECT userID FROM billContributors
	WHERE billID = :bid AND owner = 1");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$temp = $result->fetchArray();
	return $temp[0];
}

function notiLumpBill($db, $liid, $bid) {
	$stmt = $db->prepare("INSERT INTO notiBill(notiID, billID) VALUES(:liid, :bid)");
	$stmt->bindValue(":liid", $liid, SQLITE3_INTEGER);
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$stmt->execute();
}

function notiLumpUser($db, $liid, $uid) {
	$stmt = $db->prepare("INSERT INTO notiUser(notiID, secondUserID) VALUES(:liid, :uid)");
	$stmt->bindValue(":liid", $liid, SQLITE3_INTEGER);
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$stmt->execute();
}

function notiLumpGroup($db, $liid, $gid) {
	$stmt = $db->prepare("INSERT INTO notiGroup(notiID, groupID) VALUES(:liid, :gid)");
	$stmt->bindValue(":liid", $liid, SQLITE3_INTEGER);
	$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
	$stmt->execute();
}
?>