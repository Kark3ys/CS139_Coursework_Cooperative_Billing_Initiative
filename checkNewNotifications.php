<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
sleep(2);
require 'database.php';
require 'generalNotificationFuncs.php';
$uid = $_POST["uid"];
date_default_timezone_set('UTC');
$checkTime = Date('Y-m-d H:i:s', floatval($_POST["checkTime"]));
if (!empty($uid)) {
	$db = new Database();
	
	$stmt = $db->prepare("SELECT DISTINCT 
	users.realname, bills.name, groups.name, notifications.custmsg, 
	notifications.typeID, notiUser.secondUserID, notiBill.billID, 
	notiGroup.groupID, notifications.checked, notifications.addTS, 
	notiTypes.message, notifications.notiID
	FROM notifications
	INNER JOIN notiTypes ON notiTypes.typeID = notifications.typeID
	LEFT JOIN notiBill ON notiBill.notiID = notifications.notiID
	LEFT JOIN notiGroup ON notiGroup.notiID = notifications.notiID
	LEFT JOIN notiUser ON notiUser.notiID = notifications.notiID
	LEFT JOIN bills ON notiBill.billID = bills.billID
	LEFT JOIN groups ON notiGroup.groupID = groups.groupID
	LEFT JOIN users ON notiUser.secondUserID = users.userID
	WHERE addTS > :check ORDER BY addTS");
	$stmt->bindValue(":check", $checkTime, SQLITE3_TEXT);
	$result = $stmt->execute();
	$retArray = array();
	while($noti = $result->fetchArray()) {
		array_push($retArray, $noti);
	}
	$output = json_encode($retArray);
	echo $output;
	exit();
}
?>
<?php include "wrongTurn.php";?>