# Sample prompt for Windsurf

2024-12-31 Masayuki Nii (nii@msyk.net)

This is a prompt to create an INTER-Mediator application from scratch using Windsurf.
It assumes adding new functionality to a project created using another file, `prompt1.ja.md`.
Let's provide the string after "Prompt Example" below, and at that time, copy and paste the content after the prompt example as is to execute it.
It seems best to input it as markup. Of course, you can also modify the prompt itself if necessary.
Currently, it creates an SQLite database and automatically generates a user interface that displays a list and details page.

# Prompt Example
I want to add contact records as related information to the created application.

---
# Information Organization
- The page file created so far will be called the "existing page file".

# Database Schema Definition
- Add a new related table definition to the already created database schema.
- Add a field to the new table to record the primary key value of the original table.
- In other words, define a foreign key in the new table to create a one-to-many relationship with the original table.

# Definition File Creation
- To display and input related information, create a definition file with the extension .php in the root. Please set the file name appropriately.
- As a PHP program, load ```vendor/inter-mediator/inter-mediator/INTER-Mediator.php``` using the `require_once` function.
- After that, call the `IM_Entry` function.
  - The first argument of the `IM_Entry` function is an array. The elements of the array are associative arrays.
  - In the associative array, prepare two associative arrays: one for displaying the existing table and one for displaying the related table.
  - In both associative arrays, the `name`, `view`, and `table` keys are the same as the related table name.
  - In the associative array for displaying the related table, specify the value "true" for the `paging` key.
  - In the associative array for displaying the related table, specify the value "10" for the `records` key.
  - In the associative array for displaying the existing table, specify the key field name of the original table for the `key` key.
  - In the associative array for displaying the related table, specify the key field name of the related table for the `key` key.
  - In the associative array for displaying the related table, specify `true` for the `post-reconstruct` key.
  - Set the second and third arguments of the `IM_Entry` function to `null`. Set the fourth argument to `2`.

# Page File Creation
- The page file is added to the root. The file name is the same as the definition file, but with a .html extension.
- Insert `HTML`, `HEAD`, and `BODY` tags according to the basic structure of an HTML file.
- Set it to load the definition file with a `SCRIPT` tag.
- Inside the `BODY` tag, prepare an element with the `id` attribute "IM_NAVIGATOR" using a `DIV` tag.
- Inside the `BODY` tag, prepare a table with a `table` tag. This table will be called the "original table record display table".
  - In the original table record display table, all fields of the original table should be in one row.
  - In the original table record display table, the content of the fields should be displayed with a `SPAN` tag inside a `TD` tag so that the data cannot be edited.
  - The original table record display table constructs the `data-im` attribute using the context where the name field is the original table name specified in the definition file.
  - For the `SPAN` tag in the cell of the `TD` tag of the original table record display table, specify the `data-im` attribute, and as the target specification, specify a string that connects the value of the `name` key of the context for the list specified in the definition file, followed by "@", and then the field name.
  - No attributes are necessary for the `TR` tag of the related table's list table.
- Inside the `BODY` tag, prepare a table with a `table` tag. This table will be called the "related table list table".
  - In the related table list table, all fields of the related table should be in one row.
  - In the related table list table, the content of the fields should be displayed with a `SPAN` tag inside a `TD` tag so that the data cannot be edited.
  - For the `SPAN` tag in the cell of the `TD` tag of the related table list table, specify the `data-im` attribute using the associative array of the context file that uses the related table. As the target specification, specify a string that connects the value of the `name` key of the context for the related table specified in the definition file, followed by "@", and then the field name.
  - No attributes are necessary for the `TR` tag of the related table's list table.
- Inside the `BODY` tag, prepare another table with a `table` tag. This table will be called the "related table input table".
  - For the `tbody` tag of the related table input table, set the `data-im-control` attribute and its value to "post".
  - In the related table input table, display each field of the related table on a new line. Do not display the primary key and foreign key fields.
  - In one row of the related table input table, display the field name with a `TH` tag and the field content with a `TD` tag, but make the inside of the `TD` tag editable using an `INPUT` tag.
  - For the `INPUT` tag in the related table input table, specify the `data-im` attribute, and as the target specification, specify a string that connects the value of the `name` key of the context for the list specified in the definition file, followed by "@", and then the field name.
  - No attributes are necessary for the `TR` tag of the related table input table.
  - In the related table input table, finally insert a `TR` tag, and inside it, insert `TH` and `TD` tags. In the `TD` tag, add an "Input" button with a `BUTTON` tag. Specify the string "post" for the `data-im-control` attribute of this button.

# JS File Creation
- In the root, create a file with the same name as the definition file but with a .js extension. This file will be called the "script file".
- In the page file, load this app.js file using a `SCRIPT` tag.
- In the script file, assign an anonymous function to `INTERMediatorOnPage.doBeforeConstruct`. This function will be called the "startup execution function".
- Inside the startup execution function, set `INTERMediatorLog.suppressDebugMessageOnPage` to `true`.
- Inside the startup execution function, use the URL parameters. The parameters can be retrieved with `INTERMediatorOnPage.getURLParametersAsArray()`.
- Save the value of the `id` key of the parameters in a variable. The variable name will be `id_value`.
- If there is a value for the "id" key in the parameters, call `INTERMediator.clearCondition()` and then `INTERMediator.addCondition()`.
- For the argument of `clearCondition`, specify the string specified for the `name` key of the associative array of the original table in the definition file.
- For `addCondition`, specify two arguments. For the first argument, specify the string specified for the `name` key of the associative array of the original table in the definition file. For the next argument, specify a JavaScript object with the following content:
  - For the "field" key, specify the field name of the foreign key field when defined as the original table as a string.
  - For the "operator" key, specify an equal sign.
  - For the "value" key, specify the value of the global variable `id_value`.
- Furthermore, for the argument of `clearCondition`, specify the string specified for the `name` key of the associative array of the related table in the definition file.
- For `addCondition`, specify two arguments. For the first argument, specify the string specified for the `name` key of the associative array of the related table in the definition file. For the next argument, specify a JavaScript object with the following content:
  - For the "field" key, specify the field name of the foreign key field when defined as the related table as a string.
  - For the "operator" key, specify an equal sign.
  - For the "value" key, specify the value of the global variable `id_value`.
- Assign an array to `INTERMediator.additionalFieldValueOnNewRecord[context_name]`. In this code, "context_name" is the string specified for the `name` key of the associative array of the related table in the definition file.
  - The array to be assigned is an object where the element is an object, and that object has `field` key with the foreign key field name of the related table, `operation` with "=", and `value` with the value of the global variable `id_value`.

# Modification of the Existing Page File
- In the existing page file, add a button inside the first `TD` tag of the list table.
- The button name should be something that suggests the related table, such as "Contact".
- When the button is clicked, it transitions to the newly created page file. Prepare a function for that, and specify only "$" as the argument to the function.
- In the function called by clicking, assign a URL to `location.href`. The URL is the "newly created page file name" with "?id=argument" appended.
- For the `data-im` attribute of the button, add a target specification. Specify `id` for the second section and the string "$onclick" for the third section.
