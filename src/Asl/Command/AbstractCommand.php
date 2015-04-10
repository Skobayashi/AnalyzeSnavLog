<?php


namespace Asl\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;

use Asl\Reader\Reader;
use Asl\Reader\Parser\CsvParser;

abstract class AbstractCommand extends Command
{

    private $config_path = 'data/config.yml';

    private $values;

    protected $reader;

    protected $summary = [];

    protected $conf;


    public function _initConfig ()
    {
        $values = Yaml::parse(ROOT.DS.$this->config_path);
        $this->values = $values['config'];
    }


    public function _initReader ($log_file)
    {
        $reader = new Reader(new CsvParser());
        $reader->setFile([
            'name' => pathinfo($log_file)['filename'],
            'tmp_name' => $log_file,
            'type' => 'text/csv'
        ]);

        $this->reader = $reader;
    }


    public function getValues ()
    {
        return $this->values;
    }


    protected function _iterateLogFile (InputInterface $input, OutputInterface $output)
    {
        $this->_initConfig();
        $this->_initReader($input->getArgument('log_file'));

        $date = $input->getArgument('date');
        $date = (is_null($date)) ? '.*': $date;
        $date = str_replace('/', '\/', $date);

        $this->conf = $this->getValues()['headers'];

        while ($row = $this->reader->getParser()->parse()) {
            if ($this->reader->getParser()->getNumberOfRow() === 1) continue;
            if (! preg_match("/$date/", $row[$this->conf['date']])) continue;

            $this->_parse($row);
        }
    }

}

