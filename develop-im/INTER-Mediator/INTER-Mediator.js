/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

var isDebug = false;
var isEdited = false;
var fieldIdList = new Array();
var tableTemplates = new Array();
var deleteRecords = new Array();
var insertRecords = new Array();
var modifiedIds = new Array();
var addedRowIds = new Array();
var serial = 987001;
var myRequest = null;

function saveRecord()	{
	if ( myRequest != null)	{
		alert(getMessageString(109));
		return;
	}

	var valids = getvalidationParams();
	var elmStr = '';
	var postData = '';
	var tags = ['input', 'select', 'textarea'];
	var isValid = true;
	for( var j=0 ; j < tags.length ; j++ )	{
		var elements = document.getElementsByTagName( tags[j] );
		for ( var i=0 ; i < elements.length ; i++ )	{
			var nameAttr = elements[i].getAttribute('name');
			if ( nameAttr != null )	{
				var validChkName = nameAttr;
				var splitNameAttr = nameAttr.split( separator );
				if ( splitNameAttr.length > 2 )	{
					validChkName = splitNameAttr[0]+separator+splitNameAttr[1];
				}

				for( var k in valids )	{
					if ( valids[k]['field'] == validChkName )	{
						if 			( valids[k]['rule'] == 'require' )	{
							if ( elements[i].value == '' )	{
								var fName = (valids[k]['option'])?valids[k]['option']:validChkName;
								errorOut( getMessageString(110), fName );
								isValid = false;
							}
						} else if	( valids[k]['rule'] == 'mail' )	{
							if ( ! elements[i].value.match(/[\w\d_-]+@[\w\d_-]+\.[\w\d._-]+/) )	{
								var fName = (valids[k]['option'])?valids[k]['option']:validChkName;
								errorOut( getMessageString(111), fName );
								isValid = false;
							}
						}
					}
				}

				var isInclude = true;
				if( j == 0 && elements[i].getAttribute('type') == 'checkbox' ) {
					elmStr = elements[i].checked ? elements[i].value : '';
				} else if( j == 0 && elements[i].getAttribute('type') == 'radio' ) {
					isInclude = elements[i].checked;
					elmStr = elements[i].checked ? elements[i].getAttribute('value') : '';
				} else {
					elmStr = elements[i].value;
				}
				if (isInclude)	{
					postData += '&' + encodeURIComponent(nameAttr) 
								+ '=' + encodeURIComponent(elmStr);
				}
			}
		}
	}
	if ( ! isValid )	return;

	if ( typeof beforeSave == 'function' )	{
		if ( beforeSave() == false )	return;
	}

	var seq = 0;
	for(var aTable in deleteRecords)	{
		for(var i=0; i<deleteRecords[aTable].length; i++)	{
			postData += '&' + "__easypage__delete_table_" + seq 
						+ "=" + encodeURIComponent(aTable);
			postData += '&' + "__easypage__delete_key_" + seq 
						+ "=" + encodeURIComponent(deleteRecords[aTable][i]);
			seq++;
			if (isDebug) {	debugOut('Delete Table:',aTable,deleteRecords[aTable][i]); }
		}
	}

	var seq = 0;
	for(var aTable in insertRecords)	{
		for(var i=0; i<insertRecords[aTable].length; i++)	{
			postData += '&' + "__easypage__insert_table_" + seq 
						+ "=" + encodeURIComponent(aTable);
			postData += '&' + "__easypage__insert_id_" + seq + "=" 
						+ encodeURIComponent(insertRecords[aTable][i]);
			seq++;
			if (isDebug) {	debugOut('Insert Table:',aTable,insertRecords[aTable][i]); }
		}
	}
	if ( postData == '' )	{
		document.getElementById('__easypage_navigation_message').innerHTML = getMessageString(106);
		return;
	}
	postData = getDataSourceParams() + getOptionParams() + getDatabaseParams() + postData;
	myRequest = new XMLHttpRequest();
	myRequest.open("post", getSaveURL(), true, getAccessUser(), getAccessPassword() );
	myRequest.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
	myRequest.onreadystatechange = finishXMLHttpRequest;
	myRequest.send( postData. substr( 1 ));
}

