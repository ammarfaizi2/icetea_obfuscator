<?php

if (isset(
	$_POST["rkey"],
	$_POST["shebang"],
	$_FILES["rfile"]["tmp_name"]
)) {
	if (!is_string($_POST["rkey"])) {
		exit("Error");
	}

	if (!is_string($_POST["shebang"])) {
		exit("Error");
	}

	define("TMP_DIR", __DIR__."/storage/tmp");
	define("BIN_PATH", realpath(__DIR__."/../bin"));

	header("Content-Type: text/plain");
	
	require __DIR__."/../vendor/autoload.php";

	print "Moving uploaded file to tmp dir...\n";
	print "cmd: mv ".$_FILES["rfile"]["tmp_name"]." storage/tmp/{$sha1}.tmp";
	flush();
	$hash = sha1($_FILES["rfile"]["tmp_name"]);
	move_uploaded_file($_FILES["rfile"]["tmp_name"], __DIR__."/storage/tmp/{$sha1}.tmp");

	$argvd = "-o storage/obfuscated/{$hash}.phx storage/tmp/{$hash}.tmp ";
	if (!empty($shebang)) {
		$argvd .= "-s ".escapeshellarg($_POST["shebang"])." -k ".escapeshellarg($_POST["rkey"]);
	}
	print "Obfuscating file...\n";
	print "cmd: php iceobf {$argvd}\n";
	flush();
	print shell_exec(BIN_PATH." php iceobf {$argvd} 2>&1");
	flush();
	print "Finished!\n";
	print "Output file: http://{$_SERVER['HTTP_HOST']}/storage/obfuscated/{$hash}.phx\n";
	flush();
}
