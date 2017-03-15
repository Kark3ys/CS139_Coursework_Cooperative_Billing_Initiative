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
	
	$stmt = $db->prepare("SELECT billID FROM bills WHERE billID = :bid");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$check = $result->fetchArray();
	if(empty($check)) { 
		echo "<div class='callout alert'>No bill found, go <a href='bills.php' class='button alert'>back</a>.</div>";
		require_once "footer.php";
		exit();
	}
	
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
		$complete = '<span class="button success" id="completeState" value="1">Complete!</span> ';
	else
		$complete = '<span class="button alert" id="completeState" value="0">Incomplete</span> ';
	
	$stmt = $db->prepare("SELECT users.userID, users.realname FROM users
		INNER JOIN billContributors ON billContributors.userID = users.userID
		WHERE billContributors.billID = :bid AND billContributors.owner = 1");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$ownerData = $result->fetchArray();
	echo '
	<div class="column large-4">
		'.$complete.'<h2><span id="billName">'.$billData["name"].'</span><br /><small>'.$billData["typename"].'</small></h2>
		<div class="callout">
			<h3>Total: £';
	if ($editBill) 
		echo '<input type="number" id="billTotal" step="0.01" min="0.01" required value="'.$billData["total"].'"/>
					<span id="saveTotal" class="button">Save Total</span>
					<span id="saveTotalErr" style="display:none"></span>';
	else echo number_format($billData["total"], 2, '.', '');
	echo '</h3>
			<h3>Due Date: '.$billData["dueTS"].'</h3>
			<h3>Owner: <a href="profile.php?uid='.$ownerData["userID"].'">'.$ownerData["realname"].'</a></h3>
			<hr />
			<table><tr><td>Created</td><td>'.$billData["createTS"].'</td></tr>
			<tr><td>Last Modified</td><td>'.$billData["editTS"].'</td></tr></table>
			<hr />
			<div id="controlPanel">
				<input type="hidden" id="curBill" value="'.$bid.'" />
				<input type="hidden" id="editBill" value="'.$editBill.'" />
				<input type="hidden" id="ownerID" value="'.$ownerData["userID"].'" />
				Contribution: £<span id="selfCon">'.number_format($billData["ammount"], 2, '.', '').'</span><br />
				Paid: <input type="checkbox" id="selfPaid" ';
	if ($billData["paid"] != 0) echo 'checked ';
	if ($billData["recieved"] != 0) echo 'disabled ';
	echo '/><br />
				<button class="button" id="saveSelfPayChange">Save Changes</button><br />
				<span id="updatePayMsg" style="display:none"></span></br>';
	if (!$editBill) echo '
				<button id="selfRemove" class="button alert">Stop Contributing</button><br />';
	else {
		echo '
				Complete? <br/><button id="completeYes" class="button success">Yes</button><button id="completeNo" class="button alert">No</button><br />
				<button id="dbill" class="button warning">Delete Bill?</button>
				<button id="dbyes" class="button alert dbbs" style="display:none">Delete Bill!</button>
				<button id="dbno" class="button dbbs" style="display:none">Never Mind</button><br />
				Select Group:
				<select id="groupSelect">
					<option value="0">Select a Group</option>
					';
		$stmt = $db->prepare("SELECT DISTINCT groups.groupID, groups.name FROM groupUserRel
			INNER JOIN groups ON groupUserRel.groupID = groups.groupID
			LEFT JOIN billContributors ON groupUserRel.groupID = billContributors.groupID
			WHERE (billID != :bid OR billID IS NULL) AND groupUserRel.userID = :uid");
		$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
		$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
		$result = $stmt->execute();
		while ($item = $result->fetchArray()) {
			echo '<option value="'.$item[0].'">'.$item[1].'</option>
					';
		}
		echo'
				</select>
				<button id="addWholeGroup" class="button">Add Group</button><br />
				<span id="addGroupMsg" style="display:none"></span>';
	}
	echo'
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
					Group Contribution: £<span class="groupContributionTally" id="gc-'.$lgid.'">0</span>';
				if ($editBill) {
					echo ' 
					<hr />					
					<button class="saveConChanges button">Save Contribution Changes</button><br />
					<span class="saveConChangeMsg"></span><br />';
					if ($lgid == 0) echo '
					Add New Contributors:
					<div class="addcGen">';
					else echo '
					Add From Group:
					<div class="addG">';
					
					echo '
						<input type="text" name="username" pattern="[a-zA-Z0-9]+" maxlength="30" required />
						<button class="button">Add Username</button>
						<input type="hidden" value="'.$lgid.'" name="gid" />
						<span class="errZone" style="display:none"></span>
					</div>';
				}
				echo '
				</div>
				</div>
			</li>';
			}
			$firstAcc = false;
			echo '
			<li class="accordion-item is-active" data-accordion-item>
				<a href="#" class="accordion-title">'.$user["name"].'</a>
				<div class="accordion-content" data-tab-content>
				<div class="table-scroll">
					<table>
					<thead>
						<tr>
						<td>Name</td>
						<td>Contribution</td>
						<td>Paid?</td>
						<td>Recieved?</td>';
			if ($editBill) echo '
						<td>Remove?</td>';					
			echo '
						</tr>
					</thead>
					<tbody>';
			$lgid = $user["groupID"];
		}
		if ($user["paid"] != 0) $paid = "Yes";
		else $paid = "No";
		$ammount = number_format($user["ammount"],2,'.','');
		echo '
						<tr id="'.$user["userID"].'">
						<td class="name"><a href="profile.php?uid='.$user["userID"].'">'.$user["realname"].'</a></td><td class="contribution">';
		if ($editBill) {
			echo '
						<input type="number" name="ammount" step="0.01" min="0" required value="'.$ammount.'" ';
			if($user["recieved"] != 0) echo 'disabled ';
			echo '/>';
		} else echo '
						<input type="hidden" value="'.$ammount.'" name="ammount"/>£'.$ammount;
		echo '
						</td><td class="paid">'.$paid.'</td>';
		
		if ($editBill) {
			echo '
						<td class="recieved"><input type="checkbox" name="recieve" ';
			if ($user["recieved"] != 0) echo 'checked disabled';
			if ($user["paid"] == 0) echo 'disabled ';
			echo '/></td>
						<td class="remove">';
			if ($user["userID"] != $_SESSION["uid"]) echo'
							<button class="button alert" name="remove">X</button>';
			echo'
						</td>';
		} else {
			echo '
						<td class="recieved">';
			if ($user["recieved"] != 0) echo 'Yes'; else echo 'No';
			echo '</td>';
		}
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
					<button class="saveConChanges button">Save Contribution Changes</button><br />
					<span class="saveConChangeMsg"></span><br />';
		if ($lgid == 0) echo '
					Add New Contributors:
					<div class="addcGen">';
		else echo '
					Add From Group:
					<div class="addG">';
					
		echo '
						<input type="text" name="username" pattern="[a-zA-Z0-9]+" maxlength="30" required />
						<button type="submit" class="button">Add Username</button>
						<input type="hidden" value="'.$lgid.'" name="gid" />
						<span class="errZone" style="display:none"></span>
					</div>';
	}
	echo '
				</div>
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

