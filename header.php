<?php 
if(!isset($_SESSION)) {
	session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'database.php';
$isLog = !empty($_SESSION["uid"]);
if ($isLog) {
	$db = new Database();
	$stmt = $db->prepare("SELECT realname FROM users WHERE userID = :uid");
	$stmt->bindValue(":uid", $_SESSION["uid"], SQLITE3_INTEGER);
	$result = $stmt->execute();
	$temp = $result->fetchArray();
	$name = $temp["realname"];
}
?>
<!DOCTYPE html>
<html class="no-js" lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cooperative Billing Initiative</title>
    <link rel="stylesheet" href="css/foundation.css">
    <link rel="stylesheet" href="css/app.css">
		<script src="js/jquery-3.1.1.min.js"></script>
  </head>
</head>
<body>
		<div class="large-12 columns row">
			<div class="top-bar">
				<div class="top-bar-left">
					<ul class="dropdown menu" data-dropdown-menu>
						<li class="menu-text"><a href="index.php">Cooperatvie Billing Initiative</a></li>
						<?php if (!$isLog) echo '<li><a href="login.php#login">Login/Register</a></li>';?>
						<li>
							<a href="bills.php">Bills</a>
							<ul class="menu vertical">
								<li><a href="newBill.php">New Bill</a></li>
							</ul>
						</li>
						<li>
							<a href="groups.php">Groups</a>
							<ul class="menu vertical">
								<li><a href="newGroup.php">New Group</a></li>
							</ul>
						</li>
					</ul>
				</div>
				<?php if ($isLog) echo '
				<input type="hidden" id="seshUser" value="'.$_SESSION["uid"].'" />
				<script>
				$(function() {
					var id = $("#seshUser").attr("value");
					function checkNoti() {
						$.post("getNotifications.php", {uid: id}, function(data, status) {
								$("#notinumber").html(data);
							});
					}
					checkNoti();
					setInterval(checkNoti, 2000);
				});
					
				</script>
				<div class="top-bar-right">
					<ul class="menu">
						<li><a href="notifications.php">Notifications (<span id="notinumber"></span>)</a></li>
						<li><a href="profile.php">'.$name.'</a></li>
						<li><a href="logout.php">Logout</a></li>
					</ul>
				</div>';?>
			</div>
	</div>
	&nbsp;
	<div class="row">
		<div class="columns large-12">