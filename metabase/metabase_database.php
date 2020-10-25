<?php
/*
 * metabase_database.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/metabase/metabase_database.php,v 1.88 2003/01/08 04:35:34 mlemos Exp $
 *
 */

define('METABASE_TYPE_TEXT', 0);
define('METABASE_TYPE_BOOLEAN', 1);
define('METABASE_TYPE_INTEGER', 2);
define('METABASE_TYPE_DECIMAL', 3);
define('METABASE_TYPE_FLOAT', 4);
define('METABASE_TYPE_DATE', 5);
define('METABASE_TYPE_TIME', 6);
define('METABASE_TYPE_TIMESTAMP', 7);
define('METABASE_TYPE_CLOB', 8);
define('METABASE_TYPE_BLOB', 9);

$metabase_registered_transactions_shutdown = 0;

$metabase_databases = [];

function MetabaseParseConnectionArguments($connection, &$arguments)
{
    $parameters = parse_url($connection);

    if (!isset($parameters['scheme'])) {
        return ('it was not specified the connection type argument');
    }

    $arguments['Type'] = $parameters['scheme'];

    if (isset($parameters['host'])) {
        $arguments['Host'] = urldecode($parameters['host']);
    }

    if (isset($parameters['user'])) {
        $arguments['User'] = urldecode($parameters['user']);
    }

    if (isset($parameters['pass'])) {
        $arguments['Password'] = urldecode($parameters['pass']);
    }

    if (isset($parameters['port'])) {
        $arguments['Options']['Port'] = $parameters['port'];
    }

    if (isset($parameters['path'])) {
        $arguments['Database'] = urldecode(mb_substr($parameters['path'], 1));
    }

    if (isset($parameters['query'])) {
        $options = explode('&', $parameters['query']);

        for ($option = 0, $optionMax = count($options); $option < $optionMax; $option++) {
            if ('integer' != gettype($equal = mb_strpos($options[$option], '='))) {
                return ($options[$option] . ' connection option argument does not specify a value');
            }

            $argument = urldecode(mb_substr($options[$option], 0, $equal));

            $value = urldecode(mb_substr($options[$option], $equal + 1));

            if ('integer' == gettype($slash = mb_strpos($argument, '/'))) {
                if ('Options' != mb_substr($argument, 0, $slash)) {
                    return ('it was not specified a valid conection option argument');
                }

                $arguments['Options'][mb_substr($argument, $slash + 1)] = $value;
            } else {
                $arguments[$argument] = $value;
            }
        }
    }

    return ('');
}

function MetabaseLoadClass($include, $include_path, $type)
{
    $separator = '';

    $directory_separator = (defined('DIRECTORY_SEPARATOR') ? DIRECTORY_SEPARATOR : '/');

    $length = mb_strlen($include_path);

    if ($length) {
        if ($include_path[$length - 1] != $directory_separator) {
            $separator = $directory_separator;
        }
    }

    if (file_exists($include_path . $separator . $include)) {
        include $include_path . $separator . $include;

        return ('');
    }

    if (function_exists('ini_get')
        && mb_strlen($php_include_paths = ini_get('include_path'))) {
        $paths = explode((defined('PHP_OS') && !strcmp(mb_substr(PHP_OS, 0, 3), 'WIN')) ? ';' : ':', $php_include_paths);

        for ($path = 0, $pathMax = count($paths); $path < $pathMax; $path++) {
            $php_include_path = $paths[$path];

            $length = mb_strlen($php_include_path);

            if ($length) {
                if ($php_include_path[$length - 1] != $directory_separator) {
                    $separator = $directory_separator;
                }
            }

            if (file_exists($php_include_path . $separator . $include)) {
                include $php_include_path . $separator . $include;

                return ('');
            }
        }
    }

    $directory = 0;

    if (0 == mb_strlen($include_path)
        || ($directory = @opendir($include_path))) {
        if ($directory) {
            closedir($directory);
        }

        return ("it was not specified an existing $type file ($include)" . (0 == mb_strlen($include_path) ? ' and no Metabase IncludePath option was specified in the setup call' : ''));
    }

    //	return("it was not specified a valid $type include path");
    return ("it was not specified a valid $type include path  - $include_path  ");    // claudia incluiu
}

