<?php
/*
 * INTER-Mediator Ver.0.63 Released 2011-05-29
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/**
 * Created by JetBrains PhpStorm.
 * User: nii
 * Date: 11/05/22
 * Time: 20:24
 * To change this template use File | Settings | File Templates.
 */

include_once('../INTER-Mediator/DB_FileMaker_FX.php');

class DB_WebSite_FMSFX extends DB_FileMaker_FX	{

    function getFromDB( )	{
        $returnValue = parent::getFromDB();
        if ( count( $returnValue ) > 1 )    {
            // Check for the language of documents
            $lang = array();
            foreach( $returnValue as $record )  {
                $lang[$record['Language']] += 1;
            }
            if ( count( $lang ) > 1 )   {
                $clientLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
                $seps = array(',','-',';','=');
                $firstSep = strlen($clientLang);
                foreach ( $seps as $sep )   {
                    $curPos = strpos( $clientLang, $sep );
                    if ( $curPos !== false && $curPos < $firstSep ) {
                        $firstSep = $curPos;
                    }
                }
                $priorLang = substr( $clientLang, 0, $firstSep );
                $selectedRecords = array();
                foreach( $returnValue as $record )  {
                    if ( $record['Language'] == $priorLang )    {
                        $selectedRecords[] = $record;
                    }
                }
                if ( count( $selectedRecords ) == 0 )   {
                    $priorLang = 'en';
                    foreach( $returnValue as $record )  {
                        if ( $record['Language'] == $priorLang )    {
                            $selectedRecords[] = $record;
                        }
                    }
                    if ( count( $selectedRecords ) == 0 )   {
                        $maxLang = 'en';
                        $maxLangCount = -1;
                        foreach( $lang as $language=>$count )   {
                            if ( $count > $maxLangCount )   {
                                $maxLang = $language;
                                $maxLangCount = $count;
                            }
                            foreach( $returnValue as $record )  {
                                if ( $record['Language'] == $maxLang )    {
                                    $selectedRecords[] = $record;
                                }
                            }
                        }
                    }
                }
                return $selectedRecords;
            }
        }
        return $returnValue;
    }
}