function finishXMLHttpRequest( )	{
	if ( myRequest.readyState == 4 )	{
		var res = myRequest.responseXML;
		var str = childNodeValueNoError( res, 'message');
		if (str.length > 0)
			document.getElementById('__easypage_navigation_message').innerHTML = getMessageString(str);
		if ( res != null && res.getElementsByTagName( 'error' ) != null )	{
			var nodes = res.getElementsByTagName( 'error' );
			for( var i=0 ; i<nodes.length ; i++){
				var errorMsg = nodeValueNoError( nodes[i] );
				if ( errorMsg != '' )	errorOut(errorMsg);
			}
		}
		if ( res != null && res.getElementsByTagName( 'debug-message' ) != null )	{
			var nodes = res.getElementsByTagName( 'debug-message' );
			for( var i=0 ; i<nodes.length ; i++){
				var errorMsg = nodeValueNoError( nodes[i] );
				if ( errorMsg != '' )	debugOut(errorMsg);
			}
		}
		if ( res != null && res.getElementsByTagName( 'generated' ) != null )	{
			var nodes = res.getElementsByTagName( 'generated' );
			for( var i=0 ; i<nodes.length ; i++){
				var targetId = childNodeValueNoError( nodes[i], 'element-id');
				var targetVal = childNodeValueNoError( nodes[i], 'value');
				var target = document.getElementById(targetId);
				if ( target.tagName == 'DIV' )	{
					target.innerHTML = targetVal;
				} else {
					target.value = targetVal;
				}
				if( isDebug )	debugOut( 'Set the new generated id:',targetId,targetVal);
			}
		}
		deleteRecords = new Array();
		insertRecords = new Array();
		modifiedIds = new Array();
		myRequest = null;

		if ( typeof afterSaveComplete == 'function' )	{
			if ( afterSaveComplete() == false )	return;
		}

	} else {
		document.getElementById('__easypage_navigation_message').innerHTML 
			= getMessageString(104)+'readyState='+myRequest.readyState+"/"+myRequest.responseText;
	}
}

function childNodeValueNoError( node, tag )	{
	if (node == null)	return '';
	var str = '';
	var cNode = node.getElementsByTagName(tag);
	if ( ! navigator.appName.match(/Explorer/))	{
		for ( var i=0 ; i<cNode.length ; i++)	{
			str += cNode[i].textContent;
		}
	} else {
		for ( var i=0 ; i<cNode.length ; i++)	{
			for ( var j=0 ; j<cNode[i].childNodes.length ; j++)	{
				if ( cNode[i].childNodes[j].nodeValue != null )
					str += cNode[i].childNodes[j].nodeValue;
			}
		}
	}
	return str;
}
function nodeValueNoError( node )	{
	if (node == null)	return '';
	var str = '';
	if ( ! navigator.appName.match(/Explorer/))	{
		str = node.textContent;
	} else {
		for ( var i=0 ; i<node.childNodes.length ; i++)	{
			if ( node.childNodes[i].nodeValue != null )
				str += node.childNodes[i].nodeValue;
		}
	}
	return str;
}

function debugMode( bool ){
	isDebug = bool;
}

function modifiedField(id)	{
	for ( var i =0 ; i < modifiedIds.length ; i++ )	{
		if ( modifiedIds[i] == id )	{
			return;
		}
	}
	modifiedIds.push(id);

	if ( typeof afterFieldModified == 'function' )	{
		if ( afterFieldModified() == false )	return;
	}
}

function doAtTheFinishing()	{
	var modRecords = modifiedIds.length;
	for( var i in deleteRecords )	modRecords += deleteRecords[i].length;
	for( var i in insertRecords )	modRecords += insertRecords[i].length;
	if( modRecords != 0 )
		return getMessageString(105);
}


