<?php

namespace IceTeaObfuscator;

defined("TMP_DIR") or exit("TMP_DIR is not defined!\n");

use Exception;
use PhpParser\Error;
use PhpParser\NodeDumper;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\EncapsedStringPart;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \IceTeaObfucator
 */
final class Obfuscator
{
	/**
	 * @var string
	 */
	private $file;

	/**
	 * @var string
	 */
	private $shebang = null;

	/**
	 * @var string
	 */
	private $obfuscated;

	/**
	 * @var string
	 */
	private $outfile;

	/**
	 * @var string
	 */
	private $parsed;

	/**
	 * @var array
	 */
	private $memVar = [];

	/**
	 * @var string
	 */
	private $parsedHash;

	/**
	 * @var string
	 */
	private $key = null;

	/**
	 * @var array
	 */
	private $func = [];

	/**
	 * @var string
	 */
	private $strFunc;

	/**
	 * @var string
	 */
	private $decryptorName;

	/**
	 * @var string
	 */
	private $keyForKey;

	/**
	 * @var string
	 */
	private $matcherBool = true;

	/**
	 * @param string $file
	 * @param string $outfile
	 */
	public function __construct(string $file, string $outfile)
	{
		$this->file = $file;
		$this->outfile = $outfile;

		if (!file_exists($this->file)) {
			throw new Exception("File {$this->file} does not exist");
		}
	}

	public function setKey(string $key): void
	{
		$this->key = $key;
	}

	/**
	 * @param string $shebang
	 * @return void
	 */
	public function setSheBang(string $shebang): void
	{
		$this->shebang = $shebang;
	}

	/**
	 * @param string
	 */
	public function getParsedBody(): string
	{
		return $this->parsed;
	}

	/**
	 * @param string $str
	 * @return string
	 */
	private function chrToHex($str)
	{
		return "\\x".dechex(ord($str));
	}

	/**
	 * @param string $str
	 * @return string
	 */
	private function chrToOct($str)
	{
		return "\\".decoct(ord($str));
	}

	/**
	 * @param string $str
	 * @return string
	 */
	private function convert($str)
	{
		$r = "";
		foreach (str_split($str) as $char) {
			$r .= rand(2, 3) % 2 ? $this->chrToOct($char) : $this->chrToHex($char);
		}
		return $r;
	}

	/**
	 * @param int $n
	 * @param bool $noExtended
	 * @return string
	 */
	private function gen(int $n, bool $noExtendedAscii = false): string
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

	/**
	 * @param int $n
	 * @param bool $noExtended
	 * @return string
	 */
	private function gen2(int $n): string
	{
		$r = "";
		$a = range(chr(0), chr(31));
		$c = count($a) - 1;
		$d = range(chr(32), chr(255));
		$e = count($d) - 1;
		for ($i=0; $i < $n; $i++) {
			rand(0, 1) ?
				$r.= $a[rand(0, $c)] :
					$r.= $d[rand(0, $e)];
		}
		return $r;
	}

	/**
	 * @param mixed $v
	 * @return void
	 */
	private function varChanger($v): void
	{
		foreach($v as $k => $v) {
			if ($v instanceof Variable && $v->name !== "this") {
				if (isset($this->memVar[$v->name])) {
					$v->name = $this->memVar[$v->name];
				} else {
					$this->memVar[$v->name] = $this->gen(8);
					$v->name = $this->memVar[$v->name];
				}
			} else {
				$this->varChanger($v);
			}
		}
	}

	/**
	 * @return void
	 */
	public function parse(): void
	{
		$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

		try {
		    $ast = $parser->parse(file_get_contents($this->file));
		} catch (Error $error) {
		    throw new Expcetion("PHP file error: Parse error: {$error->getMessage()}");
		}

		foreach ($ast as $k => &$v) {
			@$this->varChanger($v);
		}
		$this->prepareFunctions();
		$prettyPrinter = new PrettyPrinter\Standard;
		$this->parsed = 
			"<?php ".
			"/*\0\1\3\3\3\3\0\7\1\5\6\3\3\3\3".
			// "/*".
			"{$this->gen(32, 0)}".
			"gnu.version_r.rela.dyn.rela.plt.init.plt.got.text.fini.rodata".
			".eh_frame_hdr.eh_frame.gcc_except_table.init_array.fini_array".
			".data.rel.ro.dynamic.data.bss.comment.debug_aranges.debug_info".
			".debug_abbrev.de\0*/eval(\"/*\0*/{$this->convert($prettyPrinter->prettyPrint($ast))}\");".
			// "/*@@@@@@.debug�*/";
			"/*\1\1\1\0\0\0\5\2*/";
		$this->parsedHash = sha1($this->parsed);
		file_put_contents(TMP_DIR."/{$this->parsedHash}.phptmp", $this->parsed);
		$this->parsed = trim(shell_exec("php -w ".TMP_DIR."/{$this->parsedHash}.phptmp"));
		@unlink(TMP_DIR."/{$this->parsedHash}.phptmp");
	}

