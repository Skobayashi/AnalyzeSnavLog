<?php


namespace Asl\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;

use Asl\Reader\Reader;
use Asl\Reader\Parser\CsvParser;

abstract class AbstractCommand extends Command
{

    private $config_path = 'data/config.yml';

    private $values;

    protected $reader;


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

}