function doAtTheStarting(){
	fieldIdList = new Array();
	var idAttr;
	var tags = ['input', 'select', 'textarea', 'div', 'a', 'img'];
	for( var j=0 ; j < tags.length ; j++ )	{
		var elements = document.getElementsByTagName( tags[j] );
		for ( var i=0 ; i < elements.length ; i++ )	{
			var nameAttr = (j>2) ? elements[i].getAttribute('title') : elements[i].getAttribute('name');
			if ( nameAttr )	{
				if ( elements[i].getAttribute('id') != null && elements[i].getAttribute('id') != '' )	{
					idAttr =  elements[i].getAttribute('id');
				} else	{
					idAttr = new String(++serial);
					elements[i].setAttribute('id',idAttr);
				}
				fieldIdList[nameAttr] = idAttr;
				addEvent( elements[i], 'change', new Function('modifiedField('+idAttr+')'));
				addEvent( elements[i], 'keydown', new Function('modifiedField('+idAttr+')'));
				
				var sp = nameAttr.indexOf(separator);
				if( sp > 0 )	{
					var tbName = nameAttr.substr( 0, sp );
					if ( ! tableTemplates[tbName] )	{
						for( var target = elements[i]; target.tagName != 'TR' ; target = target.parentNode );
						tableTemplates[tbName] = {'parent':target.parentNode, 'template':target.cloneNode(true), 'editable':false};
						addedRowIds[tbName] = new Array();
						target.parentNode.removeChild( target );
						if (isDebug) debugOut("Recognized Repeat Table",tbName);
					}
					if ( j < 3 )	{
						tableTemplates[tbName]['editable'] = true;
					}	
				}
			}
		}
	}
	if (isDebug) {
		var str = 'fieldIdList = ';
		for( var i in fieldIdList)	str += '[' + i + ':' + fieldIdList[i] + '] ';
		debugOut(str);
	}
	initializeWithDBValues();
}

function deleteRecord()	{
	
	if ( typeof beforeDeleteRecord == 'function' )	{
		if ( beforeDeleteRecord() == false )	return;
	}

	if ( typeof afterDeleteRecord == 'function' )	{
		if ( afterDeleteRecord() == false )	return;
	}
}

function newRecord()	{
	if( modifiedIds.length != 0 )
		if ( ! confirm( getMessageString(105) ) )
			return;

	if ( typeof beforeNewRecord == 'function' )	{
		if ( beforeNewRecord() == false )	return;
	}

	for ( var attrName in fieldIdList )	{
		var target = document.getElementById(fieldIdList[ attrName ]);
		if ( target )	{
			if( target.tagName == 'DIV' )	{
				target.innerHTML = '';
			} else {
				target.value = '';
			}
		}
	}
	for ( var tbName in addedRowIds )	{
		for ( var i = 0 ; i < addedRowIds[tbName].length ; i++ ){
			var target = document.getElementById(addedRowIds[tbName][i]);
			if (target)	{
				target.parentNode.removeChild(target);
			}
		}
	}
	mainTableName = getMainTableName();
	insertRecords = new Array();
	insertRecords[mainTableName] = new Array( fieldIdList[getKeyFieldName(mainTableName)] );

	if ( typeof afterNewRecord == 'function' )	{
		if ( afterNewRecord() == false )	return;
	}
}

function checkKeyFieldMainTable( key )	{
	if ( ! fieldIdList[key] || document.getElementById(fieldIdList[key]).tagName == 'DIV' )	{
		var target = null;
		for( var i in fieldIdList )	{
			if ( i.indexOf(separator) < 0 )	{
				target = document.getElementById(fieldIdList[i]);
				if ( target != null )	break;
			}
		}
		if (target == null)	target = document.getElementsByTagName('BODY')[0];
		var elm = document.createElement('input');
		elm.setAttribute('type', 'hidden');
		elm.setAttribute('name', key);
		elm.setAttribute('id', 'easypage_main_table_key_field');
		target.parentNode.appendChild( elm );
		fieldIdList[key] = 'easypage_main_table_key_field';
		if (isDebug) debugOut("Add the key field:"+key+" to the main table.");
	}
}

function deleteLineFromRepeatTable( tableName, trId, keyId )	{
	if( idValue( keyId ) == '' )	{
		errorOut(getMessageString(108));
	}
	debugOut( 'deleteLineFromRepeatTable', tableName, trId, keyId, idValue( keyId ));

	if ( typeof beforeTableRowDelete == 'function' )	{
		if ( beforeTableRowDelete() == false )	return;
	}

	if ( ! deleteRecords[tableName] )
		deleteRecords[tableName] = new Array();
	deleteRecords[tableName].push( idValue( keyId ));
	var tr = document.getElementById(trId);
	tr.parentNode.removeChild(tr);

	if ( typeof afterTableRowDelete == 'function' )	{
		if ( afterTableRowDelete() == false )	return;
	}
}

