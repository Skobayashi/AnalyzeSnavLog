<?php


namespace Asl\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class SummaryBookAccess extends AbstractCommand
{

    public function configure ()
    {
        $this->setName('summary-book-access')
            ->setDescription('アクセスログをブックで集計する');

        $this->addArgument('log_file', InputArgument::REQUIRED, 'ログファイルへのパスを指定する')
            ->setHelp(sprintf(
                '%sログファイルのパス%s',
                PHP_EOL,
                PHP_EOL
            ));

        $this->addArgument('date', InputArgument::OPTIONAL, '対象範囲')
            ->setHelp(sprintf(
                '%s集計する範囲%s',
                PHP_EOL,
                PHP_EOL
            ));
    }


    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $this->_iterateLogFile($input, $output);

        arsort($this->summary);
        foreach ($this->summary as $k => $b) {
            echo sprintf('"%s","%s"', $k, $b).PHP_EOL;
        }
    }


    protected function _parse ($row)
    {
        if (! isset($this->summary[$row[$this->conf['book']]])) $this->summary[$row[$this->conf['book']]] = 0;
        $this->summary[$row[$this->conf['book']]] += $row[$this->conf['access']];
    }
}
