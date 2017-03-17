<?php require "header.php";?>
<?php 
	if (!empty($_GET["uid"])) $uid = $_GET["uid"];
	if (empty($uid) || !is_numeric($uid)) {
		if ($isLog) $uid = $_SESSION["uid"];
		else $uid = 0;
	}
?>
<div class="callout">
<?php 
	if ($uid != 0) {
		$db = new Database();
		$stmt = $db->prepare("SELECT * FROM users
			WHERE users.userID = :uid");
		$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
		$result = $stmt->execute();
		$userData = $result->fetchArray();
		if ($userData) {
			echo '<ul class="accordion" data-accordion data-multi-expand="true" data-allow-all-closed="true">
				<li class="accordion-item is-active" data-accordion-item>
					<a href="#" class="accordion-title">'.$userData["realname"].'</a>
					<div class="accordion-content" data-tab-content>
						<a id="qrURL"><div id="qr-code"></div></a>
						<script>
							$("#qrURL").attr("href", "http://cs139.dcs.warwick.ac.uk/~u1600262/cs139/cw/profile.php?uid='.$uid.'");
							new QRCode(document.getElementById("qr-code"),"http://cs139.dcs.warwick.ac.uk/~u1600262/cs139/cw/profile.php?uid='.$uid.'");
						</script>
						Name: <a href="profile.php?uid='.$uid.'">'.$userData["realname"].'</a><br />
						Username: '.$userData["username"].'<br />
					</div>
				</li>
				<li class="accordion-item is-active" data-accordion-item>
					<a href="#" class="accordion-title">Groups</a>
					<div class="accordion-content" data-tab-content>';
			
			$stmt = $db->prepare("SELECT * FROM groups
				INNER JOIN groupUserRel ON groupUserRel.groupID = groups.groupID
				WHERE groupUserRel.userID = :uid");
			$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
			$result = $stmt->execute();
			$count = 0;
			while ($group = $result->fetchArray()) {
				echo '<a href="viewGroup.php?gid='.$group["groupID"].'">'
					.$group["name"].'</a><br />';
					$count++;
			}
			if ($count == 0) echo '<div class="callout warning">'.$userData["realname"]
				.' is not part of any group :(</div>';
			echo'		</div>
				</li>
			</ul>';
		} else {
			echo '<div class="callout warning">User not found.</div>';
			die();
		}
	} else {
		echo '<div class="classout warning">No user provided.</div>';
		die();
	}
?>
</div>
<?php require "footer.php";?>