function addLineToRepeatTable( tableName )	{
	if ( typeof beforeTableRowInsert == 'function' )	{
		if ( beforeTableRowInsert() == false )	return;
	}

	var data = new Array();
	data[tableName + separator + getForeignKeyFieldName(tableName)] 
		= fieldValue(getKeyFieldName(getMainTableName()));
	var keyFieldId = addToRepeat( tableName, data );
	if ( ! insertRecords[tableName] )
		insertRecords[tableName] = new Array();
	insertRecords[tableName].push( keyFieldId );

	if ( typeof afterTableRowInsert == 'function' )	{
		if ( afterTableRowInsert() == false )	return;
	}

	debugOut( 'Called addLineToRepeatTable:', getForeignKeyFieldName(tableName), 
					fieldValue(getKeyFieldName(getMainTableName())), keyFieldId);
}

function fieldValue(fName)	{
	var target = document.getElementById(fieldIdList[fName]);
	if ( ! target )	return '';
	if ( target.tagName == 'DIV')	return target.innerHTML;
	return target.value;
}

function idValue(id)	{
	var target = document.getElementById(id);
	if ( ! target )	return '';
	if ( target.tagName == 'DIV')	return target.innerHTML;
	return target.value;
}

function setClassAttributeToNode( node, className )	{
	if (node == null)	return ;
	if ( ! navigator.appName.match(/Explorer/))	{
		node.setAttribute( 'class', className );
	} else {
		node.setAttribute( 'className', className );
	}
}

function getClassAttributeFromNode( node )	{
	if (node == null)	return '';
	var str = '';
	if ( ! navigator.appName.match(/Explorer/))	{
		str = node.getAttribute( 'class' );
	} else {
		str = node.getAttribute( 'className' );
	}
	return str;
}

function addRepeatTableControl( tableName, setting )	{
	if (tableTemplates[tableName] )	{
		var tbody = tableTemplates[tableName]['parent'];
		var trLine = tableTemplates[tableName]['template'];
		
		if ( setting.match(/delete/) ){
			var td = document.createElement('TD');
			setClassAttributeToNode( td, 'easypage_table_control' );
			trLine.appendChild( td );
			var aElm = document.createElement('span');
			setClassAttributeToNode( aElm, 'easypage_table_control_delete' );
			aElm.appendChild( document.createTextNode(getMessageString(5)));
			td.appendChild( aElm );
		}
		
		if ( setting.match(/insert/) ){
			var tfoot = tbody.parentNode.createTFoot();
			tbody.parentNode.insertBefore( tfoot, tbody );
			var tr = document.createElement('TR');
			tfoot.appendChild( tr );
			td = document.createElement('TD');
			td.setAttribute( 'colSpan', trLine.getElementsByTagName('TD').length + 1 );
			td.setAttribute( 'align', 'right' );
			setClassAttributeToNode( td, 'easypage_table_control' );
			tr.appendChild( td );
			aElm = document.createElement('span');
			setClassAttributeToNode( aElm, 'easypage_table_control_insert' );
			addEvent( aElm, 'click', new Function("addLineToRepeatTable('"+tableName+"');") );
			aElm.appendChild( document.createTextNode(getMessageString(4)));
			td.appendChild( aElm );
		}
	} else {
		errorOut( 'The table-control option has irrelevant table name' );
	}
	if (isDebug) debugOut("Call function addRepeatTableControl: table="+tableName );
}
function checkKeyFieldRepeatTable( tableName, key, fkey )	{
	msg = '';
	if ( ! tableTemplates[tableName]['editable'] )	return;
	var tdTemplate = tableTemplates[tableName]['template'];
	if ( tdTemplate == null )	return;
	
	var keyFullName = tableName + separator + key;
	var fKeyFullName = tableName + separator + fkey;
	var divNodes = document.getElementsByTagName( 'DIV' );
	var isDivKey = false, isDivFKey = false;
	for ( var i = 0 ; i < divNodes.length ; i++ )	{
		var nameAttr = divNodes[i].getAttribute( 'title' );
		if ( nameAttr )	{
			if( nameAttr == keyFullName)	isDivKey = true;
			if( nameAttr == fKeyFullName)	isDivFKey = true;
		}
	}
	if ( key != '' && ( ! fieldIdList[keyFullName]  || isDivKey ) )	{
		var newIdAttr = 'easypage_repeat_table_key_field_' + tableName;
		var target = tableTemplates[tableName]['template'].getElementsByTagName('TD')[0];
		var elm = document.createElement('input');
		elm.setAttribute('type', 'hidden');
		elm.setAttribute('name', keyFullName);
		elm.setAttribute('id', newIdAttr);
		target.appendChild( elm );
		fieldIdList[keyFullName] = newIdAttr;
		msg += "/ Add the key field:"+key+" to table:"+tableName;
	}
	if ( fkey != '' && ( ! fieldIdList[fKeyFullName]  || isDivFKey ) )	{
		var newIdAttr = 'easypage_repeat_table_foreign_key_field_' + tableName;
		var target = tableTemplates[tableName]['template'].getElementsByTagName('TD')[0];
		var elm = document.createElement('input');
		elm.setAttribute('type', 'hidden');
		elm.setAttribute('name', fKeyFullName);
		elm.setAttribute('id', newIdAttr);
		target.appendChild( elm );
		fieldIdList[keyFullName] = newIdAttr;
		msg += "/ Add the forreign key field:"+key+" to table:"+tableName;
	}
	if (isDebug) debugOut("Call function checkKeyFieldRepeatTable: table="+tableName+", key="+key+", foreign key="+fkey + msg);
}

