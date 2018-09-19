<?php

require __DIR__."/../isolated/init.php";
use IceTeaObfuscator\Obfuscator;

if (isset($_POST["submit"], $_POST["shebang"], $_POST["obf_key"], $_POST["_token"], $_FILES["php_file"])) {
	
	if (! preg_match("/.*\.php/Usi", $_FILES["php_file"]["name"])) {
		header("Location: index.php?err=invalid_extension");
		exit(1);
	}

	if ($_POST["obf_key"] === "") {
		header("Location: index.php?err=empty_obf_key");
		exit(1);
	}

	header("Content-Type: text/plain");
	print "Checking file hash...\n";
	$hash = sha1_file($_FILES["php_file"]["tmp_name"]);
	$newFilename = STORAGE_PATH."/tmp/".$hash.".phpx";
	$outFilename = STORAGE_PATH."/obfuscated/".$hash.".phpx";

	print "Moving \"".$_FILES["php_file"]["name"]."\" to tmp dir...\n";
	flush();

	if (move_uploaded_file($_FILES["php_file"]["tmp_name"], $newFilename)) {
		require __DIR__."/../src/IceTeaObfuscator/Obfuscator.php";
		define("STDOUT", fopen("/tmp/obfuscate_log", "w"));
		define("STDERR", fopen("/tmp/obfuscate_log", "w"));
		print "Obfuscating code...\n";
		flush();
		$st = new Obfuscator($newFilename, $outFilename, $_POST["obf_key"]);
		if (isset($_POST["shebang"])) {
			$st->shebang = $_POST["shebang"];
		}
		$st->obfuscate();
		unset($st);
		print "Obfuscate completed!\n";
		print "Moving file to obfuscated storage dir...\n";
		print "Output file: https://".$_SERVER["HTTP_HOST"]."/storage/obfuscated/".$hash.".phpx\n";
		flush();
	} else {
		print "An error occured when moving file to tmp_dir!\n";
		exit(1);
	}
} else {
	header("Location: index.php?ref=error");
}