<?php require "header.php";?>
<?php 
	if (!empty($_GET["gid"])) 
		$gid = $_GET["gid"];
	else 
		$gid = 0;
?>
<div class="callout">
<?php 
	if ($gid != 0) {
		$db = new Database();
		$stmt = $db->prepare("SELECT * FROM groups
			INNER JOIN groupUserRel USING(groupID)
			INNER JOIN users USING(userID)
			WHERE groups.groupID = :gid AND owner = 1");
		$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
		$result = $stmt->execute();
		$groupData = $result->fetchArray();
		if ($groupData) {
			$groupOwner = $groupData["userID"] == $_SESSION["uid"];
			echo '<ul class="accordion" data-accordion data-multi-expand="true" data-allow-all-closed="true">
				<li class="accordion-item is-active" data-accordion-item>
					<a href="#" class="accordion-title">'.$groupData["name"].'</a>
					<div class="accordion-content" data-tab-content>
						Name: '.$groupData["name"].'<br />
						Owner: <a href="profile.php?uid='.$groupData["userID"].'">'.$groupData["realname"].'</a><br />
						Created: '.$groupData["createTS"].'
					</div>
				</li>';
			echo '
				<li class="accordion-item is-active" data-accordion-item>
					<a href="#" class="accordion-title">Control Panel</a>
					<div class="accordion-content" data-tab-content>
					<input type="hidden" id="gid" value="'.$gid.'">
					<input type="hidden" id="uid" value="'.$_SESSION["uid"].'">
					<input type="hidden" id="oid" value="'.$groupData["userID"].'">';
			if ($groupOwner) {
				echo '
					<input id="addUserName" type="text" name="username" pattern="[a-zA-Z0-9]+" maxlength="30" required />
					<button id="addUser" class="button">Add User</button>
					<span id="addUserMsg" style="display:none"></span><br />
					Group Join Requests
					<table>
						<thead>
							<tr>
								<td>Name</td>
								<td>Accept Request?</td>
							</tr>
						</thead>
						<tbody>';
				$stmt = $db->prepare("SELECT * FROM notifications
					INNER JOIN notiUser USING(notiID)
					INNER JOIN notiGroup USING(notiID)
					INNER JOIN users ON users.userID = notiUser.secondUserID
					WHERE groupID = :gid AND notifications.userID = :oid
					AND typeID = 17 AND checked != 1");
				$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
				$stmt->bindValue(":oid", $groupData["userID"], SQLITE3_INTEGER);
				$result = $stmt->execute();
				while ($temp = $result->fetchArray()) {
					echo '
							<tr>
							<input type="hidden" class="notiID" value="'.$temp["notiID"].'">
							<input type="hidden" class="userID" value="'.$temp["secondUserID"].'">
								<td class="name">
									<a href="profile.php?uid='.$temp["secondUserID"].'">'.$temp["realname"].'</a>
								</td>
								<td class="actionsOnContact">
									<button class="button success notiReply" value="1">Yes</button>
									<button class="button alert notiReply" value="0">No</button>
								</td>
							</tr>';
				}
					
				
				echo '
						</tbody>
					</table>
					<br />
					<button id="deleteGroup" class="button alert">Delete Group</button>';
			} else {
				$stmt = $db->prepare("SELECT userID from groupUserRel
				WHERE userID = :uid AND groupID = :gid");
				$stmt->bindValue(":uid", $_SESSION["uid"], SQLITE3_INTEGER);
				$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
				$tempRes = $stmt->execute();
				$temp = $tempRes->fetchArray();
				if (!$temp)
					echo '
						<button id="requestJoin" class="button">Request Invitation</button>
						<span id="requestJoinMsg" style="display:none"></span>';
				else
					echo '
						<button id="leaveGroup" class="button alert">Leave Group</button>
						<span id="leaveGroupMsg" style="display:none"></span>';
			}
			echo '
				</div>
			</li>
			<li class="accordion-item is-active" data-accordion-item>
					<a href="#" class="accordion-title">Users</a>
					<div class="accordion-content" data-tab-content>';
			
			$stmt = $db->prepare("SELECT * FROM users
				INNER JOIN groupUserRel ON groupUserRel.userID = users.userID
				WHERE groupUserRel.groupID = :gid");
			$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
			$result = $stmt->execute();
			$count = 0;
			if ($groupOwner) echo '
						<table id="users">
							<thead>
								<tr>
									<td>Name</td>
									<td>Remove?</td>
									<td>Make Owner?</td>
								</tr>
							</thead>
							<tbody>';
							
			while ($user = $result->fetchArray()) {
				$count++;
				if ($groupOwner) {
					echo '	
								<tr id="'.$user["userID"].'">
									<td class="name">
										<a href="profile.php?uid='.$user["userID"].'">'.$user["realname"].'</a>
									</td>
									<td class="remove">';
					if($user["userID"] != $groupData["userID"]) echo'
										<button class="button alert removeUser" name="remove">X</button>';
					echo '
									</td>
									<td class="transfer">';
					if($user["userID"] != $groupData["userID"]) echo'
										<button class="button warning transferUser" name="transfer">O</button>';
					echo '
									</td>
								</tr>';
					
				} else echo (($user["userID"] == $groupData["userID"]) ? '* ': '').'	<a href="profile.php?uid='.$user["userID"].'">'
					.$user["realname"].'</a><br />
					';
			}
			if ($count == 0) echo '<div class="callout warning">'.$groupData["name"]
				.' has no members!!!!(</div>';
			if ($groupOwner) 
				echo '
							</tbody>
						</table>';
						
			echo'
					</div>
				</li>
			</ul>';
		} else {
			echo '<div class="callout warning">Group not found.</div>';
			die();
		}
	} else {
		echo '<div class="classout warning">No group provided.</div>';
		die();
	}
?>
</div>
<script>
$(function() {
	var gid = $("#gid").attr("value");
	var uid = $("#uid").attr("value");
	var oid = $("#oid").attr("value");
	
	$("#addUser").click(function() {
		$("#addUser").prop("disabled", true);
		var user = $("#addUserName").val();
		if(user) {
			var msg = $("#addUserMsg");
			$.post("addUserToGroup.php", {name: user, gid: gid, oid: oid}, function(d) {
				console.log(d);
				switch	(parseInt(d)) {
					case 0: msg.html("User not found.");
					break;
					case 1: msg.html("User already invited.");
					break;
					case 2: msg.html("User requested to join already, request accepted.");
					break;
					case 3: msg.html("Invite sent.");
					break;
					case 4: msg.html("User already part of group.");
					break;
					default: msg.html("Something Happened!");
				}
			msg.show("slow");
			setTimeout(function(){msg.hide("slow")},2000);
			setTimeout(function(){$("#addUser").prop("disabled", false);},2600);
			});
		}
	});
	
	$("#deleteGroup").click(function() {
		$("#deleteGroup").prop("disabled", true);
		$.post("deleteGroup.php", {gid: gid}, function(d) {
			console.log(d);
			window.location.replace("groups.php");
		});
	});
	
	$("#requestJoin").click(function() {
		var msg = $("#requestJoinMsg");
		$("#requestJoin").prop("disabled", true);
		$.post("requestJoinGroup.php", {gid: gid, uid: uid, oid: oid}, function(d) {
			console.log(d);
			switch (parseInt(d)) {
				case 0:
					msg.html("Already part of group!");
				break;
				case 1:
					msg.html("Request already sent.");
				break;
				case 2:
					msg.html("Request sent!");
				break;
				default:
					msg.html("Something Happened!");
			}
			msg.show("slow");
			setTimeout(function(){msg.hide("slow")}, 2000);
			setTimeout(function(){$("#requestJoin").prop("disabled", false)}, 2600);
		});
	});
	
	$("#leaveGroup").click(function() {
		$("#leaveGroup").prop("disabled", true);
		$.post("leaveGroup.php", {gid: gid, uid: uid, oid: oid}, function(d) {
			console.log(d);
			window.location.replace("viewGroup.php?gid="+gid);
		});
	});
	
	
	$(".removeUser").click(function() {
		var row = $(this).parent().parent();
		row.find("button").prop("disabled", true);
		var targID = row.attr("id");
		
		$.post("removeUserFromGroup.php", {gid: gid, uid: targID}, function(d) {
			console.log(d);
		});
		
		row.hide(1000);
		setTimeout(function() {row.remove();}, 1000);
	});
	
	$(".transferUser").click(function() {
		var row = $(this).parent().parent();
		row.find("button").prop("disabled", true);
		var targID = row.attr("id");
		var name = row.find("td.name a").html();
		
		$.post("promoteUser.php", {gid: gid, uid: targID, oid: oid, name: name}, function(d) {
			console.log(d);
			window.location.reload();
		});
	});
	
	$(".notiReply").click(function() {
		nReply = $(this).attr("value");
		notiRow = $(this).parent().parent();
		notiRow.find(".notiReply").prop("disabled", true);
		notiID = notiRow.find("input.notiID").attr("value");
		suid = notiRow.find("input.userID").attr("value");
		nType = 17;
		$.post("handleNotification.php", 
			{nid: notiID, nType: nType, nReply: nReply, bid: 0, gid: gid, suid: suid},
			function(d) {
				console.log(d);
			});
			
		notiRow.hide(1000);
		setTimeout(function(){notiRow.remove()}, 1000);
	});
});
</script>
<?php require "footer.php";?>