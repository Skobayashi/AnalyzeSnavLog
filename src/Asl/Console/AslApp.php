<?php


namespace Asl\Console;

use Symfony\Component\Console\Application;
use Asl\Command;

class AslApp extends Application
{

    /**
     * コマンドの初期化を行う
     *
     * @param  string
     * @return void
     **/
    public function __construct ()
    {
        parent::__construct(APP.' -- ', VERSION);

        $this->add(new Command\Analyze());
    }
}