function MetabaseSetupInterface(&$arguments, &$db)
{
    if (isset($arguments['Connection'])
        && mb_strlen($error = MetabaseParseConnectionArguments($arguments['Connection'], $arguments))) {
        return ($error);
    }

    if (isset($arguments['Type'])) {
        if ('integer' == gettype($dash = mb_strpos($arguments['Type'], '-'))) {
            $type = mb_substr($arguments['Type'], 0, $dash);

            $sub_type = mb_substr($arguments['Type'], $dash + 1);
        } else {
            $type = $arguments['Type'];

            $sub_type = '';
        }
    } else {
        $type = $sub_type = '';
    }

    $sub_include = $sub_included = '';

    switch ($type) {
        case 'ibase':
            $include = 'metabase_ibase.php';
            $class_name = 'metabase_ibase_class';
            $included = 'METABASE_IBASE_INCLUDED';
            break;
        case 'ifx':
            $include = 'metabase_ifx.php';
            $class_name = 'metabase_ifx_class';
            $included = 'METABASE_IFX_INCLUDED';
            break;
        case 'msql':
            $include = 'metabase_msql.php';
            $class_name = 'metabase_msql_class';
            $included = 'METABASE_MSQL_INCLUDED';
            break;
        case 'mssql':
            $include = 'metabase_mssql.php';
            $class_name = 'metabase_mssql_class';
            $included = 'METABASE_MSSQL_INCLUDED';
            break;
        case 'mysql':
            $include = 'metabase_mysql.php';
            $class_name = 'metabase_mysql_class';
            $included = 'METABASE_MYSQL_INCLUDED';
            break;
        case 'pgsql':
            $include = 'metabase_pgsql.php';
            $class_name = 'metabase_pgsql_class';
            $included = 'METABASE_PGSQL_INCLUDED';
            break;
        case 'odbc':
            $include = 'metabase_odbc.php';
            $class_name = 'metabase_odbc_class';
            $included = 'METABASE_ODBC_INCLUDED';
            switch ($sub_type) {
                case '':
                    break;
                case 'msaccess':
                    $sub_include = 'metabase_odbc_msaccess.php';
                    $class_name = 'metabase_odbc_msaccess_class';
                    $sub_included = 'METABASE_ODBC_MSACCESS_INCLUDED';
                    break;
                default:
                    return ("\"$sub_type\" is not a supported ODBC database sub type");
            }
            break;
        case 'oci':
            $include = 'metabase_oci.php';
            $class_name = 'metabase_oci_class';
            $included = 'METABASE_OCI_INCLUDED';
            break;
        case 'sqlite':
            $include = 'metabase_sqlite.php';
            $class_name = 'metabase_sqlite_class';
            $included = 'METABASE_SQLITE_INCLUDED';
            break;
        case '':
            $included = ($arguments['IncludedConstant'] ?? '');
            if (!isset($arguments['Include'])
                || !strcmp($include = $arguments['Include'], '')) {
                return (isset($arguments['Include']) ? 'it was not specified a valid database include file' : 'it was not specified a valid DBMS driver type');
            }

            $sub_included = ($arguments['SubIncludedConstant'] ?? '');
            if (!isset($arguments['SubInclude'])
                || !strcmp($sub_include = $arguments['SubInclude'], '')) {
                return (isset($arguments['SubInclude']) ? 'it was not specified a valid database sub-include file' : 'it was not specified a valid DBMS sub-driver type');
            }

            if (!isset($arguments['ClassName'])
                || !strcmp($class_name = $arguments['ClassName'], '')) {
                return ('it was not specified a valid database class name');
            }
            break;
        default:
            return ("\"$type\" is not a supported driver type");
    }

    $include_path = ($arguments['IncludePath'] ?? '');

    $length = mb_strlen($include_path);

    $directory_separator = (defined('DIRECTORY_SEPARATOR') ? DIRECTORY_SEPARATOR : '/');

    $separator = '';

    if ($length) {
        if ($include_path[$length - 1] != $directory_separator) {
            $separator = $directory_separator;
        }
    }

    if (mb_strlen($included)
        && !defined($included)) {
        $error = MetabaseLoadClass($include, $include_path, 'DBMS driver');

        if (mb_strlen($error)) {
            return ($error);
        }
    }

    if (mb_strlen($sub_included)
        && !defined($sub_included)) {
        $error = MetabaseLoadClass($sub_include, $include_path, 'DBMS sub driver');

        if (mb_strlen($error)) {
            return ($error);
        }
    }

    $db = new $class_name();

    $db->include_path = $include_path;

    if (isset($arguments['Host'])) {
        $db->host = $arguments['Host'];
    }

    if (isset($arguments['User'])) {
        $db->user = $arguments['User'];
    }

    if (isset($arguments['Password'])) {
        $db->password = $arguments['Password'];
    }

    if (isset($arguments['Persistent'])) {
        $db->persistent = $arguments['Persistent'];
    }

    if (isset($arguments['Debug'])) {
        $db->debug = $arguments['Debug'];
    }

    $db->decimal_places = ($arguments['DecimalPlaces'] ?? 2);

    $db->lob_buffer_length = ($arguments['LOBBufferLength'] ?? 8000);

    if (isset($arguments['LogLineBreak'])) {
        $db->log_line_break = $arguments['LogLineBreak'];
    }

    if (isset($arguments['Options'])) {
        $db->options = $arguments['Options'];
    }

    if (mb_strlen($error = $db->Setup())) {
        return ($error);
    }

    if (isset($arguments['Database'])) {
        $db->SetDatabase($arguments['Database']);
    }

    return ('');
}

function MetabaseSetupDatabaseObject($arguments, &$db)
{
    global $metabase_databases;

    $database = count($metabase_databases) + 1;

    if (strcmp($error = MetabaseSetupInterface($arguments, $db), '')) {
        unset($metabase_databases[$database]);
    } else {
        eval('$metabase_databases[$database]= &$db;');

        $db->database = $database;
    }

    return ($error);
}

function MetabaseCloseSetup($database)
{
    global $metabase_databases;

    $metabase_databases[$database]->CloseSetup();

    $metabase_databases[$database] = '';
}

function MetabaseNow()
{
    return (strftime('%Y-%m-%d %H:%M:%S'));
}

function MetabaseToday()
{
    return (strftime('%Y-%m-%d'));
}

function MetabaseTime()
{
    return (strftime('%H:%M:%S'));
}

function MetabaseShutdownTransactions()
{
    global $metabase_databases;

    for (reset($metabase_databases), $database = 0, $databaseMax = count($metabase_databases); $database < $databaseMax; next($metabase_databases), $database++) {
        $metabase_database = key($metabase_databases);

        if ($metabase_databases[$metabase_database]->in_transaction
            && MetabaseRollbackTransaction($metabase_database)) {
            MetabaseAutoCommitTransactions($metabase_database, 1);
        }
    }
}

function MetabaseDefaultDebugOutput($database, $message)
{
    global $metabase_databases;

    $metabase_databases[$database]->debug_output .= "$database $message" . $metabase_databases[$database]->log_line_break;
}

class metabase_database_class
{
    /* PUBLIC DATA */

    public $database = 0;

    public $host = '';

    public $user = '';

    public $password = '';

    public $options = [];

    public $supported = [];

    public $persistent = 1;

    public $database_name = '';

    public $warning = '';

    public $affected_rows = -1;

    public $auto_commit = 1;

    public $prepared_queries = [];

    public $decimal_places = 2;

    public $first_selected_row = 0;

    public $selected_row_limit = 0;

    public $lob_buffer_length = 8000;

    public $escape_quotes = '';

    public $log_line_break = "\n";

    /* PRIVATE DATA */

    public $lobs = [];

    public $clobs = [];

    public $blobs = [];

    public $last_error = '';

    public $in_transaction = 0;

    public $debug = '';

    public $debug_output = '';

    public $pass_debug_handle = 0;

    public $result_types = [];

    public $errorHandler = '';

    public $manager;

    public $include_path = '';

    public $manager_included_constant = '';

    public $manager_include = '';

    public $manager_sub_included_constant = '';

    public $manager_sub_include = '';

    public $manager_class_name = '';

    /* PRIVATE METHODS */

