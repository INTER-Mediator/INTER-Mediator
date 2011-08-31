<?php 
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

class MessageStrings	{

function getMessages()	{
	return $this->messages;
}

var $messages = array(
	1	=>	'Record #',
	2	=>	'Refresh',
	3	=>	'Add Record',
	4	=>	'Delete Record',
	5	=>	'Insert',
	6	=>	'Delete',
	7	=>	'Prev',
	8	=>	'Next',
	9	=>	'End',
	10	=>	'No.@2@ of @1@ records',
	11	=>	'No.@2@ - @3@ of @1@ records',
	101	=>	'The result of query has no record.',
	102	=>	'This record was saved without any errors.',
	103	=>	'Any erros have arrised when this record was saved.',
	104	=>	'Communicating to save this record.',
	105	=>	'You modified any data howerver they are not saved yet.\n\nIf you waht to save them, click "Cancel" and "Save". If you click "OK", all modification are abondoned',
	104	=>	'No data to store.',
	105	=>	'Modified, inserted or deleted data isn\'t saved yet\n\nIf you need to save them, click "Cancel" and then click "Save". If you clcck "OK" here, all modified data will be abandaned.',
	106	=>	'There is no data to save.',
	107	=>	'The data of key field in a repeated table is empty. You can\'t delete or edit this record.',
	108	=>	'You\'ve deleted a record howerver it has empty key field. You can\'t delete or save it.',
	109	=>	'The saving task hasn\'t finished.',
	110	=>	'The field @1@ shouldn\'t be blank.',
	111	=>	'The field @1@ should be an email address.',
    1001 => "Other people might be updated.\n\nInitially=@1@\nCurrent=@2@\n\nYou can overwrite with your data if you select OK.",
	1002 => "Can't determine the Table Name: @1@",
);
}
?>