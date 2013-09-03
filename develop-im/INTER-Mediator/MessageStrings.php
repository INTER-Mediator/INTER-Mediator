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

    function getMessageAs($num, $appending)
    {
        $msg = $this->messages[$num];
        $index = 1;
        foreach ($appending as $keyword)    {
            $msg = str_replace("@{$index}@", $keyword, $msg);
            $index++;
        }
        return $msg;
    }

    private $messages = array(
        1 => 'Record #',
        2 => 'Refresh',
        3 => 'Add Record',
        4 => 'Delete Record',
        5 => 'Insert',
        6 => 'Delete',
        7 => 'Save',
        8 => 'Login as: ',
        9 => 'Logout',
        10 => "Move to page:",
        11 => "",
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
        1027 => "Get Challenge: ",
        1028 => "Connection Error in get_challenge=@1@/@2@",
        1029 => "Change Passowrd Access: ",
        1030 => "Connection Error on changing password=@1@/@2@",
        1031 => "Change File Uploading: ",
        1032 => "Connection Error on uploading file=@1@/@2@",
        1033 => "The field name specified in the page file doesn't exist [folder=@1@]",
        2001 => 'Authentication Error!',
        2002 => 'User:',
        2003 => 'Password:',
        2004 => 'Log In',
        2005 => 'Change Password',
        2006 => 'New Password:',
        2007 => 'Missing any of Username, old and new password.',
        2008 => 'Failure to get a challenge from server.',
        2009 => 'Succeed to change your password. Login with the new password.',
        2010 => 'Failure to change your password. Maybe the old password is not correct.',
        2011 => 'User(Mail Assress):',
        3101 => 'Drag Here.',
        3102 => 'Dragged File: ',
    );
}

?>
