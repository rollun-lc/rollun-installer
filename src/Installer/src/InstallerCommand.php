<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 16.12.17
 * Time: 00:50
 */

namespace rollun\installer;

use Composer\Command\BaseCommand;
use function Couchbase\basicEncoderV1;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallerCommand extends BaseCommand
{

	const CMD_NAME = "lib";

	const CMD_ARG_INSTALL = 'install';

	const CMD_ARG_UNINSTALL = 'uninstall';

	const CMD_OPTION_LANG = "lang";

	const CMD_OPTION_DEBUG = "debug";

	/**
	 * InstallerCommand constructor.
	 */
	public function __construct()
	{
		parent::__construct(static::CMD_NAME);
		$this->addArgument(
			"type",
			InputArgument::REQUIRED,
			"Lib management(configuration) type (install|uninstall)."
		);
		$this->addOption(
			"lang",
			"-l",
			InputOption::VALUE_OPTIONAL,
			"Output language.",
			"en"
		);

		//TODO: remove this. see isDebug (OutputInterface or --verbose options)
		$this->addOption(
			"debug",
			"-deb",
			InputOption::VALUE_OPTIONAL,
			"Enable debug info. (Can use -v|verbose mode.)",
			0
			);
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		try {
            $isDebug =(bool)($input->getOption("debug") || $input->getOption("verbose"));
            define("isDebug", $isDebug);

            $rootInstaller = new RootInstaller($this->getComposer(), $this->getIO());

			$lang = $input->getOption("lang");
			switch ($input->getArgument("type"))
			{
				case self::CMD_ARG_INSTALL:
					$rootInstaller->install($lang);
					break;
				case self::CMD_ARG_UNINSTALL:
					$rootInstaller->uninstall();
					break;
				default:
					$this->getIO()->writeError("Call with invalid type.");
					$this->getIO()->write($this->getUsages());
					break;
			}
		} catch (\Throwable $throwable) {
			$this->getIO()->writeError("Error: " . $throwable->getMessage());
		}
	}
}