    public function EscapeText(&$text)
    {
        if (strcmp($this->escape_quotes, "'")) {
            $text = str_replace($this->escape_quotes, $this->escape_quotes . $this->escape_quotes, $text);
        }

        $text = str_replace("'", $this->escape_quotes . "'", $text);
    }

    /* PUBLIC METHODS */

    public function Close()
    {
    }

    public function CloseSetup()
    {
        if ($this->in_transaction
            && $this->RollbackTransaction()
            && $this->AutoCommitTransactions(1)) {
            $this->in_transaction = 0;
        }

        $this->Close();
    }

    public function Debug($message)
    {
        if (strcmp($function = $this->debug, '')) {
            if ($this->pass_debug_handle) {
                $function($this->database, $message);
            } else {
                $function($message);
            }
        }
    }

    public function DebugOutput()
    {
        return ($this->debug_output);
    }

    public function SetDatabase($name)
    {
        $previous_database_name = $this->database_name;

        $this->database_name = $name;

        return ($previous_database_name);
    }

    public function RegisterTransactionShutdown($auto_commit)
    {
        global $metabase_registered_transactions_shutdown;

        if (($this->in_transaction = !$auto_commit)
            && !$metabase_registered_transactions_shutdown) {
            register_shutdown_function('MetabaseShutdownTransactions');

            $metabase_registered_transactions_shutdown = 1;
        }

        return (1);
    }

    public function CaptureDebugOutput($capture)
    {
        $this->pass_debug_handle = $capture;

        $this->debug = ($capture ? 'MetabaseDefaultDebugOutput' : '');
    }

    public function SetError($scope, $message)
    {
        $this->last_error = $message;

        $this->Debug($scope . ': ' . $message);

        if (strcmp($function = $this->errorHandler, '')) {
            $error = [
                'Scope' => $scope,
                'Message' => $message,
            ];

            $function($this, $error);
        }

        return (0);
    }

    public function LoadExtension($scope, $extension, $included_constant, $include)
    {
        if (0 == mb_strlen($included_constant)
            || !defined($included_constant)) {
            $error = MetabaseLoadClass($include, $this->include_path, $extension);

            if (mb_strlen($error)) {
                return ($this->SetError($scope, $error));
            }
        }

        return (1);
    }

    public function LoadManager($scope)
    {
        if (isset($this->manager)) {
            return (1);
        }

        if (!$this->LoadExtension($scope, 'database manager', 'METABASE_MANAGER_DATABASE_INCLUDED', 'manager_database.php')) {
            return (0);
        }

        if (mb_strlen($this->manager_class_name)) {
            if (0 == mb_strlen($this->manager_include)) {
                return ($this->SetError($scope, 'it was not configured a valid database manager include file'));
            }

            if (!$this->LoadExtension($scope, 'database manager', $this->manager_included_constant, $this->manager_include)) {
                return (0);
            }

            if (mb_strlen($this->manager_sub_include)
                && !$this->LoadExtension($scope, 'database manager', $this->manager_sub_included_constant, $this->manager_sub_include)) {
                return (0);
            }

            $class_name = $this->manager_class_name;
        } else {
            $class_name = 'metabase_manager_database_class';
        }

        $this->manager = new $class_name();

        return (1);
    }

    public function CreateDatabase($database)
    {
        if (!$this->LoadManager('Create database')) {
            return (0);
        }

        return ($this->manager->CreateDatabase($this, $database));
    }

    public function DropDatabase($database)
    {
        if (!$this->LoadManager('Drop database')) {
            return (0);
        }

        return ($this->manager->DropDatabase($this, $database));
    }

    public function CreateTable($name, &$fields)
    {
        if (!$this->LoadManager('Create table')) {
            return (0);
        }

        return ($this->manager->CreateTable($this, $name, $fields));
    }

    public function DropTable($name)
    {
        if (!$this->LoadManager('Drop table')) {
            return (0);
        }

        return ($this->manager->DropTable($this, $name));
    }

    public function AlterTable($name, &$changes, $check)
    {
        if (!$this->LoadManager('Alter table')) {
            return (0);
        }

        return ($this->manager->AlterTable($this, $name, $changes, $check));
    }

    public function ListTables(&$tables)
    {
        if (!$this->LoadManager('List tables')) {
            return (0);
        }

        return ($this->manager->ListTables($this, $tables));
    }

    public function ListTableFields($table, &$fields)
    {
        if (!$this->LoadManager('List table fields')) {
            return (0);
        }

        return ($this->manager->ListTableFields($this, $table, $fields));
    }

    public function GetTableFieldDefinition($table, $field, &$definition)
    {
        if (!$this->LoadManager('Get table field definition')) {
            return (0);
        }

        return ($this->manager->GetTableFieldDefinition($this, $table, $field, $definition));
    }

    public function ListTableIndexes($table, &$indexes)
    {
        if (!$this->LoadManager('List table indexes')) {
            return (0);
        }

        return ($this->manager->ListTableIndexes($this, $table, $indexes));
    }

    public function GetTableIndexDefinition($table, $index, &$definition)
    {
        if (!$this->LoadManager('Get table index definition')) {
            return (0);
        }

        return ($this->manager->GetTableIndexDefinition($this, $table, $index, $definition));
    }

    public function ListSequences(&$sequences)
    {
        if (!$this->LoadManager('List sequences')) {
            return (0);
        }

        return ($this->manager->ListSequences($this, $sequences));
    }

    public function GetSequenceDefinition($sequence, &$definition)
    {
        if (!$this->LoadManager('Get sequence definition')) {
            return (0);
        }

        return ($this->manager->GetSequenceDefinition($this, $sequence, $definition));
    }

    public function CreateIndex($table, $name, &$definition)
    {
        if (!$this->LoadManager('Create index')) {
            return (0);
        }

        return ($this->manager->CreateIndex($this, $table, $name, $definition));
    }

    public function DropIndex($table, $name)
    {
        if (!$this->LoadManager('Drop index')) {
            return (0);
        }

        return ($this->manager->DropIndex($this, $table, $name));
    }

    public function CreateSequence($name, $start)
    {
        if (!$this->LoadManager('Create sequence')) {
            return (0);
        }

        return ($this->manager->CreateSequence($this, $name, $start));
    }

    public function DropSequence($name)
    {
        if (!$this->LoadManager('Drop sequence')) {
            return (0);
        }

        return ($this->manager->DropSequence($this, $name));
    }

