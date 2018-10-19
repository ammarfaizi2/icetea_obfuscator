<?php

if (isset(
	$_GET["r"],
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

	$hash = sha1($_FILES["rfile"]["tmp_name"]);
	print "msg: Moving uploaded file to tmp dir...\n";
	print "cmd: mv ".escapeshellarg($_FILES["rfile"]["tmp_name"])." ".escapeshellarg("storage/tmp/{$hash}.tmp")."\n";
	flush();
	move_uploaded_file($_FILES["rfile"]["tmp_name"], __DIR__."/storage/tmp/{$hash}.tmp");

	$argvd = "-o ".escapeshellarg("storage/obfuscated/{$hash}.phx")." ".escapeshellarg("storage/tmp/{$hash}.tmp");
	if (!empty($shebang)) {
		$argvd .= " -s ".escapeshellarg($_POST["shebang"]);
	}
	$argvd .= " -k ".escapeshellarg($_POST["rkey"]);
	print "msg: Obfuscating file...\n";
	print "cmd: php iceobf {$argvd}\n";
	flush();
	print shell_exec("php ".BIN_PATH."/iceobf {$argvd} 2>&1");
	flush();
	print "msg: Finished!\n";
	print "msg: Output file: https://{$_SERVER['HTTP_HOST']}/storage/obfuscated/{$hash}.phx\n";
	flush();
	exit;
}

header("Location: index.php?r=1");