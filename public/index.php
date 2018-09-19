<?php

if (isset($_GET["err"])) {
	switch ($_GET["err"]) {
		case "invalid_extension":
			$alert = "You need to provide a file with (.php) extension!";
			break;
		default:
			$alert = "Unknown error!";
			break;
	}?><!DOCTYPE html>
<html>
<head>
	<title>Error</title>
	<script type="text/javascript">
		alert("<?php print $alert; ?>");
		window.location = "?ref=err";
	</script>
</head>
<body>
</body>
</html><?php
}

require __DIR__."/../isolated/init.php";
session_start();
$csrf = rstr(32);
$_SESSION["csrf"] = $csrf;

?><!DOCTYPE html>
<html>
<head>
	<title>IceTea PHP Obfuscator</title>
	<link rel="stylesheet" type="text/css" href="css/index.css"/>
</head>
<body>
	<center>
		<div>
			<h1>IceTea PHP Obfuscator</h1>
			<div class="mcage">
				<form method="post" action="action.php" enctype="multipart/form-data">
					<h2>Upload your PHP file!</h2>
					<table>
						<tr><td>File</td><td>:</td><td><input type="file" name="php_file" required/></td><td></td></tr>
						<tr><td>Key to encrypt</td><td>:</td><td><input type="text" name="obf_key" required/></td></tr>
						<tr><td></td></tr>
						<tr><td>PHP Shebang</td><td>:</td><td>
							<select name="shebang">
								<option value="">None</option>
								<option value="#!/usr/bin/php">#!/usr/bin/php</option>
								<option value="#!/usr/bin/php7.2">#!/usr/bin/php7.2</option>
								<option value="#!/usr/bin/php7.1">#!/usr/bin/php7.1</option>
								<option value="#!/usr/bin/php7.0">#!/usr/bin/php7.0</option>
								<option value="#!/usr/bin/php5.6">#!/usr/bin/php5.6</option>
								<option value="#!/usr/bin/env php">#!/usr/bin/env php</option>
								<option value="#!/usr/bin/env php7.2">#!/usr/bin/env php7.2</option>
								<option value="#!/usr/bin/env php7.1">#!/usr/bin/env php7.1</option>
								<option value="#!/usr/bin/env php7.0">#!/usr/bin/env php7.0</option>
								<option value="#!/usr/bin/env php5.6">#!/usr/bin/env php5.6</option>
							</select>
						</td></tr>
						<tr><td colspan="3" align="center"><input type="submit" name="submit" value="Obfucate" /></td></tr>
					</table>
					<input type="hidden" name="_token" value="<?php print htmlspecialchars(base64_encode($csrf), ENT_QUOTES, "UTF-8"); ?>"/>
				</form>
			</div>
		</div>
	</center>
</body>
</html>