	/**
	 * @return void
	 */
	private function prepareFunctions(): void
	{
		$this->func = [			
			"gzinflate" => "\${\"{$this->escape($this->gen2(4096 * 3))}\"}",
			"explode" => "\${\"{$this->escape($this->gen2(4096))}\"}",
			"file_get_contents" => "\${\"{$this->escape($this->gen2(4096))}\"}",
			"preg_match" => "\${\"{$this->escape($this->gen2(4096))}\"}",
			"sha1" => "\${\"{$this->escape($this->gen2(4096))}\"}",
			"extension_loaded" => "\${\"{$this->escape($this->gen2(4096))}\"}",
			"sleep" => "\${\"{$this->escape($this->gen2(4096))}\"}",
			"pcntl_fork" => "\${\"{$this->escape($this->gen2(4096))}\"}",
			"pcntl_waitpid" => "\${\"{$this->escape($this->gen2(4096))}\"}",
			"getmypid" => "\${\"{$this->escape($this->gen2(4096))}\"}",
		];


		foreach($this->func as $k => $func) {
			$nodefunct = str_replace("*", "std", $this->gen2(4096 * 4));
			$this->strFunc .= "{$func}=\"{$this->convert($k)}\"/*{$nodefunct}*/XOR";
		}

		$decryptor = gzdeflate(
			"/*".$this->escape(str_replace("*", "std", $this->gen2(1024)))."*/".
			$this->generateDecryptor(
				$this->decryptorName = $this->gen(512)
			).
			$this->prepareHashMatcher()
		);

		$vc = [
			"ppid" => "\${\"{$this->escape($this->gen2(32))}\"}",
			"pid" => "\${\"{$this->escape($this->gen2(32))}\"}",
			"status" => "\${\"{$this->escape($this->gen2(32))}\"}",
		];

		$this->strFunc .= " /*\0*/eval({$this->func['gzinflate']}(\"{$this->escape($decryptor)}\"));";
	}

	/**
	 * @return string
	 */
	private function prepareHashMatcher(): string
	{
		return "/*\0*/(\$me={$this->func['file_get_contents']}(explode(\"(\",__FILE__,0x2)[0])AND {$this->func['preg_match']}(\"\\x2f\\50\\77\\72\\134\\x24\\x5c\\44\\134\\44\\134\\57\\x7e\\x7e\\144\\51\\50\\x2e\\52\\51\\50\\77\\x3a\\144\\176\\x7e\\134\\57\\134\\44\\x5c\\x24\\134\\44\\x29\\x2f\\x55\\163\\151\",\$me,\$d)AND\$me=explode(\"\\137\\x5f\\150\\141\\154\\x74\\137\\x63\\x6f\\155\\160\\151\\154\\145\\x72\\x28\\51\\73\",\$me,2)AND\$me=\"{\$me[0]}\\137\\x5f\\150\\141\\154\\x74\\137\\x63\\x6f\\155\\160\\151\\154\\145\\x72\\x28\\51\\73\"AND\$me=sha1(\$me)AND!(\$me!==\$d[1]))OR(sleep(3)XOR exit(\"\\123\\x65\\147\\155\\x65\\156\\x74\\141\\x74\\151\\157\\x6e\\x20\\x66\\141\\x75\\x6c\\164\\12\"));";
	}

