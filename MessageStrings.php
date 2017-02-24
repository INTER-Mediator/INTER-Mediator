<?php

/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
class MessageStrings
{

    public function getMessages()
    {
        return $this->messages;
    }

    public function getMessageAs($num, $appending = null)
    {
        $msg = $this->messages[$num];
        $index = 1;
        if (! is_null($appending)) {
            foreach ($appending as $keyword) {
                $msg = str_replace("@{$index}@", $keyword, $msg);
                $index++;
            }
        }
        return $msg;
    }

    public $messages = array(
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
        12 => 'Detail',
        13 => 'Show List',
        14 => 'Copy',
        15 => 'Copy Record',
        1001 => "Other people might be updated.\n\nInitially=@1@\nCurrent=@2@\nDatabase=@3@\n\nYou can overwrite with your data if you select OK.",
        1002 => "Can't determine the Table Name: @1@",
        1003 => "No information to update: field=@1@",
        1004 => "Connection Error in db_query=@1@/@2@",
        1005 => "On calling db_query, Required parameter 'name' doesn't specified",
        1006 => "On calling db_query, Required parameter 'table' doesn't specified",
        1007 => "On calling db_update, Required parameter 'name' doesn't specified",
        1008 => "On calling db_update, Required parameter 'conditions' doesn't specified",
        1009 => "On calling db_update, Required parameter 'operator' doesn't specified",
        1010 => "On calling db_update, Required parameter 'value' doesn't specified",
        1011 => "On calling db_update, Required parameter 'dataset' doesn't specified",
        1012 => "Query Access: ",
        1013 => "Update Access: ",
        1014 => "Connection Error in db_update=@1@/@2@",
        1015 => "Connection Error in db_delete=@1@/@2@",
        1016 => "Connection Error in db_createRecord=@1@/@2@",
        1017 => "Delete Access: ",
        1018 => "Create Record Access: ",
        1019 => "On calling db_delete, Required parameter 'name' doesn't specified",
        1020 => "On calling db_delete, Required parameter 'conditions' doesn't specified",
        1021 => "On calling db_createRecord, Required parameter 'name' doesn't specified",
        1022 => 'Using Unsupported Browser (Try not to use the compatibility mode of Internet Explorer)',
        1023 => '[This site uses INTER-Mediator.]',
        1024 => 'Multiple records are going to be updated. The key field might be wrong. Are you sure?',
        1025 => 'Are you sure to delete?',
        1026 => 'Are you sure to create record?',
        1027 => "Get Challenge: ",
        1028 => "Connection Error in get_challenge=@1@/@2@",
        1029 => "Change Password Access: ",
        1030 => "Connection Error on changing password=@1@/@2@",
        1031 => "Change File Uploading: ",
        1032 => "Connection Error on uploading file=@1@/@2@",
        1033 => "The field name specified in the page file doesn't exist [folder=@1@]",
        1034 => "Other people might be updated.\n\n@1@\n\nYou can overwrite with your data if you select OK.",
        1035 => "field=@1@, initial value=@2@, current value=@3@\n",
        1036 => "field=@1@, expression=@2@ happens a parse error.",
        1037 => "A cyclic referencing is detected.",
        1038 => "Preparing the Pusher, although the settings in the server side is available, the client library isn't included.",
        1039 => "Preparing the Pusher, although the client library is included, the settings in the server side is not available.",
        1040 => "The field '@2@' in the context '@1@' does not exist in the table.",
        1041 => "Are you sure to copy this record?",
        1042 => "The database class doesn't support aggregation-select/from/group-by.",
        1043 => "Both aggregation-select and aggregation-from are required. One of them doesn't exist in the context definition.",
        1044 => "The context having aggregation-select/from/group-by is read-only.",
        1045 => "The 'key' is required in the context definition if writing operations apply to the context '@1@'.",
        1046 => "The context definition of the name '@1@' in target specifications of the page file is undefined in the definition file.",
        1047 => "The context '@1@' was chosen for this context, and ignored target specifications were: @2@",
        2001 => 'Authentication Error!',
        2002 => 'User:',
        2003 => 'Password:',
        2004 => 'Login',
        2005 => 'Change Password',
        2006 => 'New Password:',
        2007 => 'Missing any of Username, old and new password.',
        2008 => 'Failure to get a challenge from server.',
        2009 => 'Succeed to change your password. Login with the new password.',
        2010 => 'Failure to change your password. Maybe the old password is not correct.',
        2011 => 'User(Mail Address):',
        2012 => 'Retry to login. You should clarify the user and the password.',
        2013 => 'You should input user and/or password.',
        2014 => 'OAuth Login',
        2015 => 'Any alphabets have to contain in new password.',
        2016 => 'Any numbers have to contain in new password.',
        2017 => 'Any upper case alphabets have to contain in new password.',
        2018 => 'Any lower case alphabets have to contain in new password.',
        2019 => 'Any punctuations have to contain in new password.',
        2020 => 'New password have to differ from the user name.',
        2021 => 'New password have to contain more than @1@ characters.',
        3101 => 'Drag Here.',
        3102 => 'Dragged File: ',
        3201 => "Exceeded post size limit. Check the post_max_size in php.ini file.",
        3202 => "No file wasn't uploaded. Possibly, exceeded file size limit.",
        3203 => "Exceeded file size limit. Check the upload_max_filesize in php.ini file.",
        3204 => "Partially uploaded.",
        3205 => "Temporary directory doesn't exist.",
        3206 => "Can't write to disk or file system.",
        3207 => "Extension module prevents to upload.",
        3208 => "Unknown error in file uploading.",
        3209 => "Select File...",
        3210 => "Selected File: ",
        3211 => "Upload",
    );
}
