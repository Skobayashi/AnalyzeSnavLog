<?php


namespace Asl\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class SummaryUserBooksAccess extends AbstractCommand
{

    public function configure ()
    {
        $this->setName('summary-user-book-access')
            ->setDescription('アクセスログをユーザがアクセスしたブック別に集計する');

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

        foreach ($this->summary as $user => $books) {
            echo sprintf('"%s (%s件)",', $user, count($books));

            sort($books);
            foreach ($books as $k => $b) {
                if ($k != 0) echo ',';
                echo sprintf('"%s"', $b).PHP_EOL;
            }

            echo PHP_EOL;
        }
    }


    protected function _parse ($row)
    {
        if (! isset($this->summary[$row[$this->conf['user']]])) $this->summary[$row[$this->conf['user']]] = [];
        if (! in_array($row[$this->conf['book']], $this->summary[$row[$this->conf['user']]])) {
            $this->summary[$row[$this->conf['user']]][] = $row[$this->conf['book']];
        }
    }
}
