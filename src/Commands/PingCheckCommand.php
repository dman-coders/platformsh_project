<?php

namespace Drupal\platformsh_project\Commands;

use Symfony\Component\Console\Input\InputArgument;

use Drupal\platformsh_project\Check\PingCheck;

/**
 * Command for running ping checks.
 */
class PingCheckCommand extends CheckCommand {

  /**
   * The default command name.
   *
   * @var string
   */
  protected static $defaultName = 'ping-check';

  /**
   * Need to use the fully qualified class name at compile time.
   *
   * @var string
   */
  protected static $checkClass = PingCheck::class;

  /**
   * Configure the command.
   */
  protected function configure() {
    // The CheckCommand adds Option to choose response format.
    parent::configure();
    $this->setDescription('Checks the HTTP status of a given URL')
      ->addArgument('url', InputArgument::REQUIRED, 'The URL to check');
  }

}
