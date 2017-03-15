<?php session_start();
$uid = $_SESSION["uid"];
if (empty($uid)) {
	header("Location:login.php?err=2");
	exit();
}
?>
<?php require "header.php";?>
<form id="group" action="newGroup_process.php" method="POST">
	<label>Group Name: <input type="text" name="name" pattern="[a-zA-Z0-9 ]+" maxlength="30" required /></label><br />
	<button type="submit" class="button">Create Group</button>
</form>
<?php require "footer.php";?>