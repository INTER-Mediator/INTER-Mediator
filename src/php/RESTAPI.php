<?php

namespace INTERMediator;

use INTERMediator\DB\Proxy;
use INTERMediator\DB\Proxy_ExtSupport;
use Symfony\Component\Yaml\Yaml;

/**
 * A placeholder entry point for REST API.
 * @param array $dataSource The data source definitions.
 * @param array|null $options The option definitions.
 * @param array $dbSpecification The database connection specifications.
 * @param bool $debug If true, enables debug mode.
 */
function IM_Dummy_Entry_RESTAPI($dataSource, $options, $dbSpecification, $debug = false): void
{
    global $globalDataSource, $globalOptions, $globalDBSpecs, $globalDebug;
    $globalDataSource = $dataSource;
    $globalOptions = $options;
    $globalDBSpecs = $dbSpecification;
    $globalDebug = $debug;
}

class RESTAPI
{
    use Proxy_ExtSupport;

    /**
     * @var array The data source definitions.
     */
    private array $dataSource;
    /**
     * @var array The option definitions.
     */
    private array $options;
    /**
     * @var array The database connection specifications.
     */
    private array $dbSpecification;

    /**
     * @var bool If true, shows API information.
     */
    private bool $isInformation;
    /**
     * @var string|null The name of the target context.
     */
    private string|null $targetContextName = null;
    /**
     * @var string|null The key value of the target record.
     */
    private string|null $targetKeyValue = null;

    /**
     * @var string|null The path to the definition file.
     */
    private string|null $pathToDefFile = null;

    /**
     * RESTAPI constructor.
     * @param string $path The path to the definition file.
     */
    public function __construct(string $path)
    {
        $this->pathToDefFile = $path;
        $this->isInformation = false;
        $pathComps = explode('.', $path);
        $extension = strtolower(end($pathComps));
        if (!$extension) {
            $this->errorAndExit("The path to definition file is invalid.");
        } else if ($extension == "php") {
            $this->parseFromPHPFile($path);
        } else if ($extension == "yaml" || $extension == "yml") {
            $this->parseFromYAMLFile($path);
        }
        if (!isset($_SERVER['PATH_INFO'])) { // Accessing ROOT of API
            $this->apiInformation();
            $this->isInformation = true;
        } else { // True API calling
            $pathInfoArray = explode("/", $_SERVER['PATH_INFO']);
            $this->targetContextName = $pathInfoArray[1] ?? "";
            $this->targetKeyValue = $pathInfoArray[2] ?? "";
        }
    }

    /**
     * Outputs an error message and terminates the script.
     * @param string $message The error message.
     */
    private function errorAndExit(string $message)
    {
        header("Content-type: application/json; charset=UTF-8");
        http_response_code(400);
        $errorResult = [
            "message" => $message,
            "date-time" => IMUtil::currentDTString(),
            "definition-file" => $this->pathToDefFile
        ];
        echo json_encode($errorResult);
        exit;
    }

    /**
     * Parses a PHP definition file.
     * @param string $path The path to the PHP definition file.
     */
    private function parseFromPHPFile(string $path): void
    {
        global $globalDataSource, $globalOptions, $globalDBSpecs, $globalDebug;

        /**
         * Replaces the INTER-Mediator inclusion statement.
         * @param string|null $src The source code.
         * @param string $validStatement The valid inclusion statement.
         * @return string|null The modified source code.
         */
        function changeIncludeIMPath($src, $validStatement)
        {
            $includeFunctions = array('require_once', 'include_once', 'require', 'include');
            foreach ($includeFunctions as $targetFunction) {
                $pattern = '/' . $targetFunction . '\\(.+INTER-Mediator.php.+\\);/';
                if (!is_null($src) && preg_match($pattern, $src)) {
                    return preg_replace($pattern, $validStatement, $src);
                }
            }
        }

        $fileContent = file_get_contents($path);
        if ($fileContent === false) {
            $this->errorAndExit("The path to definition file is invalid.");
        }
        $IMRoot = IMUtil::pathToINTERMediator();
        $convert = str_replace("<?php", "",
            str_replace("?>", "",
                str_replace("IM_Entry", "\\INTERMediator\\IM_Dummy_Entry_RESTAPI",
                    changeIncludeIMPath(
                        $fileContent,
                        "require_once('$IMRoot/INTER-Mediator.php');"
                    ) ?? "")));
        eval($convert);
        $this->dataSource = $globalDataSource;
        $this->options = $globalOptions;
        $this->dbSpecification = $globalDBSpecs;
    }

