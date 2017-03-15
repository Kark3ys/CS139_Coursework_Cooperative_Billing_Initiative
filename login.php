<?php require "header.php"; ?>
<ul class="tabs" data-deep-link="true" data-tabs id="deeplinked-tabs">
  <li class="tabs-title is-active"><a href="#login" aria-selected="true">Login</a></li>
  <li class="tabs-title"><a href="#register">Register</a></li>
</ul>
<div class="tabs-content" data-tabs-content="deeplinked-tabs">
  <div class="tabs-panel is-active" id="login">
		<div class="row">
			<div class="columns medium-2">
			&nbsp
			</div>
			<div class="columns medium-8">
			<p>Enter your login details to proceed to the site.</p>
				<?php 
				if(!empty($_GET["err"]) && is_numeric($_GET["err"])){
					echo '<div class="callout alert">';
					switch ($_GET["err"]) {
					case 1:
						echo "Email/Password combination invalid.<br />";
					break;
					case 2:
						echo "Must be logged in.<br />";
					break;
					default:
						echo "Generic Error";					
					}
					echo '</div>';
				}
				?>
				<form action="login_process.php" method="POST">
					<label for="email">Email: </label>
					<input type="email" maxlength="50" name="email" required>
					<label for="login">Password: </label>
					<input type="password" pattern="[a-zA-Z0-9]+" maxlength="20" name="pass" required>
					<input type="Submit" name="login" value="Login" class="button">
				</form>
			</div>
			<div class="columns medium-2">
			&nbsp
			</div>
		</div>
  </div>
  <div class="tabs-panel" id="register">
		<div class="row">
			<div class="columns medium-2">
			&nbsp
			</div>
			<div class="columns medium-8">
				<form method="POST" id="regform" action="register_process.php">
					Alphanumeric Username, Name and Password Only<br />
					<?php if(!empty($_GET) && $_GET["err"] == 1) echo "<div class='callout alert'>Username Already Exists</div>";?><br />
					<label for="username">Username: </label>
					<input type="text" name="username" pattern="[a-zA-Z0-9]+" maxlength="30" required />
					<label for="name">Name: </label>
					<input type="text" name="name" pattern="[a-zA-Z0-9]+" maxlength="30" required />
					<?php if(!empty($_GET) && $_GET["err"] == 2) echo "<div class='callout alert'>Email Already Registered</div>";?><br />
					<label for="email">E-Mail Address: </label>
					<input type="email" name="email" maxlength="50" required />
					<label for="emailc">Reconfirm E-Mail Address: </label>
					<input type="email" name="emailc" maxlength="50" required /><br />
					<label for="pass">Password: </label>
					<input type="password" name="pass" pattern="[a-zA-Z0-9]+" maxlength="20" required /><br />
					<label for="passc">Reconfirm Password: </label>
					<input type="password" name="passc" pattern="[a-zA-Z0-9]+" maxlength="20" required /><br />
					<input type="submit" value="Register" class="button"/>
				</form>
			</div>
			<div class="columns medium-2">
			&nbsp
			</div>
		</div>
  </div>
</div>
<script>
$(document).ready(function() {
	$("#regform").submit(function() {
		var retVal = $("#regform input[name='email']").val() === $("#regform input[name='emailc']").val();
		if (retVal) retVal = $("#regform input[name='pass']").val() === $("#regform input[name='passc']").val();
		return retVal;
	})
})
</script>
<?php require "footer.php"; ?>