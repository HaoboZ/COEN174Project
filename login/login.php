<!--
Login page for admins.

These are set directly, accounts need to be added to the database directly.
-->
<?php
include_once(__DIR__ . "/../database/database_connection.php");
include_once('check.php');
if ($admin) {
	header("location:../index.php");
	exit;
}

$message = '';

if (isset($_POST["login"])) {
	if (empty($_POST["user_email"]) || empty($_POST["user_password"])) {
		$message = "<div class='alert alert-danger'>Both Fields Are Required</div>";
	} else {
		$query = oci_parse($connect, "
			SELECT * FROM user_info
			WHERE user_email = :user_email
	    ");
		oci_bind_by_name($query, ":user_email", $_POST["user_email"]);
		if (!oci_execute($query)) exit;

		$res = oci_fetch_assoc($query);

		if ($res && password_verify($_POST["user_password"], $res["USER_PASSWORD"])) {
			$id = uniqid();
			$query = oci_parse($connect, "
					INSERT INTO login_session
					VALUES (:session_key, :user_email, TO_DATE(:timeout, 'dd-mm-yyyy hh24:mi:ss'))
			    ");
			oci_bind_by_name($query, ":session_key", $id);
			oci_bind_by_name($query, ":user_email", $_POST["user_email"]);
			$timeout = time() + 3600 * 24;
			oci_bind_by_name($query, ":timeout", date('d-m-Y H:i:s', $timeout));
			if (!oci_execute($query)) exit;
			setcookie("login_session", $id, $timeout, '/');
			header("location:../index.php");
			exit;
		} else {
			$message = "<div class='alert alert-danger'>Wrong Email/Password</div>";
		}
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<?php include(__DIR__ . "/../content/title.php"); ?>
</head>
<body>
<br/>
<div class="container">
	<?php include(__DIR__ . "/../content/header.php"); ?>
	<h4>Admin Login</h4>
	<span><?php echo $message; ?></span>
	<form method="post">
		<div class="form-group">
			<label for="user_email">User Email</label>
			<input type="text" name="user_email" id="user_email" class="form-control"/>
		</div>
		<div class="form-group">
			<label for="user_password">Password</label>
			<input type="password" name="user_password" id="user_password" class="form-control"/>
		</div>
		<div class="form-group">
			<input type="submit" name="login" id="login" class="btn" value="Login"/>
		</div>
	</form>
	<br/>
	<p>Admin email - john_smith@gmail.com</p>
	<p>Admin Password - password</p>
</div>
</body>
</html>
