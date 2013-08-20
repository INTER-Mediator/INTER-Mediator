<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2012 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/20
 * Time: 14:24
 * To change this template use File | Settings | File Templates.
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

    function setSettings($dbSettings)
    {
        $this->dbSettings = $dbSettings;
    }

    function setLogger($logger)
    {
        $this->logger = $logger;
    }

    function setFormatter($formatter)
    {
        $this->formatter = $formatter;
    }
}