    /**
     * Parses a YAML definition file.
     * @param string $path The path to the YAML definition file.
     */
    private function parseFromYAMLFile(string $path): void
    {
        $parsed = Yaml::parse(file_get_contents($path));
        $this->dataSource = $parsed["contexts"];
        $this->options = $parsed["options"];
        $this->dbSpecification = $parsed["connection"];
    }

    /**
     * Processes the API request.
     * @throws Exception
     */
    public function processing(): void
    {
        if ($this->isInformation) { // In case of no parameter for api file accessing.
            return; // Just do nothing for information mode
        }
        // The content-type of the header has to be a JSON.
        if (!isset($_SERVER['HTTP_CONTENT_TYPE']) || $_SERVER['HTTP_CONTENT_TYPE'] != "application/json") {
            $this->errorAndExit("The Content-Type header has to be 'application/json'.");
        }
        // Get context definition for the target context.
        $targetContextDef = null;
        $n = 0;
        foreach ($this->dataSource as $contextDef) {
            if ($contextDef['name'] === $this->targetContextName) {
                $targetContextDef = $contextDef;
            }
            if (isset($contextDef['records'])) {
                $this->dataSource[$n]['records'] = 10000000; // Mostly ignoring 'records' keyed value.
            }
            $n += 1;
        }
        if (!$targetContextDef) {
            $this->errorAndExit("The target name of context doesn't exist.");
        }
        if (!isset($targetContextDef['key'])) {
            $this->errorAndExit("The 'key' value is required on the context definition.");
        }
        // Checking just existing "authetication" keyed value
        if (isset($targetContextDef["authentication"]) or isset($this->options["authentication"])) {
            // Authentication with accessToken field of authuser table.
            $token = substr($_SERVER['HTTP_AUTHORIZATION'] ?? "", 7);
            $authResult = $this->dbRead("authuser", ["accessToken" => $token]);
            $isAuthSucceed = (count($authResult) === 1);
            if (!$isAuthSucceed) {
                $this->errorAndExit("Credential is not valid.");
            }
        }
        // Initialize data accessing.
        $this->dbInit($this->dataSource, $this->options, $this->dbSpecification, false);
        $query = [];
        if ($this->targetKeyValue) {
            $query[] = ["field" => $targetContextDef['key'], "operator" => "=", "value" => $this->targetKeyValue];
        }
        $sign = "_operator";
        foreach ($_GET as $key => $value) {
            if (substr($key, -strlen($sign)) !== $sign) { // The key doesn't end with _operator.
                $query[] = ["field" => $key, "operator" => $_GET[$key . $sign] ?? "=", "value" => $value];
            }
        }
        $result = null;
        try {
            $bodyData = json_decode(file_get_contents('php://input'), true);
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $result = $this->dbRead($this->targetContextName, $query);
                    break;
                case 'POST':
                    $result = $this->dbCreate($this->targetContextName, $bodyData);
                    break;
                case 'DELETE':
                    if (!$query) {
                        $this->errorAndExit("The delete operation requires primary key or query.");
                    }
                    $this->dbDelete($this->targetContextName, $query);

                    var_export($this->getExtProxy()->logger->getErrorMessages());
                    var_export($this->getExtProxy()->logger->getDebugMessages());

                    $result = true;
                    break;
                case 'PUT':
                    if (!$bodyData) {
                        $this->errorAndExit("The update operation requires data in request body.");
                    }
                    $result = $this->dbUpdate($this->targetContextName, $query, $bodyData);
                    break;
            }
        } catch (Exception $e) {
            $this->errorAndExit($e->getMessage());
        }
        http_response_code(200);
        header("Content-type: application/json; charset=UTF-8");
        echo json_encode($result);
    }

    /**
     * Gets information about a context.
     * @param array $contextDef The context definition.
     * @return array An array containing context information.
     */
    private function contextInfo(array $contextDef)
    {
        $name = $contextDef["name"];
        $readEntity = $contextDef["view"] ?? $contextDef["name"] ?? false;
        $updateEntity = $contextDef["table"] ?? $contextDef["name"] ?? false;
        $auth = isset($contextDef["authentication"]);
        $cantWrite = !isset($contextDef["aggregation-select"]);

        $proxy = new Proxy(true);
        $proxy->initialize($this->dataSource, $this->options, $this->dbSpecification, 0);
        $readCount = count($proxy->dbClass->handler->getTableInfo($readEntity)) > 0;
        $readResult = $readCount || !$cantWrite;
        $updateCount = count($proxy->dbClass->handler->getTableInfo($updateEntity)) > 0;
        $updateResult = $cantWrite && $updateCount;
        return [$name, $auth, $readResult, $updateResult, $updateResult, $updateResult];
    }

    /**
     * Generates and outputs API information as an HTML page.
     */
    private function apiInformation(): void
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url = "https://";
        } else {
            $url = "http://";
        }
        $url .= $_SERVER['SERVER_NAME'];
        if ($_SERVER['SERVER_PORT'] != 443 && $_SERVER['SERVER_PORT'] != 80) {

            $url .= ":" . $_SERVER['SERVER_PORT'];
        }
        $url .= $_SERVER['SCRIPT_NAME'];

        $pathToCSS = IMUtil::pathToINTERMediator() . "/themes/default/css/style.css";
        $pathToScript = $_SERVER["SCRIPT_FILENAME"];
        $path = IMUtil::relativePath($pathToScript, $pathToCSS);

        echo "<html><head><title>INTER-Mediator API Specifications</title>";
        echo "<link rel='stylesheet' href='$path' /></head><body>";
        echo "<h1>API Information</h1>";
        echo "<div>API URL: $url/&lt;context-name&gt;[/&lt;primary-key-value&gt;]</div>";
        echo "<ul>";
        echo "<li>The context-name parameter is mandatory.</li>";
        echo "<li>The primary-key-value parameter is optional. This has to be the value of the \"key\" entry field.</li>";
        echo "<li>The Content-Type header has to be \"application/json\" for all operations.</li>";
        echo "<li>The body part is used just create/update operations for specifying the field data. Read/delete operations ignore the body part.</li>";
        echo "</ul>";

        echo "<h1>Available Contexts</h1><table class='context-table'>";
        echo "<tr><th>Context Name</th><th>Authentication</th><th>Read</th><th>Create</th><th>Update</th><th>Delete</th></tr>";
        foreach ($this->dataSource as $contextDef) {
            [$contextName, $auth, $read, $create, $update, $delete] = $this->contextInfo($contextDef);
            $authMark = ($auth || isset($this->options["authentication"])) ? "Required" : "-";
            $readMark = $read ? "OK" : "-";
            $createMark = $create ? "OK" : "-";
            $updateMark = $update ? "OK" : "-";
            $deleteMark = $delete ? "OK" : "-";
            echo "<tr><th>$contextName</th><td>$authMark</td><td>$readMark</td><td>$createMark</td><td>$updateMark</td><td>$deleteMark</td></tr>";
        }
        echo "</table>";
        echo <<<EOL
