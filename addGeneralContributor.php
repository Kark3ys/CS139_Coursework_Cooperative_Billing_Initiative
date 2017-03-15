<?php
require 'database.php';
require 'security.php';
require 'generalNotificationFuncs.php';
$username = h($_POST["name"]);
$bid = $_POST["bid"];
$gid = $_POST["gid"];
$exitEarly = false;
if (!empty($username)) {
	$db = new Database();
	$stmt = $db->prepare("SELECT userID FROM users WHERE username = :un");
	$stmt->bindValue(":un", $username, SQLITE3_TEXT);
	$result = $stmt->execute();
	$temp = $result->fetchArray();
	if ($temp) {
		$uid = $temp["userID"];
		$retArray['uid'] = $uid;
		$stmt = $db->prepare("SELECT userID FROM billContributors
			WHERE userID = :uid AND billID = :bid");
		$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
		$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
		$result = $stmt->execute();
		$temp = $result->fetchArray();
		if (!$temp) {
			//Check they haven't already been invited.
			$retArray['already'] = 0;
			$stmt = $db->prepare("SELECT * FROM notifications
				INNER JOIN notiBill ON notifications.notiID = notiBill.notiID
				WHERE userID = :uid AND billID = :bid AND typeID IN (7, 15)");
			$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
			$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
			$result = $stmt->execute();
			$temp = $result->fetchArray();
			if (!$temp) {
				$retArray['invited'] = 0;
				
				if ($gid != 0) {
					$stmt = $db->prepare("SELECT groupID FROM groups WHERE userID = :uid");
					$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
					$result = $stmt->execute();
					$found = false;
					while($item = $result->fetchArray())
						$found = $found || ($gid == $item["groupID"]);
					if ($found) {
						$retArray['groupFind'] = 1;
						
					} else {
						$retArray['groupFind'] = 0;
						$exitEarly = true;
					}
				}
				if (!$exitEarly) {
					$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:uid, :type)");
					$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
					if ($gid != 0) $stmt->bindValue(":type", 15, SQLITE3_INTEGER);
					else $stmt->bindValue(":type", 7, SQLITE3_INTEGER);
					$stmt->execute();
					$liid = $db->lastInsertRowID();
					notiLumpBill($db, $liid, $bid);
					if ($gid != 0)
						notiLumpGroup($db, $liid, $gid);
				}
			} else {
				$retArray['invited'] = 1;
			}
		} else {
			$retArray['already'] = 1;
		}
	} else {
		$retArray['uid'] = 0;
	}
	$output = json_encode($retArray);
	echo $output;
	exit();
}
?>
<?php include "wrongTurn.php"; ?>