<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/06/11
 * Time: 7:28
 * To change this template use File | Settings | File Templates.
 */
class UserList extends DB_UseSharedObjects implements Extending_Interface_AfterGet {

    function doAfterGetFromDB($dataSourceName, $result)
    {
        $resultArray = array();
        foreach( $result as $record )   {
            $groups = $this->dbSettings->currentDataAccess->authSupportGetGroupsOfUser($record['username']);
            sort($groups);
            $record['belonging'] = implode(', ', $groups);
            $resultArray[] = $record;
        }
        return $resultArray;
    }
}