	/**
	 * @return bool
	 */
	private function run(): bool
	{
		$pnc = gzdeflate($this->encrypt("?>".$this->parsed, $this->key));
		$this->keyForKey = $this->gen(32);
		$encryptedKey = gzdeflate($this->encrypt($this->key, $this->keyForKey));
		$enc = 
			"{$this->decryptorName}(".
			"{$this->func['gzinflate']}(\"".
			"{$this->escape($pnc)}\"),{$this->decryptorName}(".
			"{$this->func['gzinflate']}(".
			"\"{$encryptedKey}\"),\$keyforkey))";

		$enc = 
			"/*std::hd;".str_replace("\0", "@", $this->gen2(1024))."\0*/({$this->func['extension_loaded']}(\"".$this->convert("evalhook")."\"))AND ({$this->func['sleep']}(rand(0x3, 0x2))".
			"XOR print(\"{$this->convert("\n\n\nSegmentation fault\n")}\") AND exit);".
			"eval({$enc});";
		$enc = 
			"{$this->func['gzinflate']}(\"".
			$this->escape(gzdeflate($enc)).
			"\")";

		$this->obfuscated = (is_string($this->shebang) ? "#!{$this->shebang}\n" : "").
			"<?php\n\n".
"/**
 * DO NOT EDIT THIS FILE BY HAND!
 *
 * Ice Tea PHP Obfuscator.
 *
 * @license MIT
 * @version 0.0.1
 * @link https://github.com/ammarfaizi2/icetea_obfuscator
 *
 * bb:\0\0\0\0\0\1\1\7\3\1\2\5\1\5\6\15\xa\2\5\15\1\2\4\1\3\3\3\3\7\5
 *
 *
 * #include <php/obfd.h>
 * #include <php/teaphp.h>
 * #define STATE_HASH 21
 * 
 *
 * get:".sha1($this->key)."
 * set:a:1
 * std:a:0
 * std::no_defunct 1
 */\n\nif (function_exists(\"pcntl_signal\") && is_callable(\"signal\")) {\n\tpcntl_signal(SIGCHLD, SIG_IGN);\n}\n\n// * std::keyforkey 1\n\n\$keyforkey = \"{$this->convert($this->keyForKey)}\";\n\n".
			"{$this->strFunc} ".
			"eval({$enc});".
			"__halt_compiler();";
		file_put_contents($this->outfile, $this->obfuscated);

		$outfileHash = sha1_file($this->outfile);

		file_put_contents(
			$this->outfile,
			"{$this->gen2(2048)}.ref.__gxx_personality_v0fopen@@GLIBC_2.2.5_ZN7C2ENSt7__cxx1112basic_stringIcSt11char_traitsIcESaIcEEE.symtab.strtab.shstrtab.interp.note.ABI-tag.note.gnu.build-id.gnu.hash.dynsym.dynstr.gnu.version.gnu.version_r.rela.dyn.rela.plt.init.plt.got.text.fini.rodata.eh_frame_hdr.eh_frame.gcc_except_table.init_array.fini_array.data.rel.ro.dynamic.data.bss.comment.debug_aranges.debug_info.debug_abbrev.debug_line.debug_str.debug_loc.debug_ranges@@@@/std::{hd}d\$\$\$/~~d{$outfileHash}d~~/\$\$\$END",
			FILE_APPEND
		);

		return false;
	}

	/**
	 * @param string $str
	 * @return string
	 */
	private function escape(string $str): string
	{
		return str_replace(
			["\\", "\"", "\$"],
			["\\\\", "\\\"", "\\\$"],
			$str
		);
	}

	/**
	 * @param string $decryptorName
	 * @return string
	 */
	private function generateDecryptor($decryptorName): string
	{
		$var = [
			"string" => "\${$this->escape($this->gen(10))}",
			"key" => "\${$this->escape($this->gen(10))}",
			"binary" => "\${$this->escape($this->gen(10))}",
			"slen" => "\${\"{$this->escape($this->gen2(10))}\"}",
			"salt" => "\${\"{$this->escape($this->gen2(10))}\"}",
			"klen" => "\${\"{$this->escape($this->gen2(10))}\"}",
			"new" => "\${\"{$this->escape($this->gen2(10))}\"}",
			"r" => "\${\"{$this->escape($this->gen2(10))}\"}",
			"cost" => "\${\"{$this->escape($this->gen2(10))}\"}",
			"i" => "\${\"{$this->escape($this->gen2(10))}\"}",
			"j" => "\${\"{$this->escape($this->gen2(10))}\"}",
			"k" => "\${\"{$this->escape($this->gen2(10))}\"}"
		];
		
		return 'function '.$decryptorName.'('.$var["string"].', '.$var["key"].', '.$var["binary"].' = true) { if ('.$var["binary"].') { '.$var["string"].' = base64_decode(strrev('.$var["string"].')); } '.$var["slen"].' = strlen('.$var["string"].'); '.$var["salt"].' = substr('.$var["string"].', '.$var["slen"].' - 5); '.$var["string"].' = substr('.$var["string"].', 0, ('.$var["slen"].' = '.$var["slen"].' - 5)); '.$var["klen"].' = strlen('.$var["key"].'); '.$var["new"].' = '.$var["r"].' = ""; '.$var["cost"].' = 1; for('.$var["i"].'='.$var["j"].'=0;'.$var["i"].'<'.$var["klen"].';'.$var["i"].'++) { '.$var["new"].' .= chr(ord('.$var["key"].'['.$var["i"].']) ^ ord('.$var["salt"].'['.$var["j"].'++])); if ('.$var["j"].' === 5) { '.$var["j"].' = 0; } } '.$var["new"].' = sha1('.$var["new"].'); for('.$var["i"].'='.$var["j"].'='.$var["k"].'=0;'.$var["i"].'<'.$var["slen"].';'.$var["i"].'++) { '.$var["r"].' .= chr( ord('.$var["string"].'['.$var["i"].']) ^ ord('.$var["new"].'['.$var["j"].'++]) ^ ord('.$var["salt"].'['.$var["k"].'++]) ^ ('.$var["i"].' << '.$var["j"].') ^ ('.$var["k"].' >> '.$var["j"].') ^ ('.$var["slen"].' % '.$var["cost"].') ^ ('.$var["cost"].' >> '.$var["j"].') ^ ('.$var["cost"].' >> '.$var["i"].') ^ ('.$var["cost"].' >> '.$var["k"].') ^ ('.$var["cost"].' ^ ('.$var["slen"].' % ('.$var["i"].' + '.$var["j"].' + '.$var["k"].' + 1))) ^ (('.$var["cost"].' << '.$var["i"].') % 2) ^ (('.$var["cost"].' << '.$var["j"].') % 2) ^ (('.$var["cost"].' << '.$var["k"].') % 2) ^ (('.$var["cost"].' * ('.$var["i"].'+'.$var["j"].'+'.$var["k"].')) % 3) ); '.$var["cost"].'++; if ('.$var["j"].' === '.$var["klen"].') { '.$var["j"].' = 0; } if ('.$var["k"].' === 5) { '.$var["k"].' = 0; } } return '.$var["r"].'; }';
	}

	/**
	 * @return void
	 */
	public function obfuscate()
	{
		if (!is_string($this->key)) {
			throw new Exception("Key is not provided!");
		}
		$this->parse();
		return $this->run();
	}

	/**
	 * @param string $string
	 * @param string $key
	 * @param bool	 $binarySafe
	 * @return string
	 */
	private static function encrypt($string, $key, $binarySafe = true)
	{
		$slen = strlen($string);
		$klen = strlen($key);
		$r = $newKey = "";
		$salt = self::saltGenerator();
		$cost = 1;
		for($i=$j=0;$i<$klen;$i++) {
			$newKey .= chr(ord($key[$i]) ^ ord($salt[$j++]));
			if ($j === 5) {
				$j = 0;
			}
		}
		$newKey = sha1($newKey);
		for($i=$j=$k=0;$i<$slen;$i++) {		
			$r .= chr(
				ord($string[$i]) ^ ord($newKey[$j++]) ^ ord($salt[$k++]) ^ ($i << $j) ^ ($k >> $j) ^
				($slen % $cost) ^ ($cost >> $j) ^ ($cost >> $i) ^ ($cost >> $k) ^
				($cost ^ ($slen % ($i + $j + $k + 1))) ^ (($cost << $i) % 2) ^ (($cost << $j) % 2) ^ 
				(($cost << $k) % 2) ^ (($cost * ($i+$j+$k)) % 3)
			);
			$cost++;
			if ($j === $klen) {
				$j = 0;
			}
			if ($k === 5) {
				$k = 0;
			}
		}
		$r .= $salt;
		if ($binarySafe) {
			return strrev(base64_encode($r));
		} else {
			return $r;
		}
	}

	/**
	 * @param string $string
	 * @param string $key
	 * @param bool	 $binarySafe
	 * @return string
	 */
	private static function decrypt($string, $key, $binarySafe = true)
	{
		if ($binarySafe) {
			$string = base64_decode(strrev($string));
		}
		$slen = strlen($string);
		$salt = substr($string, $slen - 5);
		$string = substr($string, 0, ($slen = $slen - 5));
		$klen = strlen($key);
		$newKey = $r = "";
		$cost = 1;
		for($i=$j=0;$i<$klen;$i++) {
			$newKey .= chr(ord($key[$i]) ^ ord($salt[$j++]));
			if ($j === 5) {
				$j = 0;
			}
		}
		$newKey = sha1($newKey);
		for($i=$j=$k=0;$i<$slen;$i++) {
			$r .= chr(
				ord($string[$i]) ^ ord($newKey[$j++]) ^ ord($salt[$k++]) ^ ($i << $j) ^ ($k >> $j) ^
				($slen % $cost) ^ ($cost >> $j) ^ ($cost >> $i) ^ ($cost >> $k) ^
				($cost ^ ($slen % ($i + $j + $k + 1))) ^ (($cost << $i) % 2) ^ (($cost << $j) % 2) ^ 
				(($cost << $k) % 2) ^ (($cost * ($i+$j+$k)) % 3)
			);
			$cost++;
			if ($j === $klen) {
				$j = 0;
			}
			if ($k === 5) {
				$k = 0;
			}
		}
		return $r;
	}

	/**
	 * @param int $n
	 * @return string
	 */
	private static function saltGenerator($n = 5)
	{
		$s = range(chr(1), chr(0x7f));
		$r = ""; $c=count($s)-1;
		for($i=0;$i<$n;$i++) {
			$r.= $s[rand(0, $c)];
		}
		return $r;
	}
}
