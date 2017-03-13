<?php session_start();
$uid = $_SESSION["uid"];
if (empty($uid)) {
	header("Location:login.php?err=2");
	exit();
}
?>
<?php include "header.php"; ?>
<label>Show Read? <input type="checkbox" id="showChecked" checked /></label>
<table id="notifications">
	<thead>
		<tr>
			<td>Notification</td>
			<td>Relevant Links</td>
			<td>Actions</td>
			<td>Recieved</td>
		</tr>
	</thead>
	<tbody>
<?php
	$db = new Database();
	$stmt = $db->prepare("SELECT * FROM notifications
		INNER JOIN notiTypes ON notifications.typeID = notiTypes.typeID
		LEFT JOIN notiBill ON notifications.notiID = notiBill.notiID
		LEFT JOIN notiGroup ON notifications.notiID = notiGroup.notiID
		LEFT JOIN notiUser ON notifications.notiID = notiUser.notiID
		WHERE notifications.userID = :uid ORDER BY notifications.addTS DESC");
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	while ($noti = $result->fetchArray()) {
		$nid = $noti[0];	//Something buggy with the return values.
		$bid = $noti["billID"];
		$gid = $noti["groupID"];
		$suid = $noti["secondUserID"];
		$nType = $noti["typeID"];
		if ($noti["checked"] != 0) $dis = ' disabled '; else $dis = ' ';
		echo '
		<tr id="'.$nid.'"';
		if ($noti["checked"] != 0) echo ' style="background-color: #cacaca"';
		echo'>
			<input type="hidden" name="comp" value="';
		if ($noti["checked"] != 0) echo 'true'; else echo 'false';
		echo'">
			<input type="hidden" name="bid" value="'.$bid.'">
			<input type="hidden" name="gid" value="'.$gid.'">
			<input type="hidden" name="suid" value="'.$suid.'">
			<input type="hidden" name="nType" value="'.$nType.'">
			<td>'.$noti["message"].'</td>
			<td>Bill: ';
		if (!empty($bid)) {
			$stmt = $db->prepare("SELECT name FROM bills WHERE billID = :bid");
			$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
			$tempResult = $stmt->execute();
			$temp = $tempResult->fetchArray();
			$billName = $temp["name"];
			echo '<a href="viewBill.php?bid='.$bid.'">'.$billName.'</a>';
		} else {
			echo 'N/A';
		}
		echo'<br />
			Group: ';
		
		if(!empty($gid)) {
			$stmt = $db->prepare("SELECT name FROM groups WHERE groupID = :gid");
			$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
			$tempResult = $stmt->execute();
			$temp = $tempResult->fetchArray();
			$groupName = $temp["name"];
			echo '<a href="viewGroup.php?gid='.$gid.'">'.$groupName.'</a>';
		} else {
			echo 'N/A';
		}
		
		echo'<br />
			User: ';
		
		if(!empty($suid)) {
			$stmt = $db->prepare("SELECT realname FROM users WHERE userID = :uid");
			$stmt->bindValue(":uid", $suid, SQLITE3_INTEGER);
			$tempResult = $stmt->execute();
			$temp = $tempResult->fetchArray();
			$secName = $temp["realname"];
			echo '<a href="profile.php?gid='.$uid.'">'.$secName.'</a>';
		} else {
			echo 'N/A';
		}
		echo '</td>
			<td value="'.$nType.'">';
		switch ($nType) {
			case 2:
			case 7:
			case 15:
			case 17:
				echo 'Accept?<br /><button class="button success notiReply" value="1"'.$dis.'>Yes</button><button class="button alert notiReply" value="0"'.$dis.'>No</button>';
				break;
			default:
				echo '<button class="button markChecked"'.$dis.'>Mark Read</button>';
		}
		echo'</td>
			<td>'.$noti["addTS"].'</td></tr>';
	}
?>
	</tbody>
</table>
<script>
$(function() {
	$("#showChecked").click(function() {
		var ch = $(this).prop("checked");
		if (ch) $("input[name='comp'][value='true']").parent().show("fast");
		else $("input[name='comp'][value='true']").parent().hide("fast");
	});
	
	$(".markChecked").click(function() {
		notiRow = $(this).parent().parent();
		notiRow.find(".markChecked").prop("disabled", true);
		notiComp = notiRow.find("input[name='comp']");
		notiID = notiRow.attr("id");
		if (notiComp.attr("value") == "false") {
			notiRow.css("background-color", "#cacaca");
			notiComp.attr("value", "true");
			$.post("handleNotification.php", {nid: notiID}, function() {
				console.log("Done");
			});
		}
	});
	
	$(".notiReply").click(function() {
		nReply = $(this).attr("value");
		notiRow = $(this).parent().parent();
		notiRow.find(".notiReply").prop("disabled", true);
		notiComp = notiRow.find("input[name='comp']");
		notiID = notiRow.attr("id");
		if (notiComp.attr("value") == "false") {
			bid = notiRow.find("input[name='bid']").attr("value");
			gid = notiRow.find("input[name='bid']").attr("value");
			suid = notiRow.find("input[name='bid']").attr("value");
			nType = notiRow.find("input[name='nType']").attr("value");
			console.log(nType);
			notiRow.css("background-color", "#cacaca");
			notiComp.attr("value", "true");
			$.post("handleNotification.php", 
				{nid: notiID, nType: nType, nReply: nReply, bid: bid, gid: gid, suid: suid},
				function() {
					console.log("Done");
				});
		}
	});
});
</script>
<?php include "footer.php"; ?>