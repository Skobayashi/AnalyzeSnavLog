<?php


namespace Asl\Service;

use Symfony\Component\Yaml\Yaml;

use Asl\Reader\Reader;
use Asl\Reader\Parser\CsvParser;
use Asl\Service\Category;

class Analyzer
{

    /**
     * @var string
     **/
    private $log_file;


    /**
     * @var string
     **/
    private $date;


    /**
     * @var array
     **/
    private $header;


    /**
     * @var Reader
     **/
    private $reader;


    /**
     * @bool
     **/
    private $dry_run = false;


    /**
     * @return void
     **/
    public function enableDryRun ()
    {
        $this->dry_run = true;
    }


    /**
     * @param  string $log_file
     * @param  string $date
     * @return void
     **/
    public function __construct ($log_file, $date = null)
    {
        $this->_initConfig();
        $this->_initReader($log_file);
        $this->date = $this->_pregQuote($date);
    }


    /**
     * コンフィグファイルを読み込む
     *
     * @return void
     **/
    private function _initConfig ()
    {
        $values = Yaml::parse(ROOT.DS.'data'.DS.'config.yml');
        $this->header = $values['config']['headers'];
    }


    /**
     * @param  string $log_path
     * @return void
     **/
    private function _initReader ($log_path)
    {
        $reader = new Reader(new CsvParser());
        $reader->setFile([
            'name' => pathinfo($log_path)['filename'],
            'tmp_name' => $log_path,
            'type' => 'text/csv'
        ]);

        $this->reader = $reader;
    }


    /**
     * @var string
     **/
    private function _pregQuote ($date)
    {
        return (is_null($date)) ? '.*': preg_quote($date, '/');
    }


    /**
     * @return string
     **/
    public function getRegexDate ()
    {
        return $this->date;
    }


    /**
     * 合計アクセス数を月別に分析する
     *
     * @return array
     **/
    public function analyzeTotalAccess ()
    {
        $result = [];

        $this->_iterateLogFile(function ($row) use (&$result) {
            $dates = explode('/', $row[$this->header['date']]);
            $date = sprintf('%s年%s月', $dates[0], $dates[1]);

            if (! isset($result[$date])) $result[$date] = 0;
            $result[$date] += $row[$this->header['access']];
        });

        $this->_saveAnalyzeFile($result);
        return $result;
    }


    /**
     * 利用者数を月別に分析する
     *
     * @return array
     **/
    public function analyzeUserAccess ()
    {
        $stock = [];

        $this->_iterateLogFile(function ($row) use (&$stock) {
            $dates = explode('/', $row[$this->header['date']]);
            $date  = sprintf('%s年%s月', $dates[0], $dates[1]);

            if (! isset($stock[$date])) $stock[$date] = [];
            if (! in_array($row[$this->header['user']], $stock[$date])) {
                $stock[$date][] = $row[$this->header['user']];
            }
        });

        $result = [];
        foreach ($stock as $k => $s) {
            $result[$k] = count($s);
        }

        return $result;
    }


    /**
     * 月別のブックアクセス数Top20を分析する
     *
     * @return array
     **/
    public function analyzeBookAccessTop20 ()
    {
        $data = [];

        $this->_iterateLogFile(function ($row) use (&$data) {
            $dates = explode('/', $row[$this->header['date']]);
            $date  = sprintf('%s年%s月', $dates[0], $dates[1]);

            if (! isset($data[$date])) $data[$date] = [];
            if (! isset($data[$date][$row[$this->header['book']]])) {
                $data[$date][$row[$this->header['book']]] = 0;
            }

            $data[$date][$row[$this->header['book']]] += $row[$this->header['access']];
        });

        $result = [];
        foreach ($data as $date => $books) {
            $result[$date] = [];
            arsort($books);

            foreach ($books as $book => $access) {
                if (count($result[$date]) >= 20) break;
                $result[$date][$book] = $access;
            }
        }

        return $result;
    }


    /**
     * 月別のユーザアクセス数Top20を分析する
     *
     * @return array
     **/
    public function analyzeUserAccessTop20 ()
    {
        $data = [];

        $this->_iterateLogFile(function ($row) use (&$data) {
            $dates = explode('/', $row[$this->header['date']]);
            $date  = sprintf('%s年%s月', $dates[0], $dates[1]);

            if (! isset($data[$date])) $data[$date] = [];
            if (! isset($data[$date][$row[$this->header['user']]])) {
                $data[$date][$row[$this->header['user']]] = 0;
            }

            $data[$date][$row[$this->header['user']]] += $row[$this->header['access']];
        });

        $result = [];
        foreach ($data as $date => $books) {
            $result[$date] = [];
            arsort($books);

            foreach ($books as $book => $access) {
                if (count($result[$date]) >= 20) break;
                $result[$date][$book] = $access;
            }
        }

        return $result;
    }


    /**
     * 日別の利用者数を分析する
     *
     * @return array
     **/
    public function analyzeUserDateAccess ()
    {
        $result = [];

        $this->_iterateLogFile(function ($row) use (&$result) {
            $dates = explode('/', $row[$this->header['date']]);
            $date  = sprintf('%s年%s月%s日', $dates[0], $dates[1], $dates[2]);

            if (! isset($result[$date])) $result[$date] = 0;
            $result[$date] += $row[$this->header['access']];
        });

        ksort($result);
        return $result;
    }


    /**
     * ブックカテゴリ別にアクセス数を分析する
     *
     * @return array
     **/
    public function analyzeBookCategory ()
    {
        $data = [];

        $this->_iterateLogFile(function ($row) use (&$data) {
            $dates = explode('/', $row[$this->header['date']]);
            $date  = sprintf('%s年%s月', $dates[0], $dates[1]);

            if (! isset($data[$date])) $data[$date] = [];
            $category = Category::convert($row[$this->header['book']]);

            if (! isset($data[$date][$category])) $data[$date][$category] = 0;
            $data[$date][$category] += $row[$this->header['access']];
        });

        return $data;
    }


    /**
     * ログファイルをイテレートする
     *
     * @param  function $analyze  分析を行う処理
     * @return void
     **/
    private function _iterateLogFile ($analyze)
    {
        while ($row = $this->reader->getParser()->parse()) {
            if ($this->reader->getParser()->getNumberOfRow() === 1) continue;
            if (count($row) == 1 && $row[0] == '') continue;
            if (! preg_match("/".$this->date."/", $row[$this->header['date']])) continue;

            $analyze($row);
        }
    }


    /**
     * 分析した結果をファイルとして保存する
     *
     * @param  array $data
     * @return void
     **/
    private function _saveAnalyzeFile ($data)
    {
        if ($this->dry_run) return;
    }
}