var n = 0;
function addToRepeat( table, data )	{
	var trrigers = getTrrigerParams();
	var keyFieldName = table + separator + getKeyFieldName(table);
	if( data[keyFieldName] == '' )	{
		errorOut(getMessageString(107));
	}
	var keyFieldId;
	var cloned = tableTemplates[table]['template'].cloneNode(true);
	cloned.setAttribute( 'id', (++serial));
	var trId = serial;
	var tags = ['input', 'select', 'textarea','div'];
	var postCheck = new Array();
	var checkValues = new Array();
	for( var j=0 ; j < tags.length ; j++ )	{
		var elements = cloned.getElementsByTagName( tags[j] );
		for ( var i=0 ; i < elements.length ; i++ )	{
			var nameAttr = (j>2) ? elements[i].getAttribute('title') : elements[i].getAttribute('name');
			if ( nameAttr )	{
				elements[i].setAttribute( (j>2)?'title':'name', nameAttr+separator+n);
				elements[i].setAttribute( 'id', (++serial));
				if ( nameAttr == keyFieldName && elements[i].tagName != 'DIV' )	keyFieldId = serial;
				if( data[nameAttr] )	{
					if ( elements[i].tagName == 'DIV' )	{
						elements[i].innerHTML = data[nameAttr];
					}
					else if ( elements[i].tagName == 'SELECT' )	{
						elements[i].value = data[nameAttr];
					}
					else if ( elements[i].tagName == 'INPUT' && elements[i].getAttribute('type') == 'checkbox' )	{
						elements[i].checked = (data[nameAttr]!='');
					}
					else if ( elements[i].tagName == 'INPUT' && elements[i].getAttribute('type') == 'radio' )	{
						checkValues[serial] = elements[i].value;
						if (elements[i].value == data[nameAttr])	{
							postCheck[serial] = true;
						} else {}
					}
					else if ( elements[i].tagName == 'TEXTAREA' )	{
						elements[i].value = data[nameAttr];
					}
					else if ( elements[i].tagName == 'A' )	{
						elements[i].setAttribute( 'href', data[nameAttr] );
					}
					else if ( elements[i].tagName == 'IMG' )	{
						elements[i].setAttribute( 'src', data[nameAttr] );
					}
					else	{
						elements[i].value = data[nameAttr];
					}
				}
				addEvent( elements[i], 'change', new Function('modifiedField('+serial+');'));
				addEvent( elements[i], 'keydown', new Function('modifiedField('+serial+');'));
				for ( var ix in trrigers )	{
					if ( trrigers[ix]['field'] == nameAttr )	{
						addEvent( elements[i], trrigers[ix]['event'], 
							new Function(trrigers[ix]['function']+'(this);'));
//						debugOut( 'addEvent', nameAttr, trrigers[ix]['function']);
					}
				}
//				debugOut( 'addToRepeat', nameAttr, data[nameAttr], keyFieldName);
			}
		}
	}
	tableTemplates[table]['parent'].appendChild(cloned);
	addedRowIds[table].push(trId);
	
	elements = cloned.getElementsByTagName( 'SPAN' );
	for ( var i=0 ; i < elements.length ; i++ )	{
		if ( getClassAttributeFromNode( elements[i] ).indexOf('easypage_table_control_delete') >= 0 )	{
			addEvent( elements[i], 'click', 
				new Function("deleteLineFromRepeatTable('" + table + "','" + trId + "','" + keyFieldId + "')"));
		}
	}

	for( var e in postCheck )	{
		document.getElementById(e).checked = true;
	}
	for( var e in checkValues )	{
		document.getElementById(e).value = checkValues[e];
	}
	n++;
	return keyFieldId;
}