    public function GetSequenceNextValue($name, &$value)
    {
        return ($this->SetError('Get sequence next value', 'getting sequence next value is not supported'));
    }

    public function GetSequenceCurrentValue($name, &$value)
    {
        if (!$this->LoadManager('Get sequence current value')) {
            return (0);
        }

        return ($this->manager->GetSequenceCurrentValue($this, $name, $value));
    }

    public function Query($query)
    {
        $this->Debug("Query: $query");

        return ($this->SetError('Query', 'database queries are not implemented'));
    }

    public function Replace($table, &$fields)
    {
        if (!$this->supported['Replace']) {
            return ($this->SetError('Replace', 'replace query is not supported'));
        }

        $count = count($fields);

        for ($keys = 0, $condition = $update = $insert = $values = '', reset($fields), $field = 0; $field < $count; next($fields), $field++) {
            $name = key($fields);

            if ($field > 0) {
                $update .= ',';

                $insert .= ',';

                $values .= ',';
            }

            $update .= $name;

            $insert .= $name;

            if (isset($fields[$name]['Null'])
                && $fields[$name]['Null']) {
                $value = 'NULL';
            } else {
                if (!isset($fields[$name]['Value'])) {
                    return ($this->SetError('Replace', "it was not specified a value for the $name field"));
                }

                switch ($fields[$name]['Type'] ?? 'text') {
                    case 'text':
                        $value = $this->GetTextFieldValue($fields[$name]['Value']);
                        break;
                    case 'boolean':
                        $value = $this->GetBooleanFieldValue($fields[$name]['Value']);
                        break;
                    case 'integer':
                        $value = (string)$fields[$name]['Value'];
                        break;
                    case 'decimal':
                        $value = $this->GetDecimalFieldValue($fields[$name]['Value']);
                        break;
                    case 'float':
                        $value = $this->GetFloatFieldValue($fields[$name]['Value']);
                        break;
                    case 'date':
                        $value = $this->GetDateFieldValue($fields[$name]['Value']);
                        break;
                    case 'time':
                        $value = $this->GetTimeFieldValue($fields[$name]['Value']);
                        break;
                    case 'timestamp':
                        $value = $this->GetTimestampFieldValue($fields[$name]['Value']);
                        break;
                    default:
                        return ($this->SetError('Replace', "it was not specified a supported type for the $name field"));
                }
            }

            $update .= '=' . $value;

            $values .= $value;

            if (isset($fields[$name]['Key'])
                && $fields[$name]['Key']) {
                if ('NULL' == $value) {
                    return ($this->SetError('Replace', 'key values may not be NULL'));
                }

                $condition .= ($keys ? ' AND ' : ' WHERE ') . $name . '=' . $value;

                $keys++;
            }
        }

        if (0 == $keys) {
            return ($this->SetError('Replace', 'it were not specified which fields are keys'));
        }

        if (!($in_transaction = $this->in_transaction)
            && !$this->AutoCommitTransactions(0)) {
            return (0);
        }

        if (($success = $this->QueryField("SELECT COUNT(*) FROM $table$condition", $affected_rows, 'integer'))) {
            switch ($affected_rows) {
                case 0:
                    $success = $this->Query("INSERT INTO $table ($insert) VALUES($values)");
                    $affected_rows = 1;
                    break;
                case 1:
                    $success = $this->Query("UPDATE $table SET $update$condition");
                    $affected_rows = $this->affected_rows * 2;
                    break;
                default:
                    $success = $this->SetError('Replace', 'replace keys are not unique');
                    break;
            }
        }

        if (!$in_transaction) {
            if ($success) {
                if (($success = ($this->CommitTransaction() && $this->AutoCommitTransactions(1)))
                    && isset($this->supported['AffectedRows'])) {
                    $this->affected_rows = $affected_rows;
                }
            } else {
                $this->RollbackTransaction();

                $this->AutoCommitTransactions(1);
            }
        }

        return ($success);
    }

    public function PrepareQuery($query)
    {
        $this->Debug("PrepareQuery: $query");

        $positions = [];

        for ($position = 0; $position < mb_strlen($query) && 'integer' == gettype($question = mb_strpos($query, '?', $position));) {
            if ('integer' == gettype($quote = mb_strpos($query, "'", $position))
                && $quote < $question) {
                if ('integer' != gettype($end_quote = mb_strpos($query, "'", $quote + 1))) {
                    return ($this->SetError('Prepare query', 'it was specified a query with an unterminated text string'));
                }

                switch ($this->escape_quotes) {
                    case '':
                    case "'":
                        $position = $end_quote + 1;
                        break;
                    default:
                        if ($end_quote == $quote + 1) {
                            $position = $end_quote + 1;
                        } else {
                            if ($query[$end_quote - 1] == $this->escape_quotes) {
                                $position = $end_quote;
                            } else {
                                $position = $end_quote + 1;
                            }
                        }
                        break;
                }
            } else {
                $positions[] = $question;

                $position = $question + 1;
            }
        }

        $this->prepared_queries[] = [
            'Query' => $query,
            'Positions' => $positions,
            'Values' => [],
            'Types' => [],
        ];

        $prepared_query = count($this->prepared_queries);

        if ($this->selected_row_limit > 0) {
            $this->prepared_queries[$prepared_query - 1]['First'] = $this->first_selected_row;

            $this->prepared_queries[$prepared_query - 1]['Limit'] = $this->selected_row_limit;
        }

        return ($prepared_query);
    }

    public function ValidatePreparedQuery($prepared_query)
    {
        if ($prepared_query < 1
            || $prepared_query > count($this->prepared_queries)) {
            return ($this->SetError('Validate prepared query', 'invalid prepared query'));
        }

        if ('array' != gettype($this->prepared_queries[$prepared_query - 1])) {
            return ($this->SetError('Validate prepared query', 'prepared query was already freed'));
        }

        return (1);
    }

    public function FreePreparedQuery($prepared_query)
    {
        if (!$this->ValidatePreparedQuery($prepared_query)) {
            return (0);
        }

        $this->prepared_queries[$prepared_query - 1] = '';

        return (1);
    }

    public function ExecutePreparedQuery($prepared_query, $query)
    {
        return ($this->Query($query));
    }

