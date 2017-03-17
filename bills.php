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
<table id="bills" style="width: 100%">
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
		
		echo '<tr'.$rowColour.'><td class="billName"><a href=viewBill.php?bid='.$bill["billID"].'>'.$bill["name"]
			.'</a></td><td'.$dateColour.'>'.$bill["dueTS"].'</td><td>£'.number_format($bill["ammount"],2)
			.'</td><td>£'.number_format($bill["total"],2).'</td><td'.$paidColour.'>'.$paid.'</td>
			<td'.$rColour.'>'.$recieved.'</td><td class="groupName">'.$groupName.'</td>
			<td class="ownerName"><a href="profile.php?uid='.$ownerID.'">'.$ownerName
			.'</a></td><input type="hidden" name="comp" value='.$complete.'>
			<input type="hidden" name="paid" value="'.$bill["paid"].'">
			<input type="hidden" name="recieved" value="'.$bill["recieved"].'"></tr>';
	}
?>
</tbody>
</table>
</div>
</div>
</div>
<div class="columns large-4 medium-4">
<div class="input group">
<div class="callout" id="search">
	<input type="text" id="searchTerm" maxlength="30" required /><br />
	<fieldset id="searchTypeGroup" class="callout">
		<legend>Search By...</legend>
		<label><input type="radio" name="searchType" value="0" checked/>Bill Name</label>
		<label><input type="radio" name="searchType" value="1" />Owner Name</label>
		<label><input type="radio" name="searchType" value="2" />Group Name</label>
	</fieldset>
</div>
<div class="callout">
<label><input type="checkbox" id="showComplete" checked />Show Complete?</label>

<fieldset id="showPaidGroup" class="callout">
	<legend>Paid...</legend>
	<label><input type="radio" name="showPaid" value="0" checked/>Show All</label>
	<label><input type="radio" name="showPaid" value="1" />Show Paid</label>
	<label><input type="radio" name="showPaid" value="2" />Show Unpaid</label>
</fieldset>

<fieldset id="showRecievedGroup" class="callout">
	<legend>Paid...</legend>
	<label><input type="radio" name="showRecieved" value="0" checked/>Show All</label>
	<label><input type="radio" name="showRecieved" value="1" />Show Recieved</label>
	<label><input type="radio" name="showRecieved" value="2" />Show Not Recieved</label>
</fieldset>

</div>
<script>
function updateSearch() {
	var search = $("#searchTerm").val();
	var rows = $("tbody tr");
	var searchType = parseInt($("input[name='searchType']:checked").val());
	var tdCheck = '';
	switch (searchType) {
		case 1: tdCheck = '.ownerName';
		break;
		case 2: tdCheck = '.groupName';
		break;
		default: tdCheck = '.billName';
	}
	rows.each(function() {
		row = $(this);
		console.log(row);
		var showRow = true;
		if (search)
			showRow = (showRow && row.find("td"+tdCheck+":contains('"+search+"')").length) ? true: false;
		if (!($("#showComplete").prop("checked")))
			showRow = (showRow && row.find("input[name='comp'][value='false']").length) ? true: false;
		
		console.log(parseInt($("input[name='showPaid']:checked").val()));
		switch (parseInt($("input[name='showPaid']:checked").val())) {
			case 1: showRow = (showRow && row.find("input[name='paid'][value='1']").length) ? true: false;
			break;
			case 2: showRow = (showRow && row.find("input[name='paid'][value='0']").length) ? true: false;
			break;
		}
		
		console.log(parseInt($("input[name='showRecieved']:checked").val()));
		switch (parseInt($("input[name='showRecieved']:checked").val())) {
			case 1: showRow = (showRow && row.find("input[name='recieved'][value='1']").length) ? true: false;
			break;
			case 2: showRow = (showRow && row.find("input[name='recieved'][value='0']").length) ? true: false;
			break;
		}
		console.log(showRow);
		if (showRow) row.show("fast"); else row.hide("fast");
	});
}
$(function() {
	$("#showComplete").click(updateSearch)
	
	$("#searchTerm").on("click change keyup",updateSearch);
	$("input[name='searchType']").change(updateSearch);
	
	$("input[name='showPaid']").change(updateSearch);
	$("input[name='showRecieved']").change(updateSearch);
})
</script>
</div>
</div>
<?php require "footer.php"; ?>