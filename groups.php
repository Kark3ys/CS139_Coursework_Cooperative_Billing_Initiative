<?php session_start();
$uid = $_SESSION["uid"];
if (empty($uid)) {
	header("Location:login.php?err=2");
	exit();
}
?>
<?php require "header.php"; ?>
<div class="callout">
<?php
	$db = new Database();
	$stmt = $db->prepare("SELECT * from groups
	INNER JOIN groupUserRel USING(groupID)
	WHERE userID = :uid");
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	while($group = $result->fetchArray()) {
		echo (($group["owner"] == 1) ? '* ':'').
			"\t".'<a href="viewGroup.php?gid='.$group["groupID"].'">'.$group["name"].'</a><br />'."\n";
	}
?>
</div>
<?php require "footer.php"; ?>