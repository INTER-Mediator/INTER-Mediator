<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/20
 * Time: 14:24
 * To change this template use File | Settings | File Templates.
 */
abstract class DB_UseSharedObjects
{
    var $dbSettings = null;
    var $logger = null;
    var $formatter = null;

    function setUpSharedObjects( $obj = null )
    {
        if ( $obj === null )    {
//            require_once('DB_Settings.php');
            $this->setSettings(new DB_Settings());
//            require_once('DB_Logger.php');
            $this->setLogger(new DB_Logger());
//            require_once('DB_Formatters.php');
            $this->setFormatter(new DB_Formatters());
        } else {
            $this->setSettings($obj->dbSettings);
            $this->setLogger($obj->logger);
            $this->setFormatter($obj->formatter);
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

