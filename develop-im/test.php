<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */  -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>INTER-Mediator - Sample - Form Style/FileMaker Server</title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<script src="Sample1.php"></script>
<script type="text/javascript">
</script>
</head>
<body onload="new INTERMediator()">
<table border="1">
	<tbody>
	<tr>
		<td>id</td>
		<td><input type="text" title="id"/></td>
	</tr>
	<tr>
		<td>title</td>
		<td><input type="text" title="title" value="" /></td>
	</tr>
	<tr>
		<td>address</td>
		<td><input type="text" title="address" value="" /></td>
	</tr>
	<tr>
		<td>mail</td>
		<td><input type="text" title="mail" value="" /></td>
	</tr>
	<tr>
		<td class="a b c d">category</td>
		<td>
			<select title="category">
				<option value="101">Family</option>
				<option value="102">ClassMate</option>
				<option value="103">Collegue</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>check</td>
		<td><input type="checkbox" title="check" value="1" /></td>
	</tr>
	<tr>
		<td>location</td>
		<td>
			<input type="radio" title="location" value="201" />Domestic
			<input type="radio" title="location" value="202" />International
			<input type="radio" title="location" value="203" />Neightbor
			<input type="radio" title="location" value="204" />Space
		</td>
	</tr>
	<tr>
		<td>memo</td>
		<td><textarea title="memo"></textarea></td>
	</tr>
	<tr>
		<td colspan="2">
			<table border="1">
			<thead>
			<tr>
				<th>person_id</th><th>datetime</th><th>summary</th>
				<th>important</th><th>way</th><th>kind</th><th>description</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td><div title="contact_to@person_id"></div></td>
				<td><input type="text" title="contact_to@datetime|contact_to@datetime@value"/></td>
				<td><input type="text" title="contact_to@summary"/></td>
				<td><input type="checkbox" title="contact_to@important" value="1"/></td>
				<td>
					<input type="radio" title="contact_to@way" value="301" />Direct
					<input type="radio" title="contact_to@way" value="302" />Phone
					<input type="radio" title="contact_to@way" value="303" />Another
				</td>
				<td>
					<select title="contact_to@kind">
						<option class="IM[contact_kind@id|contact_kind@name] contactkind" value="401">xxx</option>
					</select>
				</td>
				<td><textarea title="contact_to@description"></textarea></td>
			</tr>
			</tbody>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<ul>
				<li><hr><input type="text" title="history_to@startdate" /></li>
				<li><input type="text" title="history_to@enddate" /></li>
				<li><input type="text" title="history_to@description" /></li>
			</ul>
		</td>
	</tr>
	</tbody>
</table>

<p>The following table is out of the above master-detail relation.</p>
<table border="1">
	<thead>
	<tr>
		<th>郵便番号</th>
		<th>都道府県</th>
		<th>市区町村</th>
		<th>町域名</th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td><div title="postalcode@f3"></div></td>
		<td><div title="postalcode@f7"></div></td>
		<td><div title="postalcode@f8"></div></td>
		<td><div title="postalcode@f9"></div></td>
	</tr>
	</tbody>
</table>
</body>
</html>