    public function ExecuteQuery($prepared_query)
    {
        if (!$this->ValidatePreparedQuery($prepared_query)) {
            return (0);
        }

        $index = $prepared_query - 1;

        for ($this->clobs[$prepared_query] = $this->blobs[$prepared_query] = [], $success = 1, $query = '', $last_position = $position = 0, $positionMax = count($this->prepared_queries[$index]['Positions']); $position < $positionMax; $position++) {
            if (!isset($this->prepared_queries[$index]['Values'][$position])) {
                return ($this->SetError('Execute query', 'it was not defined query argument ' . ($position + 1)));
            }

            $current_position = $this->prepared_queries[$index]['Positions'][$position];

            $query .= mb_substr($this->prepared_queries[$index]['Query'], $last_position, $current_position - $last_position);

            $value = $this->prepared_queries[$index]['Values'][$position];

            if ($this->prepared_queries[$index]['IsNULL'][$position]) {
                $query .= $value;
            } else {
                switch ($this->prepared_queries[$index]['Types'][$position]) {
                    case 'clob':
                        if (!($success = $this->GetCLOBFieldValue($prepared_query, $position + 1, $value, $this->clobs[$prepared_query][$position + 1]))) {
                            unset($this->clobs[$prepared_query][$position + 1]);

                            break;
                        }
                        $query .= $this->clobs[$prepared_query][$position + 1];
                        break;
                    case 'blob':
                        if (!($success = $this->GetBLOBFieldValue($prepared_query, $position + 1, $value, $this->blobs[$prepared_query][$position + 1]))) {
                            unset($this->blobs[$prepared_query][$position + 1]);

                            break;
                        }
                        $query .= $this->blobs[$prepared_query][$position + 1];
                        break;
                    default:
                        $query .= $value;
                        break;
                }
            }

            $last_position = $current_position + 1;
        }

        if ($success) {
            $query .= mb_substr($this->prepared_queries[$index]['Query'], $last_position);

            if ($this->selected_row_limit > 0) {
                $this->prepared_queries[$index]['First'] = $this->first_selected_row;

                $this->prepared_queries[$index]['Limit'] = $this->selected_row_limit;
            }

            if (isset($this->prepared_queries[$index]['Limit'])
                && $this->prepared_queries[$index]['Limit'] > 0) {
                $this->first_selected_row = $this->prepared_queries[$index]['First'];

                $this->selected_row_limit = $this->prepared_queries[$index]['Limit'];
            } else {
                $this->first_selected_row = $this->selected_row_limit = 0;
            }

            $success = $this->ExecutePreparedQuery($prepared_query, $query);
        }

        for (reset($this->clobs[$prepared_query]), $clob = 0, $clobMax = count($this->clobs[$prepared_query]); $clob < $clobMax; $clob++, next($this->clobs[$prepared_query])) {
            $this->FreeCLOBValue($prepared_query, key($this->clobs[$prepared_query]), $this->clobs[$prepared_query][key($this->clobs[$prepared_query])], $success);
        }

        unset($this->clobs[$prepared_query]);

        for (reset($this->blobs[$prepared_query]), $blob = 0, $blobMax = count($this->blobs[$prepared_query]); $blob < $blobMax; $blob++, next($this->blobs[$prepared_query])) {
            $this->FreeBLOBValue($prepared_query, key($this->blobs[$prepared_query]), $this->blobs[$prepared_query][key($this->blobs[$prepared_query])], $success);
        }

        unset($this->blobs[$prepared_query]);

        return ($success);
    }

    public function QuerySet($prepared_query, $parameter, $type, $value, $is_null = 0, $field = '')
    {
        if (!$this->ValidatePreparedQuery($prepared_query)) {
            return (0);
        }

        $index = $prepared_query - 1;

        if ($parameter < 1
            || $parameter > count($this->prepared_queries[$index]['Positions'])) {
            return ($this->SetError('Query set', 'it was not specified a valid argument number'));
        }

        $this->prepared_queries[$index]['Values'][$parameter - 1] = $value;

        $this->prepared_queries[$index]['Types'][$parameter - 1] = $type;

        $this->prepared_queries[$index]['Fields'][$parameter - 1] = $field;

        $this->prepared_queries[$index]['IsNULL'][$parameter - 1] = $is_null;

        return (1);
    }

    public function QuerySetNull($prepared_query, $parameter, $type)
    {
        return ($this->QuerySet($prepared_query, $parameter, $type, 'NULL', 1, ''));
    }

    public function QuerySetText($prepared_query, $parameter, $value)
    {
        return ($this->QuerySet($prepared_query, $parameter, 'text', $this->GetTextFieldValue($value)));
    }

    public function QuerySetCLOB($prepared_query, $parameter, $value, $field)
    {
        return ($this->QuerySet($prepared_query, $parameter, 'clob', $value, 0, $field));
    }

    public function QuerySetBLOB($prepared_query, $parameter, $value, $field)
    {
        return ($this->QuerySet($prepared_query, $parameter, 'blob', $value, 0, $field));
    }

    public function QuerySetInteger($prepared_query, $parameter, $value)
    {
        return ($this->QuerySet($prepared_query, $parameter, 'integer', $this->GetIntegerFieldValue($value)));
    }

    public function QuerySetBoolean($prepared_query, $parameter, $value)
    {
        return ($this->QuerySet($prepared_query, $parameter, 'boolean', $this->GetBooleanFieldValue($value)));
    }

    public function QuerySetDate($prepared_query, $parameter, $value)
    {
        return ($this->QuerySet($prepared_query, $parameter, 'date', $this->GetDateFieldValue($value)));
    }

    public function QuerySetTimestamp($prepared_query, $parameter, $value)
    {
        return ($this->QuerySet($prepared_query, $parameter, 'timestamp', $this->GetTimestampFieldValue($value)));
    }

    public function QuerySetTime($prepared_query, $parameter, $value)
    {
        return ($this->QuerySet($prepared_query, $parameter, 'time', $this->GetTimeFieldValue($value)));
    }

    public function QuerySetFloat($prepared_query, $parameter, $value)
    {
        return ($this->QuerySet($prepared_query, $parameter, 'float', $this->GetFloatFieldValue($value)));
    }

