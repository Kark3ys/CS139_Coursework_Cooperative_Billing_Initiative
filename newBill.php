<?php session_start();
$uid = $_SESSION["uid"];
if (empty($uid)) {
	header("Location:login.php?err=2");
	exit();
}
?>
<?php require "header.php";?>
<form id="bill" action="newBill_process.php" method="POST">
	<label>Bill Name: <input type="text" name="name" pattern="[a-zA-Z0-9 ]+" maxlength="30" required /></label><br />
	<label>Total (£): <input type="number" name="total" step="0.01" min="0.01" required value="10.00"/></label><br />
	<label>Type: <select name="type" required>
	<?php 
		$db = new Database();
		$result = $db->query("SELECT * FROM billTypes");
		while ($item = $result->fetchArray()) {
			echo '<option value="'.$item["typeID"].'">'.$item["name"].'</option>';
		}
	?>
	</select></label><br />
	<label>Due Date:<br />
	<input type="date" name="due" required/>
	<button type="submit" class="button">Create Bill</button>
</form>
<?php require "footer.php";?>