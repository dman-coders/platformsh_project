<?php
namespace Drupal\platformsh_project\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Drupal\platformsh_project\Check\PingCheck;

class PingCheckCommand extends Command
{
  protected static $defaultName = 'ping-check';

  protected function configure()
  {
    $this->setDescription('Checks the HTTP status of a given URL')
      ->addArgument('url', InputArgument::REQUIRED, 'The URL to check');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $url = $input->getArgument('url');
    $args = ['url'=>$url];
    $check = \Drupal\platformsh_project\Check\PingCheck::execute($args, &$status);
    $output->writeln($check);
    return Command::SUCCESS;

  }
}