    public function QuerySetDecimal($prepared_query, $parameter, $value)
    {
        return ($this->QuerySet($prepared_query, $parameter, 'decimal', $this->GetDecimalFieldValue($value)));
    }

    public function AffectedRows(&$affected_rows)
    {
        if (-1 == $this->affected_rows) {
            return ($this->SetError('Affected rows', 'there was no previous valid query to determine the number of affected rows'));
        }

        $affected_rows = $this->affected_rows;

        return (1);
    }

    public function EndOfResult($result)
    {
        $this->SetError('End of result', 'end of result method not implemented');

        return (-1);
    }

    public function FetchResult($result, $row, $field)
    {
        $this->warning = 'fetch result method not implemented';

        return ('');
    }

    public function FetchLOBResult($result, $row, $field)
    {
        $lob = count($this->lobs) + 1;

        $this->lobs[$lob] = [
            'Result' => $result,
            'Row' => $row,
            'Field' => $field,
            'Position' => 0,
        ];

        $character_lob = [
            'Database' => $this->database,
            'Error' => '',
            'Type' => 'resultlob',
            'ResultLOB' => $lob,
        ];

        if (!MetabaseCreateLOB($character_lob, $clob)) {
            return ($this->SetError('Fetch LOB result', $character_lob['Error']));
        }

        return ($clob);
    }

    public function RetrieveLOB($lob)
    {
        if (!isset($this->lobs[$lob])) {
            return ($this->SetError('Fetch LOB result', 'it was not specified a valid lob'));
        }

        if (!isset($this->lobs[$lob]['Value'])) {
            $this->lobs[$lob]['Value'] = $this->FetchResult($this->lobs[$lob]['Result'], $this->lobs[$lob]['Row'], $this->lobs[$lob]['Field']);
        }

        return (1);
    }

    public function EndOfResultLOB($lob)
    {
        if (!$this->RetrieveLOB($lob)) {
            return (0);
        }

        return ($this->lobs[$lob]['Position'] >= mb_strlen($this->lobs[$lob]['Value']));
    }

    public function ReadResultLOB($lob, &$data, $length)
    {
        if (!$this->RetrieveLOB($lob)) {
            return (-1);
        }

        $length = min($length, mb_strlen($this->lobs[$lob]['Value']) - $this->lobs[$lob]['Position']);

        $data = mb_substr($this->lobs[$lob]['Value'], $this->lobs[$lob]['Position'], $length);

        $this->lobs[$lob]['Position'] += $length;

        return ($length);
    }

    public function DestroyResultLOB($lob)
    {
        if (isset($this->lobs[$lob])) {
            $this->lobs[$lob] = '';
        }
    }

    public function FetchCLOBResult($result, $row, $field)
    {
        return ($this->SetError('Fetch CLOB result', 'fetch clob result method is not implemented'));
    }

    public function FetchBLOBResult($result, $row, $field)
    {
        return ($this->SetError('Fetch BLOB result', 'fetch blob result method is not implemented'));
    }

    public function ResultIsNull($result, $row, $field)
    {
        $value = $this->FetchResult($result, $row, $field);

        return (!isset($value));
    }

    public function BaseConvertResult(&$value, $type)
    {
        switch ($type) {
            case METABASE_TYPE_TEXT:
                return (1);
            case METABASE_TYPE_INTEGER:
                $value = (int)$value;

                return (1);
            case METABASE_TYPE_BOOLEAN:
                $value = (strcmp($value, 'Y') ? 0 : 1);

                return (1);
            case METABASE_TYPE_DECIMAL:
                return (1);
            case METABASE_TYPE_FLOAT:
                $value = (float)$value;

                return (1);
            case METABASE_TYPE_DATE:
            case METABASE_TYPE_TIME:
            case METABASE_TYPE_TIMESTAMP:
                return (1);
            case METABASE_TYPE_CLOB:
            case METABASE_TYPE_BLOB:
                $value = '';

                return ($this->SetError('BaseConvertResult', "attempt to convert result value to an unsupported type $type"));
            default:
                $value = '';

                return ($this->SetError('BaseConvertResult', "attempt to convert result value to an unknown type $type"));
        }
    }

    public function ConvertResult(&$value, $type)
    {
        return ($this->BaseConvertResult($value, $type));
    }

    public function ConvertResultRow($result, &$row)
    {
        if (isset($this->result_types[$result])) {
            if (-1 == ($columns = $this->NumberOfColumns($result))) {
                return (0);
            }

            for ($column = 0; $column < $columns; $column++) {
                if (!isset($row[$column])) {
                    continue;
                }

                switch ($type = $this->result_types[$result][$column]) {
                    case METABASE_TYPE_TEXT:
                        break;
                    case METABASE_TYPE_INTEGER:
                        $row[$column] = (int)$row[$column];
                        break;
                    default:
                        if (!$this->ConvertResult($row[$column], $type)) {
                            return (0);
                        }
                }
            }
        }

        return (1);
    }

    public function FetchDateResult($result, $row, $field)
    {
        $value = $this->FetchResult($result, $row, $field);

        $this->ConvertResult($value, METABASE_TYPE_DATE);

        return ($value);
    }

    public function FetchTimestampResult($result, $row, $field)
    {
        $value = $this->FetchResult($result, $row, $field);

        $this->ConvertResult($value, METABASE_TYPE_TIMESTAMP);

        return ($value);
    }

    public function FetchTimeResult($result, $row, $field)
    {
        $value = $this->FetchResult($result, $row, $field);

        $this->ConvertResult($value, METABASE_TYPE_TIME);

        return ($value);
    }

    public function FetchBooleanResult($result, $row, $field)
    {
        $value = $this->FetchResult($result, $row, $field);

        $this->ConvertResult($value, METABASE_TYPE_BOOLEAN);

        return ($value);
    }

    public function FetchFloatResult($result, $row, $field)
    {
        $value = $this->FetchResult($result, $row, $field);

        $this->ConvertResult($value, METABASE_TYPE_FLOAT);

        return ($value);
    }

    public function FetchDecimalResult($result, $row, $field)
    {
        $value = $this->FetchResult($result, $row, $field);

        $this->ConvertResult($value, METABASE_TYPE_DECIMAL);

        return ($value);
    }

    public function NumberOfRows($result)
    {
        $this->warning = 'number of rows method not implemented';

        return (0);
    }

