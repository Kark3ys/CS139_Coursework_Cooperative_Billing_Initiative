<?php session_start();
$uid = $_SESSION["uid"];
if (empty($uid)) {
	header("Location:login.php?err=2");
	exit();
}
?>
<?php require "header.php"; ?>
<?php
if(!empty($_GET["err"]) && is_numeric($_GET["err"])) {
	echo '
<div class="row">
	<div class="columns large-12">
		<div class="callout alert">';
	switch ($_GET["err"]) {
		case 1:
			echo 'Bill has been deleted by owner';
		break;
		case 2:
			echo 'You left the bill.';
		break;
		default:
			echo 'Generic Error';
	}
	echo '
		</div>
	</div>
</div>
<div class="row">';
}
?>
<div class="columns medium-8">
<div class="callout">
<div class="table-scroll">
<table id="bills">
	<thead>
		<tr>
			<td>Bill Name</td>
			<td>Due Date</td>
			<td>Contribution</td>
			<td>Total</td>
			<td>Paid</td>
			<td>Recieved</td>
			<td>Group</td>
			<td>Owner</td>
		</tr>
	</thead>
	<tbody>
<?php
	$db = new Database();
	$stmt = $db->prepare("SELECT * FROM bills
	INNER JOIN billContributors ON bills.billID = billContributors.billID
	INNER JOIN users ON users.userID = billContributors.userID
	WHERE users.userID = :uid	ORDER BY bills.dueTS");
	
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	while ($bill = $result->fetchArray()) {
		$stmt = $db->prepare("SELECT * FROM bills
		INNER JOIN billContributors ON billContributors.billID = bills.billID
		INNER JOIN users ON billContributors.userID = users.userID
		WHERE bills.billID = :bid AND billContributors.owner = 1;");
		$stmt->bindValue(":bid", $bill["billID"], SQLITE3_INTEGER);
		$tempresult = $stmt->execute();
		$temp = $tempresult->fetchArray();
		$ownerName = $temp["realname"];
		$ownerID = $temp["userID"];
		date_default_timezone_set("UTC");
		if (Time() > strToTime($bill["dueTS"])) {
			$dateColour = ' style="background-color: #cc4b37" ';
		} elseif (Time() + (7 * 24 * 60 * 60) > strToTime($bill["dueTS"])) {
			$dateColour=' style="background-color: #ffae00" ';
		} else {
			$dateColour=' style="background-color: #1779ba" ';
		}
		
		if ($bill["paid"]!=0) {
			$paid = "Yes";
			$paidColour = ' style="background-color: #3adb76" ';
		} else {
			$paid = "No";
			$paidColour = ' style="background-color: #cc4b37" ';
		}
		
		if ($bill["recieved"] != 0) {
			$recieved = "Yes";
			$rColour = ' style="background-color: #3adb76" ';
		} else {
			$recieved = "No";
			$rColour = ' style="background-color: #cc4b37" ';
		}
		
		if ($bill["complete"] != 0) {
			$complete = "true";
			$rowColour = ' style="background-color: #3adb76" ';
		} else {
			$complete = "false";
			$rowColour='';
		}
		
		if ($bill["groupID"] != 0) {
			$stmt = $db->prepare("SELECT name FROM groups WHERE groupID = :gid");
			$stmt->bindValue(":gid", $bill["groupID"], SQLITE3_INTEGER);
			$tempresult = $stmt->execute();
			$temp = $tempresult->fetchArray();
			$groupName = '<a href="viewGroup.php?gid='.$bill["groupID"].'">'.$temp["name"].'</a>';
		} else {
			$groupName = "N/A";
		}	
		
		echo '<tr'.$rowColour.'><td><a href=viewBill.php?bid='.$bill["billID"].'>'.$bill["name"]
			.'</a></td><td'.$dateColour.'>'.$bill["dueTS"].'</td><td>£'.number_format($bill["ammount"],2)
			.'</td><td>£'.number_format($bill["total"],2).'</td><td'.$paidColour.'>'.$paid.'</td>
			<td'.$rColour.'>'.$recieved.'</td><td>'.$groupName.'</td>
			<td><a href="profile.php?uid='.$ownerID.'">'.$ownerName
			.'</a></td><input type=hidden name="comp" value='.$complete.'></tr>';
	}
?>
</tbody>
</table>
</div>
</div>
</div>
<div class="columns large-4 medium-4">
<div class="input group">
<form id="search">
	<input type="text" name="searchTerm" maxlength="30" required />
	<fieldset id="searchTypeGroup" class="callout">
		<legend>Search By...</legend>
		<label><input type="radio" name="searchType" value="0" />General Search</label>
		<label><input type="radio" name="searchType" value="1" />Bill Name</label>
		<label><input type="radio" name="searchType" value="2" />Owner Name</label>
	</fieldset>
</form>
<label><input type="checkbox" id="showComplete" checked />Show Complete?</label>
<script>
$(function() {
	$("#showComplete").click(function() {
		var ch = $(this).prop("checked");
		if (ch) $("input[name='comp'][value='true']").parent().show("fast");
		else $("input[name='comp'][value='true']").parent().hide("fast");
	})
})
</script>
</div>
</div>
<?php require "footer.php"; ?>