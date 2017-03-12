<?php session_start();
$uid = $_SESSION["uid"];
if (empty($uid)) {
	header("Location:login.php?err=2");
	exit();
}
?>
<?php require "header.php"?>
<?php
	$bid = $_GET["bid"];
	if(empty($bid) || !is_numeric($bid)) {
		echo "<div class='callout alert'>No bill found, go <a href='bills.php' class='button alert'>back</a>.</div>";
		require_once "footer.php";
		exit();
	}
	$db = new Database();
	$showBill = false;
	$editBill = false;
	$stmt = $db->prepare("SELECT owner FROM billContributors 
		WHERE userID = :uid AND billID = :bid");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$check = $result->fetchArray();
	if(!empty($check)) {
		$showBill = true;
		if ($check["owner"] != 0) $editBill = true;
	}
	if (!$showBill) {
		echo "<div class='callout alert'>No access to this bill, go <a href='bills.php' class='button alert'>back</a>.</div>";
		require_once "footer.php";
		exit();
	}
	$stmt = $db->prepare("SELECT * FROM bills	
		INNER JOIN billTypes ON bills.typeID = billTypes.typeID 
		INNER JOIN billContributors ON billContributors.billID = bills.billID
		WHERE bills.billID = :bid AND billContributors.userID = :uid");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$billData = $result->fetchArray();
	if ($billData["complete"] != 0) 
		$complete = '<span class="button success">Complete!</span> ';
	else
		$complete = '<span class="button alert">Incomplete</span> ';
	
	$stmt = $db->prepare("SELECT users.userID, users.realname FROM users
		INNER JOIN billContributors ON billContributors.userID = users.userID
		WHERE billContributors.billID = :bid AND billContributors.owner = 1");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$ownerData = $result->fetchArray();
	echo '
	<div class="column large-4">
		<h2>'.$complete.$billData["name"].'<br /><small>'.$billData["typename"].'</small></h2>
		<div class="callout">
			<h3>Total: £'.number_format($billData["total"], 2).'</h3>
			<h3>Due Date: '.$billData["dueTS"].'</h3>
			<h3>Owner: <a href="profile.php?uid='.$ownerData["userID"].'">'.$ownerData["realname"].'</a></h3>
			<hr />
			<table><tr><td>Created</td><td>'.$billData["createTS"].'</td></tr>
			<tr><td>Last Modified</td><td>'.$billData["editTS"].'</td></tr></table>
			<hr />
			<div id="controlPanel">
				Contribution: £'.$billData["ammount"].'<br />
				Paid: <input type="checkbox" name="selfPaid" ';
	if ($billData["paid"] != 0) echo 'checked ';
	if ($billData["recieved"] != 0) echo 'disabled ';
	echo '/><br />';
	if (!$editBill) echo '
				<button id="selfRemove" class="button alert">Remove Self</button><br />';
	else echo '
				Complete? <button id="completeYes" class="button success">Yes</button><button id="completeNo" class="button alert">No</button><br />
				<button id="dbill" class="button warning">Delete Bill?</button>
				<button id="dbyes" class="button alert dbbs" style="display:none">Delete Bill!</button>
				<button id="dbno" class="button dbbs" style="display:none">Never Mind</button><br />';
	echo'
				<button class="button">Save Changes</button>
			</div>
		</div>
	</div>';
	
	$stmt = $db->prepare("SELECT * FROM billContributors
	INNER JOIN users ON billContributors.userID = users.userID
	LEFT JOIN groups ON billContributors.groupID = groups.groupID
	WHERE billContributors.billID = :bid ORDER BY billContributors.groupID");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$lgid = -1;
	$firstAcc = true;
	echo '
	<div id="groupCollate" class="column large-8">
		<ul class="accordion" data-accordion data-multi-expand="true" data-allow-all-closed="true">';
	while ($user = $result->fetchArray()) {
		if ($lgid != $user["groupID"]) {
			if (!$firstAcc) {
				echo '
					</tbody>
					</table>
					Group Contribution: £<span id="gc-'.$lgid.'">0</span>';
				if ($editBill) {
					if ($lgid == 0) echo '
					<hr />
					Add New Contributors:
					<form id="addcGen">';
					else echo '
					<hr />
					Add From Group:
					<button id="saveChanges" class="button success">
					<form id="addG">';
					
					echo '
						<input type="text" name="username" pattern="[a-zA-Z0-9]+" maxlength="30" required />
						<button type="submit" class="button">Add Username</button>
						<input type="hidden" value="'.$lgid.'" name="gid" />
						<span class="errZone"></span>
					</form>';
				}
				echo '
				</div>
			</li>';
			}
			$firstAcc = false;
			echo '
			<li class="accordion-item is-active" data-accordion-item>
				<a href="#" class="accordion-title">'.$user["name"].'</a>
				<div class="accordion-content" data-tab-content>
					<table>
					<thead>
						<tr>
						<td>Name</td>
						<td>Contribution</td>
						<td>Paid?</td>';
			if ($editBill) echo '
						<td>Recieved?</td>
						<td>Remove?</td>';					
			echo '
						</tr>
					</thead>
					<tbody>';
			$lgid = $user["groupID"];
		}
		if ($user["paid"] != 0) $paid = "Yes";
		else $paid = "No";
		$ammount = number_format($user["ammount"],2);
		echo '
						<tr id="u'.$user["userID"].'">
						<td><a href="profile.php?uid='.$user["userID"].'">'.$user["realname"].'</a></td><td>';
		if ($editBill) echo '
						<input type="number" name="ammount" step="0.01" min="0" required value="'.$ammount.'"/>';
		else echo '
						<input type="hidden" value="'.$ammount.' name="ammount"/>£'.$ammount;
		echo '
						</td><td>'.$paid.'</td>';
		
		if ($editBill) echo '
						<td><input type="checkbox" name="recieve" /></td>
						<td><button class="button alert" name="remove">X</button></td>';
		echo '
						</tr>';
	}
	
	echo '
					</tbody>
					</table>
					Group Contribution: £<span class="groupContributionTally" id="gc-'.$lgid.'">0</span>';
	if ($editBill) {
		echo '
					<hr />
					<button id="saveConChanges" class="button">Save Contribution Changes</button>';
		if ($lgid == 0) echo '
					Add New Contributors:
					<form id="addcGen">';
		else echo '
					Add From Group:
					<form id="addG">';
					
		echo '
						<input type="text" name="username" pattern="[a-zA-Z0-9]+" maxlength="30" required />
						<button type="submit" class="button">Add Username</button>
						<input type="hidden" value="'.$lgid.'" name="gid" />
						<span class="errZone"></span>
					</form>';
	}
	echo '
				</div>
			</li>
		</ul>
	</div>';
?>
<script>
function updateGCT() {
	var total = 0.0;
	$(".groupContributionTally").each(function() {
		total = 0.0;
		$(this).parent().find("table tbody tr td input[name='ammount']").each(function() {
			total += parseFloat($(this).val());
		});
		$(this).html(total.toFixed(2));
	});
}
$(function() {
	updateGCT();
	$("#groupCollate").on("change", "ul li div table tbody tr td input[name='ammount']", function() {
		updateGCT();
	});
	$("#dbill").click(function() {
		$(".dbbs").toggle("fast");
	});
	$("#dbno").click(function() {
		$(".dbbs").hide("fast");
	});
});
		
</script>
<?php require_once "footer.php"?>