function setValue(field,value)	{
	var trrigers = getTrrigerParams();
	var elmId = fieldIdList[field];
	var target = document.getElementById(elmId);
	if (target == null)	return;
	if ( target.tagName == 'DIV' )
		target.innerHTML = value;
	else if ( target.tagName == 'SELECT' )	{
		target.value = value;
	}
	else if ( target.tagName == 'INPUT' && target.getAttribute('type') == 'checkbox' )	{
		target.checked = (value!='');
	}
	else if ( target.tagName == 'INPUT' && target.getAttribute('type') == 'radio' )	{
		for ( var i=elmId ; i>987000 ; i--)		{
			target = document.getElementById(i);
			if ( target.tagName != 'INPUT' || target.getAttribute('type') != 'radio')
				break;
			if( target.value == value )	{
				target.checked=true;
				break;
			}
		}
	}
	else if ( target.tagName == 'TEXTAREA' )	{
		target.value = value;
	}
	else if ( target.tagName == 'A' )	{
		target.setAttribute( 'href', data[nameAttr] );
	}
	else if ( target.tagName == 'IMG' )	{
		target.setAttribute( 'src', data[nameAttr] );
	}
	else	{
		target.value = value;
	}
	addEvent( target, 'change', new Function('modifiedField('+serial+');'));
	addEvent( target, 'keydown', new Function('modifiedField('+serial+');'));
	for ( var ix in trrigers )	{
		if ( trrigers[ix]['field'] == field )	{
			addEvent( target, trrigers[ix]['event'], 
							new Function(trrigers[ix]['function']+'(this);'));
		}
	}
}

function addEvent(node, evt, func)	{
	if ( node.addEventListener )	{
		node.addEventListener(evt,func,false);
	} else if ( node.attachEvent )	{
		node.attachEvent('on'+evt,func);
	}
}

function getNameAttributeForAllTags( node )	{
	var tag = node.tagName;
	if 			( tag == 'INPUT' )	{	return node.getAttribute('name');	}
	else if	( tag == 'TEXTAREA' ){	return node.getAttribute('name');	}
	else if	( tag == 'SELECT' )	{	return node.getAttribute('name');	}
	else if	( tag == 'DIV' )		{	return node.getAttribute('title');	}
	else if	( tag == 'A' )		{	return node.getAttribute('title');	}
	else if	( tag == 'IMG' )		{	return node.getAttribute('title');	}
	return null;
}

function showNoRecordMessage()	{
	errorOut(getMessageString(101))
}

function getElementNodeByName( nameAttr, originNode )	{
	var field = getNameAttributeForAllTags( originNode );
	var comps = field.split( separator );
	var targetName = nameAttr;
	if ( comps.length > 2 )	{
		targetName = nameAttr+separator+comps[2];
	}
	var tags = ['input', 'select', 'textarea', 'div', 'a', 'img'];
	for( var j=0 ; j < tags.length ; j++ )	{
		var elements = document.getElementsByTagName( tags[j] );
		for ( var i=0 ; i < elements.length ; i++ )	{
			var nAttr = (j>2) ? elements[i].getAttribute('title') : elements[i].getAttribute('name');
			if ( nAttr )	{
				if ( targetName == nAttr )	{
					return elements[i];
				}
			}
		}
	}
	return null;
}

function getElementNodesByName( nameAttr )	{
	var nodes = new Array();
	var tags = ['input', 'select', 'textarea', 'div', 'a', 'img'];
	for( var j=0 ; j < tags.length ; j++ )	{
		var elements = document.getElementsByTagName( tags[j] );
		for ( var i=0 ; i < elements.length ; i++ )	{
			var nAttr = (j>2) ? elements[i].getAttribute('title') : elements[i].getAttribute('name');
			if ( nAttr )	{
				if ( nAttr.indexOf( nameAttr ) == 0 )	{
					nodes.push( elements[i] );
				}
			}
		}
	}
	return nodes;
}

function toNumber( str )	{
	var s = '';
	for ( var i = 0 ; i < str.length ; i++ )	{
		var c = str.charAt(i);
		if ( (c >= '0' && c<= '9') || c == '-' || c == '.' )		s += c;
	}
	return new Number(s);
}

