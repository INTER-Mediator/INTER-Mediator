<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
*
*   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
*   INTER-Mediator is supplied under MIT License.
*/

abstract class DB_UseSharedObjects
{
    public $dbSettings = null;
    public $logger = null;
    public $formatter = null;
    public $dbClass = null;
    public $proxyObject = null;

    public function setUpSharedObjects( $obj = null )
    {
        if ( $obj === null )    {
            $this->setSettings(new DB_Settings());
            $this->setLogger(new DB_Logger());
            $this->setFormatter(new DB_Formatters());
        } else {
            $this->setSettings($obj->dbSettings);
            $this->setLogger($obj->logger);
            $this->setFormatter($obj->formatter);
            $this->dbClass = $obj->dbClass;
            $this->proxyObject = $obj;
        }
    }

    private function setSettings($dbSettings)
    {
        $this->dbSettings = $dbSettings;
    }

    private function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function setFormatter($formatter)
    {
        $this->formatter = $formatter;
    }
}

