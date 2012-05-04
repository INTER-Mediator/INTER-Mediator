/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/**
 * Created by JetBrains PhpStorm.
 * User: nii
 * Date: 11/06/09
 * Time: 17:40
 * To change this template use File | Settings | File Templates.
 */
//=================================
// Database Access
//=================================
var INTERMediator_DBAdapter = {
    /*
     db_query
     Querying from database. The parameter of this function should be the object as below:

     {   name:<name of the context>
     records:<the number of retrieving records, could be null>
     fields:<the array of fields to retrieve, but this parameter is ignored so far.
     parentkeyvalue:<the value of foreign key field, could be null>
     conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records, could be null>
     useoffset:<true/false whether the offset parameter is set on the query.>    }

     This function returns recordset of retrieved.
     */
    db_query:function (args) {
        alert('The INTERMediator_DBAdapter of HTML local database isn\'t supported yet. It will be in a future.');
    },

    /*
     db_update
     Update the database. The parameter of this function should be the object as below:

     {   name:<Name of the Context>
     conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records>
     dataset:<the array of the object {field:xx,value:xx}. each value will be set to the field.> }
     */
    db_update:function (args) {
        alert('The INTERMediator_DBAdapter of HTML local database isn\'t supported yet. It will be in a future.');
    },

    /*
     db_delete
     Delete the record. The parameter of this function should be the object as below:

     {   name:<Name of the Context>
     conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records, could be null>}
     */
    db_delete:function (args) {
        alert('The INTERMediator_DBAdapter of HTML local database isn\'t supported yet. It will be in a future.');
    },

    /*
     db_createRecord
     Create a record. The parameter of this function should be the object as below:

     {   name:<Name of the Context>
     dataset:<the array of the object {field:xx,value:xx}. Initial value for each field> }

     This function returns the value of the key field of the new record.
     */
    db_createRecord:function (args) {
        alert('The INTERMediator_DBAdapter of HTML local database isn\'t supported yet. It will be in a future.');
    }

};

