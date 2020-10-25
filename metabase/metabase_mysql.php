<?php

if (!defined('METABASE_MYSQL_INCLUDED')) {
    define('METABASE_MYSQL_INCLUDED', 1);

    /*
     * metabase_mysql.php
     *
     * @(#) $Header: /home/mlemos/cvsroot/metabase/metabase_mysql.php,v 1.70 2003/01/30 23:59:51 mlemos Exp $
     *
     */

    class metabase_mysql_class extends metabase_database_class
    {
        public $connection = 0;

        public $connected_host;

        public $connected_user;

        public $connected_password;

        public $connected_port;

        public $opened_persistent = '';

        public $decimal_factor = 1.0;

        public $highest_fetched_row = [];

        public $columns = [];

        public $fixed_float = 0;

        public $escape_quotes = '\\';

        public $sequence_prefix = '_sequence_';

        public $dummy_primary_key = 'dummy_primary_key';

        public $manager_class_name = 'metabase_manager_mysql_class';

        public $manager_include = 'manager_mysql.php';

        public $manager_included_constant = 'METABASE_MANAGER_MYSQL_INCLUDED';

        public $default_table_type = '';

        public function Connect()
        {
            $port = ($this->options['Port'] ?? '');

            if (0 != $this->connection) {
                if (!strcmp($this->connected_host, $this->host)
                    && !strcmp($this->connected_user, $this->user)
                    && !strcmp($this->connected_password, $this->password)
                    && !strcmp($this->connected_port, $port)
                    && $this->opened_persistent == $this->persistent) {
                    return (1);
                }

                $GLOBALS['xoopsDB']->close($this->connection);

                $this->connection = 0;

                $this->affected_rows = -1;
            }

            $this->fixed_float = 30;

            $function = ($this->persistent ? 'mysql_pconnect' : 'mysql_connect');

            if (!function_exists($function)) {
                return ($this->SetError('Connect', 'MySQL support is not available in this PHP configuration'));
            }

            if (($this->connection = @$function($this->host . (!strcmp($port, '') ? '' : ':' . $port), $this->user, $this->password)) <= 0) {
                return ($this->SetError('Connect', $php_errormsg ?? 'Could not connect to MySQL server'));
            }

            if (isset($this->options['FixedFloat'])) {
                $this->fixed_float = $this->options['FixedFloat'];
            } else {
                if (($result = $GLOBALS['xoopsDB']->queryF('SELECT VERSION()', $this->connection))) {
                    $version = explode('.', mysql_result($result, 0, 0));

                    $major = (int)$version[0];

                    $minor = (int)$version[1];

                    $revision = (int)$version[2];

                    if ($major > 3
                        || (3 == $major
                            && $minor >= 23
                            && ($minor > 23
                                || $revision >= 6))) {
                        $this->fixed_float = 0;
                    }

                    $GLOBALS['xoopsDB']->freeRecordSet($result);
                }
            }

            if (isset($this->supported['Transactions'])
                && !$this->auto_commit) {
                if (!$GLOBALS['xoopsDB']->queryF('SET AUTOCOMMIT=0', $this->connection)) {
                    $GLOBALS['xoopsDB']->close($this->connection);

                    $this->connection = 0;

                    $this->affected_rows = -1;

                    return (0);
                }

                $this->RegisterTransactionShutdown(0);
            }

            $this->connected_host = $this->host;

            $this->connected_user = $this->user;

            $this->connected_password = $this->password;

            $this->connected_port = $port;

            $this->opened_persistent = $this->persistent;

            return (1);
        }

        public function Close()
        {
            if (0 != $this->connection) {
                if (isset($this->supported['Transactions'])
                    && !$this->auto_commit) {
                    $this->AutoCommitTransactions(1);
                }

                $GLOBALS['xoopsDB']->close($this->connection);

                $this->connection = 0;

                $this->affected_rows = -1;
            }
        }

        public function Query($query)
        {
            $this->Debug("Query: $query");

            $first = $this->first_selected_row;

            $limit = $this->selected_row_limit;

            $this->first_selected_row = $this->selected_row_limit = 0;

            if (!strcmp($this->database_name, '')) {
                return ($this->SetError('Query', 'it was not specified a valid database name to select'));
            }

            if (!$this->Connect()) {
                return (0);
            }

            if ('integer' == gettype($space = mb_strpos($query_string = mb_strtolower(ltrim($query)), ' '))) {
                $query_string = mb_substr($query_string, 0, $space);
            }

            if (($select = ('select' == $query_string || 'show' == $query_string))
                && $limit > 0) {
                $query .= " LIMIT $first,$limit";
            }

            if (mysqli_select_db($GLOBALS['xoopsDB']->conn, $this->database_name, $this->connection)
                && ($result = $GLOBALS['xoopsDB']->queryF($query, $this->connection))) {
                if ($select) {
                    $this->highest_fetched_row[$result] = -1;
                } else {
                    $this->affected_rows = $GLOBALS['xoopsDB']->getAffectedRows($this->connection);
                }
            } else {
                return ($this->SetError('Query', $GLOBALS['xoopsDB']->error($this->connection)));
            }

            return ($result);
        }

        public function Replace($table, &$fields)
        {
            $count = count($fields);

            for ($keys = 0, $query = $values = '', reset($fields), $field = 0; $field < $count; next($fields), $field++) {
                $name = key($fields);

                if ($field > 0) {
                    $query .= ',';

                    $values .= ',';
                }

                $query .= $name;

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

                $values .= $value;

                if (isset($fields[$name]['Key'])
                    && $fields[$name]['Key']) {
                    if ('NULL' == $value) {
                        return ($this->SetError('Replace', 'key values may not be NULL'));
                    }

                    $keys++;
                }
            }

            if (0 == $keys) {
                return ($this->SetError('Replace', 'it were not specified which fields are keys'));
            }

            return ($this->Query("REPLACE INTO $table ($query) VALUES($values)"));
        }

        public function EndOfResult($result)
        {
            if (!isset($this->highest_fetched_row[$result])) {
                $this->SetError('End of result', 'attempted to check the end of an unknown result');

                return (-1);
            }

            return ($this->highest_fetched_row[$result] >= $this->NumberOfRows($result) - 1);
        }

        public function FetchResult($result, $row, $field)
        {
            $this->highest_fetched_row[$result] = max($this->highest_fetched_row[$result], $row);

            return (mysql_result($result, $row, $field));
        }

        public function FetchResultArray($result, &$array, $row)
        {
            if (!mysql_data_seek($result, $row)
                || !($array = $GLOBALS['xoopsDB']->fetchRow($result))) {
                return ($this->SetError('Fetch result array', $GLOBALS['xoopsDB']->error($this->connection)));
            }

            $this->highest_fetched_row[$result] = max($this->highest_fetched_row[$result], $row);

            return ($this->ConvertResultRow($result, $array));
        }

        public function FetchCLOBResult($result, $row, $field)
        {
            return ($this->FetchLOBResult($result, $row, $field));
        }

        public function FetchBLOBResult($result, $row, $field)
        {
            return ($this->FetchLOBResult($result, $row, $field));
        }

        public function ConvertResult(&$value, $type)
        {
            switch ($type) {
                case METABASE_TYPE_BOOLEAN:
                    $value = (strcmp($value, 'Y') ? 0 : 1);

                    return (1);
                case METABASE_TYPE_DECIMAL:
                    $value = sprintf('%.' . $this->decimal_places . 'f', (float)$value / $this->decimal_factor);

                    return (1);
                case METABASE_TYPE_FLOAT:
                    $value = (float)$value;

                    return (1);
                case METABASE_TYPE_DATE:
                case METABASE_TYPE_TIME:
                case METABASE_TYPE_TIMESTAMP:
                    return (1);
                default:
                    return ($this->BaseConvertResult($value, $type));
            }
        }

        public function NumberOfRows($result)
        {
            return ($GLOBALS['xoopsDB']->getRowsNum($result));
        }

        public function FreeResult($result)
        {
            unset($this->highest_fetched_row[$result]);

            unset($this->columns[$result]);

            unset($this->result_types[$result]);

            return ($GLOBALS['xoopsDB']->freeRecordSet($result));
        }

        public function GetCLOBFieldTypeDeclaration($name, &$field)
        {
            if (isset($field['length'])) {
                $length = $field['length'];

                if ($length <= 255) {
                    $type = 'TINYTEXT';
                } else {
                    if ($length <= 65535) {
                        $type = 'TEXT';
                    } else {
                        if ($length <= 16777215) {
                            $type = 'MEDIUMTEXT';
                        } else {
                            $type = 'LONGTEXT';
                        }
                    }
                }
            } else {
                $type = 'LONGTEXT';
            }

            return ("$name $type" . (isset($field['notnull']) ? ' NOT NULL' : ''));
        }

        public function GetBLOBFieldTypeDeclaration($name, &$field)
        {
            if (isset($field['length'])) {
                $length = $field['length'];

                if ($length <= 255) {
                    $type = 'TINYBLOB';
                } else {
                    if ($length <= 65535) {
                        $type = 'BLOB';
                    } else {
                        if ($length <= 16777215) {
                            $type = 'MEDIUMBLOB';
                        } else {
                            $type = 'LONGBLOB';
                        }
                    }
                }
            } else {
                $type = 'LONGBLOB';
            }

            return ("$name $type" . (isset($field['notnull']) ? ' NOT NULL' : ''));
        }

        public function GetIntegerFieldTypeDeclaration($name, &$field)
        {
            return ("$name " . (isset($field['unsigned']) ? 'INT UNSIGNED' : 'INT') . (isset($field['default']) ? ' DEFAULT ' . $field['default'] : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
        }

        public function GetDateFieldTypeDeclaration($name, &$field)
        {
            return ($name . ' DATE' . (isset($field['default']) ? " DEFAULT '" . $field['default'] . "'" : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
        }

        public function GetTimestampFieldTypeDeclaration($name, &$field)
        {
            return ($name . ' DATETIME' . (isset($field['default']) ? " DEFAULT '" . $field['default'] . "'" : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
        }

        public function GetTimeFieldTypeDeclaration($name, &$field)
        {
            return ($name . ' TIME' . (isset($field['default']) ? " DEFAULT '" . $field['default'] . "'" : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
        }

        public function GetFloatFieldTypeDeclaration($name, &$field)
        {
            if (isset($this->options['FixedFloat'])) {
                $this->fixed_float = $this->options['FixedFloat'];
            } else {
                if (0 == $this->connection) {
                    $this->Connect();
                }
            }

            return ("$name DOUBLE" . ($this->fixed_float ? '(' . ($this->fixed_float + 2) . ',' . $this->fixed_float . ')' : '') . (isset($field['default']) ? ' DEFAULT ' . $this->GetFloatFieldValue($field['default']) : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
        }

        public function GetDecimalFieldTypeDeclaration($name, &$field)
        {
            return ("$name BIGINT" . (isset($field['default']) ? ' DEFAULT ' . $this->GetDecimalFieldValue($field['default']) : '') . (isset($field['notnull']) ? ' NOT NULL' : ''));
        }

        public function GetCLOBFieldValue($prepared_query, $parameter, $clob, &$value)
        {
            for ($value = "'"; !MetabaseEndOfLOB($clob);) {
                if (MetabaseReadLOB($clob, $data, $this->lob_buffer_length) < 0) {
                    $value = '';

                    return ($this->SetError('Get CLOB field value', MetabaseLOBError($clob)));
                }

                $this->EscapeText($data);

                $value .= $data;
            }

            $value .= "'";

            return (1);
        }

        public function FreeCLOBValue($prepared_query, $clob, $value, $success)
        {
            unset($value);
        }

        public function GetBLOBFieldValue($prepared_query, $parameter, $blob, &$value)
        {
            for ($value = "'"; !MetabaseEndOfLOB($blob);) {
                if (!MetabaseReadLOB($blob, $data, $this->lob_buffer_length)) {
                    $value = '';

                    return ($this->SetError('Get BLOB field value', MetabaseLOBError($clob)));
                }

                $value .= addslashes($data);
            }

            $value .= "'";

            return (1);
        }

        public function FreeBLOBValue($prepared_query, $blob, $value, $success)
        {
            unset($value);
        }

        public function GetFloatFieldValue($value)
        {
            return (!strcmp($value, 'NULL') ? 'NULL' : (string)$value);
        }

        public function GetDecimalFieldValue($value)
        {
            return (!strcmp($value, 'NULL') ? 'NULL' : (string)round((float)$value * $this->decimal_factor));
        }

        public function GetColumnNames($result, &$column_names)
        {
            $result_value = (int)$result;

            if (!isset($this->highest_fetched_row[$result_value])) {
                return ($this->SetError('Get column names', 'it was specified an inexisting result set'));
            }

            if (!isset($this->columns[$result_value])) {
                $this->columns[$result_value] = [];

                $columns = mysqli_num_fields($result);

                for ($column = 0; $column < $columns; $column++) {
                    $this->columns[$result_value][mb_strtolower(mysql_field_name($result, $column))] = $column;
                }
            }

            $column_names = $this->columns[$result_value];

            return (1);
        }

        public function NumberOfColumns($result)
        {
            if (!isset($this->highest_fetched_row[(int)$result])) {
                $this->SetError('Get column names', 'it was specified an inexisting result set');

                return (-1);
            }

            return (mysqli_num_fields($result));
        }

        public function GetSequenceNextValue($name, &$value)
        {
            $sequence_name = $this->sequence_prefix . $name;

            if (!$this->Query("INSERT INTO $sequence_name (sequence) VALUES (NULL)")) {
                return (0);
            }

            $value = (int)$GLOBALS['xoopsDB']->getInsertId($this->connection);

            if (!$this->Query("DELETE FROM $sequence_name WHERE sequence<$value")) {
                $this->warning = 'could delete previous sequence table values';
            }

            return (1);
        }

        public function AutoCommitTransactions($auto_commit)
        {
            $this->Debug('AutoCommit: ' . ($auto_commit ? 'On' : 'Off'));

            if (!isset($this->supported['Transactions'])) {
                return ($this->SetError('Auto-commit transactions', 'transactions are not in use'));
            }

            if (((!$this->auto_commit) == (!$auto_commit))) {
                return (1);
            }

            if ($this->connection) {
                if ($auto_commit) {
                    if (!$this->Query('COMMIT')
                        || !$this->Query('SET AUTOCOMMIT=1')) {
                        return (0);
                    }
                } else {
                    if (!$this->Query('SET AUTOCOMMIT=0')) {
                        return (0);
                    }
                }
            }

            $this->auto_commit = $auto_commit;

            return ($this->RegisterTransactionShutdown($auto_commit));
        }

        public function CommitTransaction()
        {
            $this->Debug('Commit Transaction');

            if (!isset($this->supported['Transactions'])) {
                return ($this->SetError('Commit transaction', 'transactions are not in use'));
            }

            if ($this->auto_commit) {
                return ($this->SetError('Commit transaction', 'transaction changes are being auto commited'));
            }

            return ($this->Query('COMMIT'));
        }

        public function RollbackTransaction()
        {
            $this->Debug('Rollback Transaction');

            if (!isset($this->supported['Transactions'])) {
                return ($this->SetError('Rollback transaction', 'transactions are not in use'));
            }

            if ($this->auto_commit) {
                return ($this->SetError('Rollback transaction', 'transactions can not be rolled back when changes are auto commited'));
            }

            return ($this->Query('ROLLBACK'));
        }

        public function Setup()
        {
            $this->supported['Sequences'] = $this->supported['Indexes'] = $this->supported['AffectedRows'] = $this->supported['SummaryFunctions'] = $this->supported['OrderByText'] = $this->supported['GetSequenceCurrentValue'] = $this->supported['SelectRowRanges'] = $this->supported['LOBs'] = $this->supported['Replace'] = 1;

            if (isset($this->options['UseTransactions'])
                && $this->options['UseTransactions']) {
                $this->supported['Transactions'] = 1;

                $this->default_table_type = 'BDB';
            } else {
                $this->default_table_type = '';
            }

            if (isset($this->options['DefaultTableType'])) {
                switch ($this->default_table_type = mb_strtoupper($this->options['DefaultTableType'])) {
                    case 'BERKELEYDB':
                        $this->default_table_type = 'BDB';
                        // no break
                    case 'BDB':
                    case 'INNODB':
                    case 'GEMINI':
                        break;
                    case 'HEAP':
                    case 'ISAM':
                    case 'MERGE':
                    case 'MRG_MYISAM':
                    case 'MYISAM':
                        if (isset($this->supported['Transactions'])) {
                            return ($this->options['DefaultTableType'] . ' is not a transaction-safe default table type');
                        }
                        break;
                    default:
                        return ($this->options['DefaultTableType'] . ' is not a supported default table type');
                }
            }

            $this->decimal_factor = pow(10.0, $this->decimal_places);

            return ('');
        }
    }
}
