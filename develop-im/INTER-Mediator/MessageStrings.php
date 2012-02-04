<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/

class MessageStrings
{

    function getMessages()
    {
        return $this->messages;
    }

    var $messages = array(
        1 => 'Record #',
        2 => 'Refresh',
        3 => 'Add Record',
        4 => 'Delete Record',
        5 => 'Insert',
        6 => 'Delete',
        7 => 'Save',
        1001 => "Other people might be updated.\n\nInitially=@1@\nCurrent=@2@\nDatabase=@3@\n\nYou can overwrite with your data if you select OK.",
        1002 => "Can't determine the Table Name: @1@",
        1003 => "No information to update: field=@1@",
        1004 => "Connection Error in db_query=@1@/@2@",
        1005 => "On calling db_query, Requred parameter 'name' doesn't specified",
        1006 => "On calling db_query, Requred parameter 'table' doesn't specified",
        1007 => "On calling db_update, Requred parameter 'name' doesn't specified",
        1008 => "On calling db_update, Requred parameter 'conditions' doesn't specified",
        1009 => "On calling db_update, Requred parameter 'operator' doesn't specified",
        1010 => "On calling db_update, Requred parameter 'value' doesn't specified",
        1011 => "On calling db_update, Requred parameter 'dataset' doesn't specified",
        1012 => "Query Access: ",
        1013 => "Update Access: ",
        1014 => "Connection Error in db_update=@1@/@2@",
        1015 => "Connection Error in db_delete=@1@/@2@",
        1016 => "Connection Error in db_createRecord=@1@/@2@",
        1017 => "Delete Access: ",
        1018 => "Create Record Access: ",
        1019 => "On calling db_delete, Requred parameter 'name' doesn't specified",
        1020 => "On calling db_delete, Requred parameter 'conditions' doesn't specified",
        1021 => "On calling db_createRecord, Requred parameter 'name' doesn't specified",
        1022 => 'Using Unsupported Browser',
        1023 => '[This site uses INTER-Mediator.]',
        1024 => 'Multiple records are going to be updated. The key field might be wrong. Are you sure?',
        1025 => 'Are you sure to delete?',
        1026 => 'Are you sure to create record?',
        2001 => 'Authentication Error!',
        2002 => 'User:',
        2003 => 'Password:',
        2004 => 'Log In',
    );
}

?>