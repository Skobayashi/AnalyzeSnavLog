<?php


namespace Asl\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class SummaryUserAccess extends AbstractCommand
{

    public function configure ()
    {
        $this->setName('summary-user-access')
            ->setDescription('アクセスログをユーザで集計する');

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
        $this->_initConfig();
        $this->_initReader($input->getArgument('log_file'));

        $date = $input->getArgument('date');
        $date = (is_null($date)) ? '.*': $date;
        $date = str_replace('/', '\/', $date);
        $conf = $this->getValues()['headers'];

        $books = [];

        while ($row = $this->reader->getParser()->parse()) {
            if ($this->reader->getParser()->getNumberOfRow() === 1) continue;
            if (! preg_match("/$date/", $row[$conf['date']])) continue;

            if (! isset($books[$row[$conf['user']]])) $books[$row[$conf['user']]] = 0;
            $books[$row[$conf['user']]] += $row[$conf['access']];
        }

        arsort($books);
        foreach ($books as $k => $b) {
            echo sprintf('"%s","%s"', $k, $b).PHP_EOL;
        }
    }
}