<h1>Examples</h1>
<h2>Read Table from Database</h2>
<table>
<tr><th>Summary</th><td>Getting all records in context.</td></tr>
<tr><th>URL</th><td>$url/person</td></tr>
<tr><th>Method</th><td>GET</td></tr>
<tr><th>Result</th><td>[{"id":1,"name":"Updated","address":"Saitama, Japan","mail":"msyk@msyk.net","category":null,"checking":null,"location":null,"memo":null},{"id":2,"name":"Updated","address":"Tokyo, Japan","mail":"msyk@msyk.net","category":null,"checking":null,"location":null,"memo":null},{"id":3,"name":"Updated2","address":"Osaka, Japan","mail":"msyk@msyk.net","category":null,"checking":null,"location":null,"memo":null}]</td></tr>
</table>
<table>
<tr><th>Summary</th><td>Getting a specific record in context.</td></tr>
<tr><th>URL</th><td>$url/person/3</td></tr>
<tr><th>Method</th><td>GET</td></tr>
<tr><th>Result</th><td>[{"id":3,"name":"Updated2","address":"Osaka, Japan","mail":"msyk@msyk.net","category":null,"checking":null,"location":null,"memo":null}]</td></tr>
</table>
<table>
<tr><th>Summary</th><td>Getting a specific record in context with a parameter.</td></tr>
<tr><th>URL</th><td>$url/person/?id=3</td></tr>
<tr><th>Method</th><td>GET</td></tr>
<tr><th>Result</th><td>[{"id":3,"name":"Updated2","address":"Osaka, Japan","mail":"msyk@msyk.net","category":null,"checking":null,"location":null,"memo":null}]</td></tr>
</table>
<table>
<tr><th>Summary</th><td>Getting a specific record in context with a parameter.</td></tr>
<tr><th>URL</th><td>$url/person/?id=2&id_operator=%3E%3D</td></tr>
<tr><th>Method</th><td>GET</td></tr>
<tr><th>Result</th><td>[{"id":2,"name":"Updated","address":"Tokyo, Japan","mail":"msyk@msyk.net","category":null,"checking":null,"location":null,"memo":null},{"id":3,"name":"Updated2","address":"Osaka, Japan","mail":"msyk@msyk.net","category":null,"checking":null,"location":null,"memo":null}]</td></tr>
<tr><th>Comment</th><td>
The query parameters of the URL are basically in the field-name=field-value format, and the operator is equal (field=value).
If you want to specify the operator, the key should be "field-name_operator" as this example.
</td></tr>
</table>
<h2>Create Record in Table from Database</h2>
<table>
<tr><th>Summary</th><td>Creating a record.</td></tr>
<tr><th>URL</th><td>$url/person</td></tr>
<tr><th>Method</th><td>POST</td></tr>
<tr><th>Body</th><td>{"name": "Created", "address": "Anyplace"}</td></tr>
<tr><th>Result</th><td>[{"id":7,"name":"Created","address":"Anyplace","mail":null,"category":null,"checking":null,"location":null,"memo":null}]</td></tr>
<tr><th>Comment</th><td>The create operations return the created record, and you can get the primary key value from the result.</td></tr>
</table>
<h2>Update Record in Table from Database</h2>
<table>
<tr><th>Summary</th><td>Updating a record.</td></tr>
<tr><th>URL</th><td>$url/person/7</td></tr>
<tr><th>Method</th><td>PUT</td></tr>
<tr><th>Body</th><td>{"name": "Test", "address": "TestTest"}</td></tr>
<tr><th>Result</th><td>[{"id":7,"name":"Test","address":"TestTest","mail":null,"category":null,"checking":null,"location":null,"memo":null}]</td></tr>
<tr><th>Comment</th><td>The last component of the URL is the "id" field value to edit.</td></tr>
</table>
<h2>Delete Record in Table from Database</h2>
<table>
<tr><th>Summary</th><td>Deleting a record.</td></tr>
<tr><th>URL</th><td>$url/person/7</td></tr>
<tr><th>Method</th><td>DELETE</td></tr>
<tr><th>Result</th><td>true</td></tr>
<tr><th>Comment</th><td>The last component of the URL is the "id" field value to delete.</td></tr>
</table>
<h2>Authentication with Operations</h2>
<table>
<tr><th>Summary</th><td>Reading records with authentication.</td></tr>
<tr><th>URL</th><td>$url/person</td></tr>
<tr><th>Method</th><td>GET</td></tr>
<tr><th>Headers</th><td>Content-Type: application/json<br>
Authorization: Bearer f9b73706b337feee318b3527a464f39108016e9facab848f42e37426594ebafe</td></tr>
<tr><th>Result</th><td>[...some records....]</td></tr>
<tr><th>Comment</th><td>
If the context definition requires authentication, the accessToken field is checked with the value of the Authorization header.
If a record matches, it is considered a successful authentication.
The authuser table is the default user table of INTER-Mediator. Some documents describe the details of authentication.
</td></tr>
</table>
<p>
To successfully authenticate on a web API using INTER-Mediator, the 'authuser' table must have the accessToken field.
The field value can be any string, but the following example uses SHA-256 of a random string.
On the API call, the Authorization header must be the value of the accessToken field with the 'Bearer' prefix.
</p>
<div style="padding: 12px 6px"><pre><code>mysql> select id,username,accessToken from authuser;
+----+----------+------------------------------------------------------------------+
| id | username | accessToken                                                      |
+----+----------+------------------------------------------------------------------+
|  1 | user1    | b10e7da88d2d4b624604efda92730cf61367f41e3f81b34e53194105e99c7dbd |
|  2 | user2    | f9b73706b337feee318b3527a464f39108016e9facab848f42e37426594ebafe |
|  3 | user3    | 7b216afd67d3e4b153520ba24961421d014b2bb7d1a88e70047ca8fd9f9211bc |
|  4 | user4    | c972efb826a8a343a8e270a3325131ff069ecdb8c870b856a66850da19942c7a |
|  5 | user5    | b7ff8c95ca93a392879d09a7aabcca00bc92ec391678d7e7c0c751ce31ae1192 |
|  6 | mig2m    | e9c18ff869b3490c735a4b8d9c497e9abb138099105b4c0aaa46919af7980250 |
|  7 | mig2     | 0e9ab52d44807dcaf42b80372f0d45ee8c40a5e9d1027c6531bd8b11f71e1c38 |
+----+----------+------------------------------------------------------------------+
</code></pre></div>
<table>
<tr><th>Summary</th><td>Reading records without authentication.</td></tr>
<tr><th>URL</th><td>$url/person</td></tr>
<tr><th>Method</th><td>GET</td></tr>
<tr><th>Headers</th><td>Content-Type: application/json</td></tr>
<tr><th>Result</th><td>{"message":"Credential is not valid.","date-time":"2025-09-13 03:30:19","definition-file":"..\/Sample_Auth\/MySQL_definitions.php"}</td></tr>
<tr><th>Comment</th><td>
In case of an error, a message is returned with the date, time, and the definition file used for this API.
</td></tr>
</table>
</body>
</html>
EOL;

    }
}