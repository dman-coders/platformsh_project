<?php

namespace Drupal\platformsh_project\Commands;

use Drupal\platformsh_project\Check\Check;
use Robo\Log\RoboLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class CheckCommand extends Command {

  protected static $defaultName = 'check';

  protected static $checkClass = Check::class;

  protected static $allowedFormatOptions = ['text', 'json', 'html'];

  private $logger;

  public function __construct() {
    // create a log channel
    #$this->logger = new Logger('log_to_stderr');
    # $this->logger->pushHandler(new StreamHandler("php://stderr"));

    parent::__construct();
  }

  protected function configure() {
    $allowedFormatOptions = static::$allowedFormatOptions;
    $this->setDescription('Checks the status of things')
      ->addOption('format', 'f', InputArgument::OPTIONAL, 'The format of the output. ', 'text', $allowedFormatOptions);
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $format = $input->getOption('format');
    if (!in_array($format, static::$allowedFormatOptions)) {
      $output->writeln('<error>Invalid format option. Allowed options are ' . implode(', ', static::$allowedFormatOptions) . '</error>');
      return Command::INVALID;
    }
    // The logger will automatically respect the `-v,-vv,-vvv` verbosity flags.
    $this->logger = new RoboLogger($output);
    $args = $this->get_flattened_args($input);
    $result = "";

    // Process based on the format
    switch ($format) {
      case 'text':
        $status = $this->execute_text($args, $result);
        break;
      case 'json':
        $status = $this->execute_json($args, $result);
        break;
      case 'html':
        $status = $this->execute_html($args, $result);
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
    return static::map_return_codes_to_check_status($status);
  }

  /**
   * Map the return codes from the Check class to the Symfony Command status
   * codes.
   *
   * These are mostly identical (0,1,2) but the Check class has an additional
   * status code for "Not Applicable".
   *
   * @param int $status
   *
   * @return int
   */
  function map_return_codes_to_check_status($status) {
    $matrix = [
      Check::OK => Command::SUCCESS,
      Check::NOTICE => Command::SUCCESS,
      Check::ERROR => Command::FAILURE,
      Check::NA => Command::INVALID,
    ];
    return $matrix[$status];
  }

  /**
   * @param array $args Keypair of named arguments, as defined in the
   *   addArgument setup of the command definition.
   * @param mixed $result
   *   Additional info about the check result. Error message or other data.
   *
   * @return int
   */
  protected function executeCheck($args, &$result) {
    // Execute the check provided by the named `Check` class.
    $checkClass = static::$checkClass;
    // Ensure the class exists and has the method before trying to call it
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

  protected function execute_text($args, &$result) {
    return static::executeCheck($args, $result);
  }

  function execute_json($args, &$result) {
    $status = static::executeCheck($args, $result);
    $struct_result = [
      'check' => static::$checkClass::name,
      'args' => $args,
      'result' => $result,
      'status' => $status,
    ];
    $result = json_encode($struct_result);
    return $status;
  }

  function execute_html($args, &$result) {
    return static::executeCheck($args, $result);
  }

  /**
   * Retrieve the flattened arguments from the Command definition as an array.
   *
   * @ $input InputInterface The input object with some of the args in it
   *
   * @return array The flattened arguments.
   */
  protected function get_flattened_args(InputInterface $input): array {
    $args = [];
    foreach ($this->getDefinition()->getArguments() as $key => $value) {
      $args[$key] = $input->getArgument($key);
    }
    return $args;
  }

}
