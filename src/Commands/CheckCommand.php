<?php
namespace Drupal\platformsh_project\Commands;

use Drupal\platformsh_project\Check\Check;
use Drupal\platformsh_project\Check\PingCheck;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class CheckCommand extends Command{
  protected static $defaultName = 'check';
  protected static $checkClass = Check::class;

  protected static $allowedFormatOptions = ['text', 'json', 'html'];

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

    $args = $this->get_flattened_args($input);

    // Process based on the format
    switch($format) {
      case 'text':
        $formatted_result = $this->execute_text($args, $status);
        break;
      case 'json':
        $formatted_result = $this->execute_json($args, $status);
        break;
      case 'html':
        $formatted_result = $this->execute_html($args, $status);
        break;
    }
    if ($status > 0) {
      $output->writeln('<error>Check failed</error>');
    }
    $output->writeln($formatted_result);

    return Command::SUCCESS;
  }

  /**
   * @param array $args Keypair of named arguments, as defined in the addArgument setup of the command definition.
   * @param int $status
   *
   * @return mixed
   */
  protected function executeCheck($args, &$status) {
    // Execute the check provided by the named `Check` class.
    $checkClass = static::$checkClass;
    // Ensure the class exists and has the method before trying to call it
    if (class_exists($checkClass)) {
      if(! method_exists($checkClass,'execute')){
        throw new \Exception("Method execute does not exist in class $checkClass");
      }
    } else {
      throw new \Exception("Class $checkClass does not exist");
    }
    $status = null;
    // This is expected to return a single, simple value.
    return $checkClass::execute($args, $status, $log);

  }

  protected function execute_text($args, &$status) {
    return static::executeCheck($args, $status);
  }
  function execute_json($args, &$status) {
    return static::executeCheck($args, $status);
  }
  function execute_html($args, &$status) {
    return static::executeCheck($args, $status);
  }

  /**
   * Retrieve the flattened arguments from the Command definition as an array.
   *
   * @ $input InputInterface The input object with some of the args in it
   * @return array The flattened arguments.
   */
  protected function get_flattened_args(InputInterface $input):array{
    $args = [];
    foreach ($this->getDefinition()->getArguments() as $key => $value) {
        $args[$key] = $input->getArgument($key);
    }
    return $args;
  }

}
