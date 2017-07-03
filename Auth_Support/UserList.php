<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/06/11
 * Time: 7:28
 * To change this template use File | Settings | File Templates.
 */
class UserList extends DB_UseSharedObjects implements Extending_Interface_AfterRead {

    public function doAfterReadFromDB($result)
    {
        $resultArray = array();
        foreach( $result as $record )   {
            $groups = $this->dbSettings->getCurrentDataAccess()->authTableGetGroupsOfUser($record['username']);
            sort($groups);
            $record['belonging'] = implode(', ', $groups);
            $resultArray[] = $record;
        }
        return $resultArray;
    }
}