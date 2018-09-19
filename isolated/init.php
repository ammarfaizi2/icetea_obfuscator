<?php

define("STORAGE_PATH", realpath(__DIR__."/../public/storage"));

function rstr($n, $noExtendedAscii = false)
{
	$r = "";
	$a = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM_";
	if (! $noExtendedAscii) {
		for ($i=0; $i < 5; $i++) { 
			for ($i=135; $i < 255; $i++) { 
				$a .= chr($i);
			}
		}
	}
	$c = strlen($a) - 1;
	$r.= $a[rand(0, $c)];
	if ($n === 1) {
		return $r;
	}
	$a.= "1234567890";
	$c = strlen($a) - 1;
	$n--;
	for ($i=0; $i < $n; $i++) { 
		$r.= $a[rand(0, $c)];
	}
	return $r;
}