function numberFormat( str )	{
	var s = new Array();
	var n = new Number( str );
	var sign = '';
	if ( n < 0 )	{
		sign = '-';
		n = -n;
	}
	var f = n - Math.floor(n);
	if ( f == 0 )		f = '';
	for( n = Math.floor(n); n > 0 ; n = Math.floor( n/1000 ))	{
		if ( n > 1000 )	{
			s.push(('000' + (n % 1000).toString()).substr(-3));
		} else {
			s.push(n);
		}
	}
	return sign + s.reverse().join(',') + f;
}

function appendCredit()	{
	var body = document.getElementsByTagName('body')[0];
	var cNode = document.createElement('div');
	body.appendChild( cNode );
	cNode.style.backgroundColor = '#F6F7FF';
	cNode.style.height = '2px';
	
	cNode = document.createElement('div');
	body.appendChild( cNode );
	cNode.style.backgroundColor = '#EBF1FF';
	cNode.style.height = '2px';
	
	cNode = document.createElement('div');
	body.appendChild( cNode );
	cNode.style.backgroundColor = '#E1EAFF';
	cNode.style.height = '2px';
	
	cNode = document.createElement('div');
	body.appendChild( cNode );
	cNode.setAttribute( 'align', 'right' );
	cNode.style.backgroundColor = '#D7E4FF';
	cNode.style.padding = '2px';
	var spNode = document.createElement('span');
	cNode.appendChild( spNode );
	cNode.style.color = '#666666';
	cNode.style.fontSize = '7pt';
	var aNode = document.createElement('a');
	aNode.appendChild( document.createTextNode( 'INTER-Mediator' ));
	aNode.setAttribute( 'href', 'http://msyk.net/im' );
	aNode.setAttribute( 'target', '_href' );
	spNode.appendChild( document.createTextNode( 'Generated by ' ) );
	spNode.appendChild( aNode );
	spNode.appendChild( document.createTextNode( ' Ver.@@@@2@@@@(@@@@1@@@@)' ) );
}

function errorOut(str, msg1, msg2, msg3 )	{
	if ( msg1 != null )	str = str.replace( '@1@', msg1 );
	if ( msg2 != null )	str = str.replace( '@2@', msg2 );
	if ( msg3 != null )	str = str.replace( '@3@', msg3 );

	var debugNode = document.getElementById('easypage_error_panel_4873643897897');
	if ( debugNode == null )	{
		debugNode = document.createElement('div');
		debugNode.setAttribute( 'id', 'easypage_error_panel_4873643897897' );
		debugNode.style.backgroundColor = '#FFDDDD';
		var title = document.createElement('h3');
		title.appendChild(document.createTextNode('Error Info from INTER-Mediator'));
		title.appendChild(document.createElement('hr'));
		debugNode.appendChild( title );
		var body = document.getElementsByTagName('body')[0];
		body.insertBefore( debugNode, body.firstChild );
	}
	debugNode.appendChild(document.createTextNode(str));
	debugNode.appendChild(document.createElement('hr'));
}

function debugOut(str)	{
	var debugNode = document.getElementById('easypage_debug_panel_4873643897897');
	if ( debugNode == null )	{
		debugNode = document.createElement('div');
		debugNode.setAttribute( 'id', 'easypage_debug_panel_4873643897897' );
		debugNode.style.backgroundColor = '#DDDDDD';
		var clearButton = document.createElement('button');
		clearButton.setAttribute( 'title','clear' );
		addEvent( clearButton, 'click', 
			function(){
				var target = document.getElementById('easypage_debug_panel_4873643897897');
				target.parentNode.removeChild(target);
		});
		var tNode = document.createTextNode('clear');
		clearButton.appendChild(tNode)
		var title = document.createElement('h3');
		title.appendChild(document.createTextNode('Debug Info from INTER-Mediator'));
		title.appendChild(clearButton);
		title.appendChild(document.createElement('hr'));
		debugNode.appendChild( title );
		var body = document.getElementsByTagName('body')[0];
		body.insertBefore( debugNode, body.firstChild );
	}
	var message = new Array();
	for ( var i = 0 ; i < debugOut.arguments.length ; i++ )	message.push( new String(debugOut.arguments[i]) );
	debugNode.appendChild(document.createTextNode(message.join(', ')));
	debugNode.appendChild(document.createElement('hr'));
}
