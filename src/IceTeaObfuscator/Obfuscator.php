<?php

namespace IceTeaObfuscator;

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
	private $outfile;

	/**
	 * @param string $file
	 * @param string $outfile
	 */
	public function __construct(string $file, string $outfile)
	{
		$this->file = $file;
		$this->outfile = $outfile;
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
	 * @return void
	 */
	private function parse(): void
	{

	}

	/**
	 * @return bool
	 */
	private function run(): bool
	{

	}

	/**
	 * @return void
	 */
	public function obfuscate()
	{
		$this->parse();
		return $this->run();
	}
}
