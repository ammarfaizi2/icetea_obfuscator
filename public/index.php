<!DOCTYPE html>
<html>
<head>
	<title>IceTea PHP Obfuscator</title>
	<style type="text/css">
		* {
			font-family: Arial, Tahoma;
		}
		.cg {
			border: 1px solid #000;
			margin-top: 50px;
			width: 500px;
			padding-bottom: 20px;
		}
		.cg2 {
			margin-top: 100px;
		}
		a {
			color:blue;
			text-decoration: none;
		}
	</style>
</head>
<body>
	<center>
		<h1>Ice Tea PHP Obfuscator</h1>
		<div class="cg">
			<form method="post" action="action.php?r=1" enctype="multipart/form-data">
				<h2>Upload your PHP file!</h2>
				<table>
					<tr><td>File</td><td>:</td><td><input type="file" name="rfile" required/></td></tr>
					<tr><td>Key</td><td>:</td><td><input type="text" name="rkey" required/></td></tr>
					<tr><td>Shebang</td><td>:</td><td>
						<select name="shebang">
							<option value="">None</option>
							<option>#!/usr/bin/php</option>
							<option>#!/usr/bin/php7.3</option>
							<option>#!/usr/bin/php7.2</option>
							<option>#!/usr/bin/php7.1</option>
							<option>#!/usr/bin/php7.0</option>
							<option>#!/usr/bin/env php</option>
							<option>#!/usr/bin/env php7.3</option>
							<option>#!/usr/bin/env php7.2</option>
							<option>#!/usr/bin/env php7.1</option>
							<option>#!/usr/bin/env php7.0</option>
						</select>
					</td></tr>
					<tr><td colspan="3" align="center"><button type="submit">Submit</button></td></tr>
				</table>
			</form>
		</div>
		<div class="cg2">
			<h2>GitHub Repository: <br/><a target="_blank" href="https://github.com/ammarfaizi2/icetea_obfuscator">https://github.com/ammarfaizi2/icetea_obfuscator</a></h2>
		</div>
	</center>
</body>
</html>