    public function FreeResult($result)
    {
        $this->warning = 'free result method not implemented';

        return (0);
    }

    public function Error()
    {
        return ($this->last_error);
    }

    public function SetErrorHandler($function)
    {
        $last_function = $this->errorHandler;

        $this->errorHandler = $function;

        return ($last_function);
    }

    public function GetIntegerFieldTypeDeclaration($name, &$field)
    {
        if (isset($field['unsigned'])) {
            $this->warning = "unsigned integer field \"$name\" is being declared as signed integer";
        }

        return ("$name INT" . (isset($field['default']) ? ' DEFAULT ' . $field['default'] : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function GetTextFieldTypeDeclaration($name, $field)
    {
        return ((isset($field['length']) ? "$name CHAR (" . $field['length'] . ')' : "$name TEXT") . (isset($field['default']) ? ' DEFAULT ' . $this->GetTextFieldValue($field['default']) : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function GetCLOBFieldTypeDeclaration($name, &$field)
    {
        return ((isset($field['length']) ? "$name CHAR (" . $field['length'] . ')' : "$name TEXT") . (isset($field['default']) ? ' DEFAULT ' . $this->GetTextFieldValue($field['default']) : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function GetBLOBFieldTypeDeclaration($name, &$field)
    {
        return ((isset($field['length']) ? "$name CHAR (" . $field['length'] . ')' : "$name TEXT") . (isset($field['default']) ? ' DEFAULT ' . $this->GetTextFieldValue($field['default']) : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function GetBooleanFieldTypeDeclaration($name, $field)
    {
        return ("$name CHAR (1)" . (isset($field['default']) ? ' DEFAULT ' . $this->GetBooleanFieldValue($field['default']) : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function GetDateFieldTypeDeclaration($name, &$field)
    {
        return ("$name CHAR (" . mb_strlen('YYYY-MM-DD') . ')' . (isset($field['default']) ? ' DEFAULT ' . $this->GetDateFieldValue($field['default']) : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function GetTimestampFieldTypeDeclaration($name, &$field)
    {
        return ("$name CHAR (" . mb_strlen('YYYY-MM-DD HH:MM:SS') . ')' . (isset($field['default']) ? ' DEFAULT ' . $this->GetTimestampFieldValue($field['default']) : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function GetTimeFieldTypeDeclaration($name, &$field)
    {
        return ("$name CHAR (" . mb_strlen('HH:MM:SS') . ')' . (isset($field['default']) ? ' DEFAULT ' . $this->GetTimeFieldValue($field['default']) : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function GetFloatFieldTypeDeclaration($name, &$field)
    {
        return ("$name TEXT " . (isset($field['default']) ? ' DEFAULT ' . $this->GetFloatFieldValue($field['default']) : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function GetDecimalFieldTypeDeclaration($name, &$field)
    {
        return ("$name TEXT " . (isset($field['default']) ? ' DEFAULT ' . $this->GetDecimalFieldValue($field['default']) : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function GetIntegerFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : (string)$value);
    }

    public function GetTextFieldValue($value)
    {
        $this->EscapeText($value);

        return ("'$value'");
    }

    public function GetCLOBFieldValue($prepared_query, $parameter, $clob, &$value)
    {
        return ($this->SetError('Get CLOB field value', 'prepared queries with values of type "clob" are not yet supported'));
    }

    public function FreeCLOBValue($prepared_query, $clob, &$value, $success)
    {
    }

    public function GetBLOBFieldValue($prepared_query, $parameter, $blob, &$value)
    {
        return ($this->SetError('Get BLOB field value', 'prepared queries with values of type "blob" are not yet supported'));
    }

    public function FreeBLOBValue($prepared_query, $blob, &$value, $success)
    {
    }

    public function GetBooleanFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : ($value ? "'Y'" : "'N'"));
    }

    public function GetDateFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : "'$value'");
    }

    public function GetTimestampFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : "'$value'");
    }

    public function GetTimeFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : "'$value'");
    }

    public function GetFloatFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : "'$value'");
    }

    public function GetDecimalFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : "'$value'");
    }

    public function GetFieldValue($type, $value)
    {
        switch ($type) {
            case 'integer':
                return ($this->GetIntegerFieldValue($value));
            case 'text':
                return ($this->GetTextFieldValue($value));
            case 'boolean':
                return ($this->GetBooleanFieldValue($value));
            case 'date':
                return ($this->GetDateFieldValue($value));
            case 'timestamp':
                return ($this->GetTimestampFieldValue($value));
            case 'time':
                return ($this->GetTimeFieldValue($value));
            case 'float':
                return ($this->GetFloatFieldValue($value));
            case 'decimal':
                return ($this->GetDecimalFieldValue($value));
        }

        return ('');
    }

    public function Support($feature)
    {
        return (isset($this->supported[$feature]));
    }

    public function AutoCommitTransactions()
    {
        $this->Debug('AutoCommit: ' . ($auto_commit ? 'On' : 'Off'));

        return ($this->SetError('Auto-commit transactions', 'transactions are not supported'));
    }

    public function CommitTransaction()
    {
        $this->Debug('Commit Transaction');

        return ($this->SetError('Commit transaction', 'commiting transactions are not supported'));
    }

    public function RollbackTransaction()
    {
        $this->Debug('Rollback Transaction');

        return ($this->SetError('Rollback transaction', 'rolling back transactions are not supported'));
    }

    public function Setup()
    {
        return ('');
    }

    public function SetSelectedRowRange($first, $limit)
    {
        if (!isset($this->supported['SelectRowRanges'])) {
            return ($this->SetError('Set selected row range', 'selecting row ranges is not supported by this driver'));
        }

        if ('integer' != gettype($first)
            || $first < 0) {
            return ($this->SetError('Set selected row range', 'it was not specified a valid first selected range row'));
        }

        if ('integer' != gettype($limit)
            || $limit < 1) {
            return ($this->SetError('Set selected row range', 'it was not specified a valid selected range row limit'));
        }

        $this->first_selected_row = $first;

        $this->selected_row_limit = $limit;

        return (1);
    }

    public function GetColumnNames($result, &$columns)
    {
        $columns = [];

        return ($this->SetError('Get column names', 'obtaining result column names is not implemented'));
    }

    public function NumberOfColumns($result)
    {
        $this->SetError('Number of columns', 'obtaining the number of result columns is not implemented');

        return (-1);
    }

    public function SetResultTypes($result, $types)
    {
        if (isset($this->result_types[$result])) {
            return ($this->SetError('Set result types', 'attempted to redefine the types of the columns of a result set'));
        }

        if (-1 == ($columns = $this->NumberOfColumns($result))) {
            return (0);
        }

        if ($columns < count($types)) {
            return ($this->SetError('Set result types', 'it were specified more result types (' . count($types) . ") than result columns ($columns)"));
        }

        $valid_types = [
            'text' => METABASE_TYPE_TEXT,
            'boolean' => METABASE_TYPE_BOOLEAN,
            'integer' => METABASE_TYPE_INTEGER,
            'decimal' => METABASE_TYPE_DECIMAL,
            'float' => METABASE_TYPE_FLOAT,
            'date' => METABASE_TYPE_DATE,
            'time' => METABASE_TYPE_TIME,
            'timestamp' => METABASE_TYPE_TIMESTAMP,
            'clob' => METABASE_TYPE_CLOB,
            'blob' => METABASE_TYPE_BLOB,
        ];

        for ($column = 0, $columnMax = count($types); $column < $columnMax; $column++) {
            if (!isset($valid_types[$types[$column]])) {
                return ($this->SetError('Set result types', $types[$column] . ' is not a supported column type'));
            }

            $this->result_types[$result][$column] = $valid_types[$types[$column]];
        }

        for (; $column < $columns; $column++) {
            $this->result_types[$result][$column] = METABASE_TYPE_TEXT;
        }

        return (1);
    }

    public function FetchResultField($result, &$value)
    {
        if (!$result) {
            return ($this->SetError('Fetch field', 'it was not specified a valid result set'));
        }

        if ($this->EndOfResult($result)) {
            $success = $this->SetError('Fetch field', 'result set is empty');
        } else {
            if ($this->ResultIsNull($result, 0, 0)) {
                unset($value);
            } else {
                $value = $this->FetchResult($result, 0, 0);
            }

            $success = 1;
        }

        if ($success
            && isset($this->result_types[$result])) {
            switch ($type = $this->result_types[$result][0]) {
                case METABASE_TYPE_TEXT:
                    break;
                case METABASE_TYPE_INTEGER:
                    $value = (int)$value;
                    break;
                default:
                    $success = $this->ConvertResult($value, $type);
                    break;
            }
        }

        $this->FreeResult($result);

        return ($success);
    }

    public function BaseFetchResultArray($result, &$array, $row)
    {
        if (-1 == ($columns = $this->NumberOfColumns($result))) {
            return (0);
        }

        for ($array = [], $column = 0; $column < $columns; $column++) {
            if (!$this->ResultIsNull($result, $row, $column)) {
                $array[$column] = $this->FetchResult($result, $row, $column);
            }
        }

        return ($this->ConvertResultRow($result, $array));
    }

    public function FetchResultArray($result, &$array, $row)
    {
        return ($this->BaseFetchResultArray($result, $array, $row));
    }

    public function FetchResultRow($result, &$row)
    {
        if (!$result) {
            return ($this->SetError('Fetch field', 'it was not specified a valid result set'));
        }

        if ($this->EndOfResult($result)) {
            $success = $this->SetError('Fetch field', 'result set is empty');
        } else {
            $success = $this->FetchResultArray($result, $row, 0);
        }

        $this->FreeResult($result);

        return ($success);
    }

    public function FetchResultColumn($result, &$column)
    {
        if (!$result) {
            return ($this->SetError('Fetch field', 'it was not specified a valid result set'));
        }

        for ($success = 1, $column = [], $row = 0; !$this->EndOfResult($result); $row++) {
            if ($this->ResultIsNull($result, 0, 0)) {
                continue;
            }

            $column[$row] = $this->FetchResult($result, $row, 0);

            if (isset($this->result_types[$result])) {
                switch ($type = $this->result_types[$result][0]) {
                    case METABASE_TYPE_TEXT:
                        break;
                    case METABASE_TYPE_INTEGER:
                        $column[$row] = (int)$column[$row];
                        break;
                    default:
                        if (!($success = $this->ConvertResult($column[$row], $type))) {
                            break 2;
                        }
                        break;
                }
            }
        }

        $this->FreeResult($result);

        return ($success);
    }

    public function FetchResultAll($result, &$all)
    {
        if (!$result) {
            return ($this->SetError('Fetch field', 'it was not specified a valid result set'));
        }

        for ($success = 1, $all = [], $row = 0; !$this->EndOfResult($result); $row++) {
            if (!($success = $this->FetchResultArray($result, $all[$row], $row))) {
                break;
            }
        }

        $this->FreeResult($result);

        return ($success);
    }

    public function QueryField($query, &$field, $type = 'text')
    {
        if (!($result = $this->Query($query))) {
            return (0);
        }

        if (strcmp($type, 'text')) {
            $types = [$type];

            if (!($success = $this->SetResultTypes($result, $types))) {
                $this->FreeResult($result);

                return (0);
            }
        }

        return ($this->FetchResultField($result, $field));
    }

    public function QueryRow($query, &$row, $types = '')
    {
        if (!($result = $this->Query($query))) {
            return (0);
        }

        if ('array' == gettype($types)) {
            if (!($success = $this->SetResultTypes($result, $types))) {
                $this->FreeResult($result);

                return (0);
            }
        }

        return ($this->FetchResultRow($result, $row));
    }

    public function QueryColumn($query, &$column, $type = 'text')
    {
        if (!($result = $this->Query($query))) {
            return (0);
        }

        if (strcmp($type, 'text')) {
            $types = [$type];

            if (!($success = $this->SetResultTypes($result, $types))) {
                $this->FreeResult($result);

                return (0);
            }
        }

        return ($this->FetchResultColumn($result, $column));
    }

    public function QueryAll($query, &$all, $types = '')
    {
        if (!($result = $this->Query($query))) {
            return (0);
        }

        if ('array' == gettype($types)) {
            if (!($success = $this->SetResultTypes($result, $types))) {
                $this->FreeResult($result);

                return (0);
            }
        }

        return ($this->FetchResultAll($result, $all));
    }
}

