# Sample prompt for Windsurf

2024-12-31 Masayuki Nii (nii@msyk.net)

This is a prompt for creating an INTER-Mediator application from scratch using Windsurf. Provide the string after the "Prompt Example" section below, and try copying and executing it as is. It seems best to input it with the markup intact. Of course, you can also modify the prompt itself as needed. Currently, it will create an SQLite database and automatically generate a user interface for list and detail pages.

# Prompt Example
Please create a web application for an address book using INTER-Mediator. Please set up a search box in the list view. For information about INTER-Mediator, refer to the following.

---
# Database Schema Definition
- Use SQLite as the database.
- Define a database schema appropriate for the purpose of the application and apply it to the database.
- Do not add NOT NULL constraints to schema fields.

# Installing INTER-Mediator
- INTER-Mediator can be installed via Composer. The identifier is `inter-mediator/inter-mediator`. Specify the version as "dev-master".
- For Composer installation, you need to allow the following plugins:
  - `mouf/nodejs-installer`
  - `simplesamlphp/composer-module-installer`
- After installation, move to vendor/inter-mediator/inter-mediator and run the `npm install` command.
- Then, run the `vendor/inter-mediator/inter-mediator/dist-docs/generateminifyjshere.sh` script.

# Post-Installation Tasks for INTER-Mediator
- Create a lib directory at the project root.
- Copy the file vendor/inter-mediator/inter-mediator/params.php to the lib directory.
- Edit the contents of the copied lib/params.php file as follows:
  - Assign 'PDO' to the $dbClass variable.
  - Specify the username for database connection in the $dbUser variable.
  - Specify the password for database connection in the $dbPassword variable.
  - Specify the connection string required for PDO in the $dbDSN variable.
  - Assign an empty array to the $dbOption variable.

# Creating the Definition File
- Create a file named deffile.php at the root.
- As a PHP program, require the file `vendor/inter-mediator/inter-mediator/INTER-Mediator.php` using the require_once function.
- Then, call the IM_Entry function.
  - The first argument of the IM_Entry function is an array. The elements of the array are associative arrays. Prepare two associative arrays: one for the list view and one for the detail view.
  - In each associative array, the view and table keys should both be set to the main table name of the created database. For the name key, append "_list" for the list associative array and "_detail" for the detail associative array.
  - For the associative array with _list, specify the string "master-hide" for the navi-control key.
  - In the associative array with _list, set the paging key to true.
  - In the associative array with _list, specify the string "insert-confirm delete-confirm" for the repeat-control key.
  - In the associative array with _list, specify the main table's key field name for the key key.
  - For the associative array with _detail, specify the string "detail-update" for the navi-control key.
  - In the associative array with _detail, set the records key to 1.
  - In the associative array with _detail, specify the main table's key field name for the key key.
  - Set the 2nd and 3rd arguments of the IM_Entry function to null. Set the 4th argument to 2.

# Creating the Page File
- Create a file named app.html at the root.
- Insert HTML, HEAD, and BODY tags according to the basic structure of an HTML file.
- Set the SCRIPT tag to load deffile.php.
- In the BODY tag, create a DIV element with the id attribute set to "IM_NAVIGATOR".
- In the BODY tag, create a table. This table will be called the "list table".
  - In the list table, include all fields of the created table in one row.
  - For each row in the list table, insert empty TD tags at the beginning and end.
  - In the list table, make the field contents non-editable by displaying them inside SPAN tags within the TD tags.
  - In the SPAN tags inside the TD cells of the list table, set the data-im attribute as follows: the value is the list context name specified in the definition file, followed by "@", and then the field name.
  - No attributes are required for the TR tags in the list table.
- In the BODY tag, create another table. This table will be called the "detail table".
  - In the detail table, display each field of the created table in one row.
  - In each row of the detail table, display the field name in a TH tag and the field content in a TD tag, making the TD editable using an INPUT tag.
  - In the INPUT tags in the detail table, set the data-im attribute as follows: the value is the detail context name specified in the definition file, followed by "@", and then the field name.
  - No attributes are required for the TR tags in the detail table.

# Creating the JS File
- Create a file named app.js at the root.
- The contents should be as follows:
```
INTERMediatorOnPage.doBeforeConstruct = function () {
  INTERMediatorLog.suppressDebugMessageOnPage = true
}
```
- In the page file, load this app.js file using the SCRIPT tag.

# Adding a Search Box to the List Page
- Retrieve and remember the context name for the list view from the definition file.
- There should be a table for the list view in the list page.
- In the THEAD of that table, create a new row with a TR tag. Create only one cell, but set the COLSPAN attribute to span the number of columns in the TBODY so that the cell covers the width of the table.
- In the cell, create a text field using an input tag. Set the placeholder to show a "🔍" symbol.
- Set the type attribute of this input tag to "text".
- Set the data-im attribute of this input tag as follows:
  - The value of this attribute should be as follows:
  - Start with "_@condition:", followed by the context name, then a colon (:)
  - Then, list all field names in the context, separated by commas, followed by another colon (:)
  - Finally, append "*match*"