function completePost(bid, compState) {
	$.post("updateComplete.php", {bid: bid, compState: compState}, function() {
		console.log("done");
	});
}
$(function() {
	var uid = $("#seshUser").attr("value");
	var bid = $("#curBill").attr("value");
	var oid = $("#ownerID").attr("value");
	var canEdit = $("#editBill").attr("value");
	var billName = $("span#billName").html();
	updateGCT();
	
	//Check if list deleted.
	setInterval(function() {
		$.post("checkBillExists.php", {bid: bid, uid: uid}, function(d) {
			if (d == 0) window.location.replace("bills.php?err=1");
		});
	}, 1000);
	
	//Control Pannel BS
	$("#saveTotal").click(function() {
		var billTotalInput = $("#billTotal");
		if(parseFloat(billTotalInput.val()) != parseFloat(billTotalInput.attr("value"))) {
			var billTotal = parseFloat(billTotalInput.val());
			console.log(billTotal);
			$.post("updateTotal.php", {bid: bid, total: billTotal}, function() {
				console.log("done");
			});
			$("#saveTotalErr").html("Total Updated");
		} else {
			$("#saveTotalErr").html("Total Unchanged");
		}
		$("#saveTotalErr").show("slow");
		setTimeout(function() { $("#saveTotalErr").hide("slow")}, 2000);
	});

	$("#saveSelfPayChange").click(function() {
		$("#saveSelfPayChange").prop("disabled", true);
		var upMsg = $("#updatePayMsg");
		if ($("#selfPaid").prop("defaultChecked") != $("#selfPaid").prop("checked")) {
			var paid = $("#selfPaid").prop("checked");
			$("tr#"+uid+" td.paid").html((paid) ? "Yes" : "No");
			if (oid == uid) {
				$("#selfPaid").prop("disabled", true);
				$("tr#"+uid+" td input[name='recieve']").prop("checked", true);
				$("tr#"+uid+" td input[name='recieve']").prop("disabled", true);
				$("tr#"+uid+" td input[name='ammount']").prop("disabled", true);
			}
			if (paid) paid = 1; else paid = 0;
			$.post("updateSelfPaid.php", {paid: paid, uid: uid, bid: bid}, function(d) {
				var data = JSON.parse(d);
				console.log(d);
				console.log(data.upRec);
				if (data.upRec != 0) upMsg.html("Paid and Recieved (It's your own money after all)");
				else if (paid == 1) upMsg.html("Paid, notification sent to owner.");
					else upMsg.html("Un-Paid, notification sent to owner.");
			});
			$("#selfPaid").prop("defaultChecked", paid);
		} else {
			upMsg.html("Paid state hasn't changed.");
		}
		upMsg.show("slow");
		setTimeout(function() { upMsg.hide("slow");}, 2000);
		setTimeout(function() {$("#saveSelfPayChange").prop("disabled", false)}, 2600);
	});
	
	$("#selfRemove").click(function() {
		$.post("leaveBill.php", {bid: bid, uid: uid, oid: oid}, function(d) {
			console.log(d);
		});
		window.location.replace("bills.php?err=2");
	});
	
	$("#dbill").click(function() {
		$(".dbbs").toggle("fast");
	});
	$("#dbno").click(function() {
		$(".dbbs").hide("fast");
	});
	$("#dbyes").click(function() {
		$(this).prop("disabled", true);
		$.post("deleteBill.php", {bid: bid, billName: billName}, function(data) {
			console.log(data);
			window.location.replace("bills.php?err=1");
		});
	});
	
	$("#completeYes").click(function() {
		if ($("#completeState").attr("value") != 1) {
			completePost(bid, 1);
			$("#completeState").removeClass("alert");
			$("#completeState").addClass("success");
			$("#completeState").attr("value", 1);
			$("#completeState").html("Complete!");
		}
	});
	$("#completeNo").click(function() {
		if ($("#completeState").attr("value") != 0) {
			completePost(bid, 0);
			$("#completeState").removeClass("success");
			$("#completeState").addClass("alert");
			$("#completeState").attr("value", 0);
			$("#completeState").html("Incomplete");
		}
	});
	
	$("#addWholeGroup").click(function() {
		var gid = $("#groupSelect").val();
		var msg = $("#addGroupMsg");
		if (gid != 0) {
			$.post("addGroupToBill.php", {gid: gid, bid: bid}, function(d) {
				console.log(d);
			});
			msg.html("Invited group members not already contributing.");
		} else {
			msg.html("Select a group first.");
		}
		msg.show("slow");
		setTimeout(function(){msg.hide("slow");}, 2000);
	});
	
	//Group Collate BS
	
	$("#groupCollate").on("change", "ul li div table tbody tr td input[name='ammount']", function() {
		updateGCT();
	});
	
	$("#groupCollate").on("click", "ul li .saveConChanges", function() {
		var changes = []
		var liCont = $(this).parent();
		var msg = liCont.find(".saveConChangeMsg");
		liCont.find("input[name='ammount']").each(function () {
			if (parseFloat($(this).val()) != parseFloat($(this).attr("value"))) {
				$(this).parent().parent().find("td.paid").html("No");
				changes.push({val: $(this).val(), id: $(this).parent().parent().attr("id"), bill: bid});
			}
		});
		console.log("ready");
		liCont.find("input[name='ammount']").attr("value", liCont.find("input[name='ammount']").val());
		changes.forEach(function(item) {
			console.log("start");
			if (item) {
				console.log("val: " + item.val + " id: " + item.id);
				if (uid == item.id) {
					$("#selfCon").html(parseFloat(item.val).toFixed(2));
					console.log("self found");
				}
				$.post("updateContribution.php",
				{uid:item.id, ammount:item.val, bid: item.bill},
				function (data, status) {
					console.log(data);
				}).fail(function() { alert("Fail")});
			}
		});		
		if (changes)
			msg.html("Changes Saved");
		else
			msg.html("No Changes Made");
			msg.show("slow");
			setTimeout(function() { $(".saveConChangeMsg").hide("slow")}, 2000);
	});
	$(".addcGen button").click(function() {
		var user = $(this).parent().find("input[type='text']").val();
		if (user) {
			var errZone = $(this).parent().find(".errZone");
			var groupTable = $(this).parent().parent().find("table tbody");
			$.post("addGeneralContributor.php", {name: user, bid: bid, gid: 0}, function(d) {
				var data = JSON.parse(d);
				if (data.uid == 0) 
					errZone.html("User not found");
				else 
					if (data.invited != 0)
						errZone.html("User already invited");
					else
						if (data.already != 0) 
							errZone.html("User already contributing");
						else 
							errZone.html("User Found, Notifications Sent");
				
				errZone.show("slow");
				setTimeout(function() { errZone.hide("slow")}, 5000);
			}).fail(function() { alert("Fail")});
		}
	});
	
	$(".addG button").click(function() {
		var user = $(this).parent().find("input[type='text']").val();
		if (user) {
			var errZone = $(this).parent().find(".errZone");
			var groupTable = $(this).parent().parent().find("table tbody");
			var gid = $(this).parent().find("input[name='gid']").val();
			$.post("addGeneralContributor.php", {name: user, bid: bid, gid: gid}, function(d) {
				var data = JSON.parse(d);
				if (data.uid == 0) 
					errZone.html("User not found");
				else 
					if (data.invited != 0)
						errZone.html("User already invited");
					else
						if (data.already != 0) 
							errZone.html("User already contributing");
						else 
							if (data.groupFind == 0)
								errZone.html("User not part of this group.");
							else
								errZone.html("User Found, Notifications Sent");

				errZone.show("slow");
				setTimeout(function() { errZone.hide("slow")}, 2000);
			}).fail(function() { alert("Fail")});
		}
	});
	
	$("#groupCollate ul li div table tbody tr td button[name='remove']").click(function() {
		var tableRow = $(this).parent().parent();
		var targID = tableRow.attr("id");
		//Remove the user.
		
		$.post("removeUserFromBill.php", {bid: bid, uid: targID}, function(d) {
			console.log(d);
		});
		
		tableRow.hide(1000);
		setTimeout(function() {
			tableRow.remove();
			updateGCT();
		}, 1000);
	});
	
	$("#groupCollate ul li div table tbody tr td input[name='recieve']").change(function() {
		$(this).prop("disabled", true);
		var tableRow = $(this).parent().parent();
		var targID = tableRow.attr("id");
		//Mark as recieved and lock
		tableRow.find("input[name='ammount']").prop("disabled", true);
		$.post("recievedContribution.php", {bid: bid, uid: targID}, function(d) {
			console.log(d);
		});

	});
	
});
		
</script>
<?php require_once "footer.php"?>