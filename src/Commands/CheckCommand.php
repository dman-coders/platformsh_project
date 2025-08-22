<?php

namespace Drupal\platformsh_project\Commands;

use Drupal\platformsh_project\Check\Check;
use Robo\Log\RoboLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base command for running checks.
 */
class CheckCommand extends Command {

  /**
   * The default command name.
   *
   * @var string
   */
  protected static $defaultName = 'check';

  /**
   * The check class to use.
   *
   * @var string
   */
  protected static $checkClass = Check::class;

  /**
   * Allowed format options.
   *
   * @var array
   */
  protected static array $allowedFormatOptions = ['text', 'json', 'html'];

  /**
   * The logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * Configure the command.
   */
  protected function configure() {
    $allowedFormatOptions = static::$allowedFormatOptions;
    $this->setDescription('Checks the status of things')
      ->addOption(
        'format',
        'f',
        InputArgument::OPTIONAL,
        'The format of the output.',
        'text',
        $allowedFormatOptions
      );
  }

  /**
   * Execute the command.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input interface.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output interface.
   *
   * @return int
   *   The command exit code.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $format = $input->getOption('format');
    if (!in_array($format, static::$allowedFormatOptions)) {
      $output->writeln('<error>Invalid format option. Allowed options are ' . implode(', ', static::$allowedFormatOptions) . '</error>');
      return Command::INVALID;
    }
    // The logger will automatically respect the `-v,-vv,-vvv` verbosity flags.
    $this->logger = new RoboLogger($output);
    $args = $this->getFlattenedArgs($input);
    $result = "";

    // Process based on the format.
    switch ($format) {
      case 'text':
        $status = $this->executeText($args, $result);
        break;

      case 'json':
        $status = $this->executeJson($args, $result);
        break;

      case 'html':
        $status = $this->executeHtml($args, $result);
        break;
    }
    if ($status > 0) {
      $this->logger->error('Check failed');
    }
    $output->writeln($result);

    // If the check returns OK or NOTICE response, it's a SUCCESS,
    // ERROR is a FAILURE.
    // Blindingly obvious, BUT, whether a check returns a false,
    // and whether the command actually ran is a difference sometimes.
    // A failed question is different from a question that was answered "no".
    return static::mapReturnCodesToCheckStatus($status);
  }

  /**
   * Map the return codes from the Check class to the Symfony Command\n   *
   * status codes.
   *
   * These are mostly identical (0,1,2) but the Check class has an additional
   * status code for "Not Applicable".
   *
   * @param int $status
   *   The status code from the check.
   *
   * @return int
   *   The Symfony command status code.
   */
  public function mapReturnCodesToCheckStatus($status) {
    $matrix = [
      Check::OK => Command::SUCCESS,
      Check::NOTICE => Command::SUCCESS,
      Check::ERROR => Command::FAILURE,
      Check::NA => Command::INVALID,
    ];
    return $matrix[$status];
  }

  /**
   * Execute the check.
   *
   * @param array $args
   *   Keypair of named arguments, as defined in the addArgument setup of the
   *   command definition.
   * @param mixed $result
   *   Additional info about the check result.
   *   Error message or other data.
   *
   * @return int
   *   The check status code.
   */
  protected function executeCheck($args, &$result) {
    // Execute the check provided by the named `Check` class.
    $checkClass = static::$checkClass;
    // Ensure the class exists and has the method before trying to call it.
    if (class_exists($checkClass)) {
      if (!method_exists($checkClass, 'execute')) {
        throw new \Exception("Method execute does not exist in class $checkClass");
      }
    }
    else {
      throw new \Exception("Class $checkClass does not exist");
    }
    $status = NULL;
    // This is expected to return a single, simple value.
    return $checkClass::execute($args, $result, $this->logger);
  }

  /**
   * Execute check and return text result.
   *
   * @param array $args
   *   The check arguments.
   * @param mixed $result
   *   The result reference.
   *
   * @return int
   *   The status code.
   */
  protected function executeText($args, &$result) {
    return static::executeCheck($args, $result);
  }

  /**
   * Execute check and return JSON result.
   *
   * @param array $args
   *   The check arguments.
   * @param mixed $result
   *   The result reference.
   *
   * @return int
   *   The status code.
   */
  public function executeJson($args, &$result) {
    $status = static::executeCheck($args, $result);
    $structResult = [
      'check' => static::$checkClass::NAME,
      'args' => $args,
      'result' => $result,
      'status' => $status,
    ];
    $result = json_encode($structResult);
    return $status;
  }

  /**
   * Execute check and return HTML result.
   *
   * @param array $args
   *   The check arguments.
   * @param mixed $result
   *   The result reference.
   *
   * @return int
   *   The status code.
   */
  public function executeHtml($args, &$result) {
    return static::executeCheck($args, $result);
  }

  /**
   * Retrieve the flattened arguments from the Command definition as an array.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input object with some of the args in it.
   *
   * @return array
   *   The flattened arguments.
   */
  protected function getFlattenedArgs(InputInterface $input): array {
    $args = [];
    foreach ($this->getDefinition()->getArguments() as $key => $value) {
      $args[$key] = $input->getArgument($key);
    }
    return $args;
  }

}
