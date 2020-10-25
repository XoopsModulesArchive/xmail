<?php

if (!defined('METABASE_MANAGER_MYSQL_INCLUDED')) {
    define('METABASE_MANAGER_MYSQL_INCLUDED', 1);

    /*
     * manager_mysql.php
     *
     * @(#) $Header: /home/mlemos/cvsroot/metabase/manager_mysql.php,v 1.7 2003/01/08 20:30:43 mlemos Exp $
     *
     */

    class metabase_manager_mysql_class extends metabase_manager_database_class
    {
        public $verified_table_types = [];

        public function VerifyTransactionalTableType($db, $table_type)
        {
            switch (mb_strtoupper($table_type)) {
                case 'BERKELEYDB':
                case 'BDB':
                    $check = ['have_bdb'];
                    break;
                case 'INNODB':
                    $check = ['have_innodb', 'have_innobase'];
                    break;
                case 'GEMINI':
                    $check = ['have_gemini'];
                    break;
                case 'HEAP':
                case 'ISAM':
                case 'MERGE':
                case 'MRG_MYISAM':
                case 'MYISAM':
                case '':
                    return (1);
                default:
                    return ($db->SetError('Verify transactional table', $table_type . ' is not a supported table type'));
            }

            if (!$db->Connect()) {
                return (0);
            }

            if (isset($this->verified_table_types[$table_type])
                && $this->verified_table_types[$table_type] == $db->connection) {
                return (1);
            }

            for ($has_any = $type = 0, $typeMax = count($check); $type < $typeMax; $type++) {
                if (!$db->QueryAll("SHOW VARIABLES LIKE '" . $check[$type] . "'", $has)) {
                    return (0);
                }

                $has_any += count($has);

                if (count($has)
                    && !strcmp($has[0][1], 'YES')) {
                    break;
                }
            }

            if (0 == count($has_any)) {
                return ($db->SetError('Verify transactional table', 'could not tell if ' . $table_type . ' is a supported table type'));
            }

            if (0 == count($has)
                || strcmp($has[0][1], 'YES')) {
                return ($db->SetError('Verify transactional table', $table_type . ' is not a supported table type by this MySQL database server'));
            }

            $this->verified_table_types[$table_type] = $db->connection;

            return (1);
        }

        public function CreateDatabase($db, $name)
        {
            if (!$db->Connect()) {
                return (0);
            }

            if (function_exists('mysql_create_db')) {
                $success = mysql_create_db($name, $db->connection);
            } else {
                $db->EscapeText($name);

                $success = $GLOBALS['xoopsDB']->queryF("CREATE DATABASE $name", $db->connection);
            }

            if (!$success) {
                return ($db->SetError('Create database', $GLOBALS['xoopsDB']->error($db->connection)));
            }

            return (1);
        }

        public function DropDatabase($db, $name)
        {
            if (!$db->Connect()) {
                return (0);
            }

            if (function_exists('mysql_drop_db')) {
                $success = mysql_drop_db($name, $db->connection);
            } else {
                $db->EscapeText($name);

                $success = $GLOBALS['xoopsDB']->queryF("DROP DATABASE $name", $db->connection);
            }

            if (!$success) {
                return ($db->SetError('Drop database', $GLOBALS['xoopsDB']->error($db->connection)));
            }

            return (1);
        }

        public function CreateTable($db, $name, &$fields)
        {
            if (!isset($name)
                || !strcmp($name, '')) {
                return ($db->SetError('Create table', 'it was not specified a valid table name'));
            }

            if (0 == count($fields)) {
                return ($db->SetError('Create table', "it were not specified any fields for table \"$name\""));
            }

            if (!$this->VerifyTransactionalTableType($db, $db->default_table_type)) {
                return (0);
            }

            $query_fields = '';

            if (!$this->GetFieldList($db, $fields, $query_fields)) {
                return (0);
            }

            if (isset($db->supported['Transactions'])
                && 'BDB' == $db->default_table_type) {
                $query_fields .= ', ' . $db->dummy_primary_key . ' INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (' . $db->dummy_primary_key . ')';
            }

            return ($db->Query("CREATE TABLE $name ($query_fields)" . (mb_strlen($db->default_table_type) ? ' TYPE=' . $db->default_table_type : '')));
        }

        public function AlterTable($db, $name, &$changes, $check)
        {
            if ($check) {
                for ($change = 0, reset($changes), $changeMax = count($changes); $change < $changeMax; next($changes), $change++) {
                    switch (key($changes)) {
                        case 'AddedFields':
                        case 'RemovedFields':
                        case 'ChangedFields':
                        case 'RenamedFields':
                        case 'name':
                            break;
                        default:
                            return ($db->SetError('Alter table', 'change type "' . key($changes) . '" not yet supported'));
                    }
                }

                return (1);
            }  

            $query = (isset($changes['name']) ? 'RENAME AS ' . $changes['name'] : '');

            if (isset($changes['AddedFields'])) {
                $fields = $changes['AddedFields'];

                for ($field = 0, reset($fields), $fieldMax = count($fields); $field < $fieldMax; next($fields), $field++) {
                    if (strcmp($query, '')) {
                        $query .= ', ';
                    }

                    $query .= 'ADD ' . $fields[key($fields)]['Declaration'];
                }
            }

            if (isset($changes['RemovedFields'])) {
                $fields = $changes['RemovedFields'];

                for ($field = 0, reset($fields), $fieldMax = count($fields); $field < $fieldMax; next($fields), $field++) {
                    if (strcmp($query, '')) {
                        $query .= ', ';
                    }

                    $query .= 'DROP ' . key($fields);
                }
            }

            $renamed_fields = [];

            if (isset($changes['RenamedFields'])) {
                $fields = $changes['RenamedFields'];

                for ($field = 0, reset($fields), $fieldMax = count($fields); $field < $fieldMax; next($fields), $field++) {
                    $renamed_fields[$fields[key($fields)]['name']] = key($fields);
                }
            }

            if (isset($changes['ChangedFields'])) {
                $fields = $changes['ChangedFields'];

                for ($field = 0, reset($fields), $fieldMax = count($fields); $field < $fieldMax; next($fields), $field++) {
                    if (strcmp($query, '')) {
                        $query .= ', ';
                    }

                    if (isset($renamed_fields[key($fields)])) {
                        $field_name = $renamed_fields[key($fields)];

                        unset($renamed_fields[key($fields)]);
                    } else {
                        $field_name = key($fields);
                    }

                    $query .= "CHANGE $field_name " . $fields[key($fields)]['Declaration'];
                }
            }

            if (count($renamed_fields)) {
                for ($field = 0, reset($renamed_fields), $fieldMax = count($renamed_fields); $field < $fieldMax; next($renamed_fields), $field++) {
                    if (strcmp($query, '')) {
                        $query .= ', ';
                    }

                    $old_field_name = $renamed_fields[key($renamed_fields)];

                    $query .= "CHANGE $old_field_name " . $changes['RenamedFields'][$old_field_name]['Declaration'];
                }
            }

            return ($db->Query("ALTER TABLE $name $query"));
        }

        public function ListTables($db, &$tables)
        {
            if (!$db->QueryColumn('SHOW TABLES', $table_names)) {
                return (0);
            }

            $prefix_length = mb_strlen($db->sequence_prefix);

            for ($tables = [], $table = 0, $tableMax = count($table_names); $table < $tableMax; $table++) {
                if (mb_substr($table_names[$table], 0, $prefix_length) != $db->sequence_prefix) {
                    $tables[] = $table_names[$table];
                }
            }

            return (1);
        }

        public function ListTableFields($db, $table, &$fields)
        {
            if (!($result = $db->Query("SHOW COLUMNS FROM $table"))) {
                return (0);
            }

            if (!$db->GetColumnNames($result, $columns)) {
                $db->FreeResult($result);

                return (0);
            }

            if (!isset($columns['field'])) {
                $db->FreeResult($result);

                return ($db->SetError('List table fields', 'show columns does not return the table field names'));
            }

            $field_column = $columns['field'];

            for ($fields = [], $field = 0; !$db->EndOfResult($result); $field++) {
                $field_name = $db->FetchResult($result, $field, $field_column);

                if ($field_name != $db->dummy_primary_key) {
                    $fields[] = $field_name;
                }
            }

            $db->FreeResult($result);

            return (1);
        }

        public function GetTableFieldDefinition($db, $table, $field, &$definition)
        {
            $field_name = mb_strtolower($field);

            if ($field_name == $db->dummy_primary_key) {
                return ($db->SetError('Get table field definition', $db->dummy_primary_key . ' is an hidden column'));
            }

            if (!($result = $db->Query("SHOW COLUMNS FROM $table"))) {
                return (0);
            }

            if (!$db->GetColumnNames($result, $columns)) {
                $db->FreeResult($result);

                return (0);
            }

            if (!isset($columns[$column = 'field'])
                || !isset($columns[$column = 'type'])) {
                $db->FreeResult($result);

                return ($db->SetError('Get table field definition', "show columns does not return the column $column"));
            }

            $field_column = $columns['field'];

            $type_column = $columns['type'];

            for ($field_row = 0; !$db->EndOfResult($result); $field_row++) {
                if (!$db->FetchResultArray($result, $row, $field_row)) {
                    $db->FreeResult($result);

                    return (0);
                }

                if ($field_name == mb_strtolower($row[$field_column])) {
                    $db_type = mb_strtolower($row[$type_column]);

                    $db_type = strtok($db_type, '(), ');

                    if ('national' == $db_type) {
                        $db_type = strtok('(), ');
                    }

                    $length = strtok('(), ');

                    $decimal = strtok('(), ');

                    $type = [];

                    switch ($db_type) {
                        case 'tinyint':
                        case 'smallint':
                        case 'mediumint':
                        case 'int':
                        case 'integer':
                        case 'bigint':
                            $type[0] = 'integer';
                            if ('1' == $length) {
                                $type[1] = 'boolean';
                            }
                            break;
                        case 'tinytext':
                        case 'mediumtext':
                        case 'longtext':
                        case 'text':
                        case 'char':
                        case 'varchar':
                            $type[0] = 'text';
                            if ('binary' == $decimal) {
                                $type[1] = 'blob';
                            } elseif ('1' == $length) {
                                $type[1] = 'boolean';
                            } elseif (mb_strstr($db_type, 'text')) {
                                $type[1] = 'clob';
                            }
                            break;
                        case 'enum':
                        case 'set':
                            $type[0] = 'text';
                            $type[1] = 'integer';
                            break;
                        case 'date':
                            $type[0] = 'date';
                            break;
                        case 'datetime':
                        case 'timestamp':
                            $type[0] = 'timestamp';
                            break;
                        case 'time':
                            $type[0] = 'time';
                            break;
                        case 'float':
                        case 'double':
                        case 'real':
                            $type[0] = 'float';
                            break;
                        case 'decimal':
                        case 'numeric':
                            $type[0] = 'decimal';
                            break;
                        case 'tinyblob':
                        case 'mediumblob':
                        case 'longblob':
                        case 'blob':
                            $type[0] = 'blob';
                            break;
                        case 'year':
                            $type[0] = 'integer';
                            $type[1] = 'date';
                            break;
                        default:
                            return ($db->SetError('Get table field definition', 'unknown database attribute type'));
                    }

                    unset($notnull);

                    if (isset($columns['null'])
                        && 'YES' != $row[$columns['null']]) {
                        $notnull = 1;
                    }

                    unset($default);

                    if (isset($columns['default'])
                        && isset($row[$columns['default']])) {
                        $default = $row[$columns['default']];
                    }

                    for ($definition = [], $datatype = 0, $datatypeMax = count($type); $datatype < $datatypeMax; $datatype++) {
                        $definition[$datatype] = [
                            'type' => $type[$datatype],
                        ];

                        if (isset($notnull)) {
                            $definition[$datatype]['notnull'] = 1;
                        }

                        if (isset($default)) {
                            $definition[$datatype]['default'] = $default;
                        }

                        if (mb_strlen($length)) {
                            $definition[$datatype]['length'] = $length;
                        }
                    }

                    $db->FreeResult($result);

                    return (1);
                }
            }

            $db->FreeResult($result);

            return ($db->SetError('Get table field definition', 'it was not specified an existing table column'));
        }

        public function ListTableIndexes($db, $table, &$indexes)
        {
            if (!($result = $db->Query("SHOW INDEX FROM $table"))) {
                return (0);
            }

            if (!$db->GetColumnNames($result, $columns)) {
                $db->FreeResult($result);

                return (0);
            }

            if (!isset($columns['key_name'])) {
                $db->FreeResult($result);

                return ($db->SetError('List table indexes', 'show index does not return the table index names'));
            }

            $key_name_column = $columns['key_name'];

            for ($found = $indexes = [], $index = 0; !$db->EndOfResult($result); $index++) {
                $index_name = $db->FetchResult($result, $index, $key_name_column);

                if ('PRIMARY' != $index_name
                    && !isset($found[$index_name])) {
                    $indexes[] = $index_name;

                    $found[$index_name] = 1;
                }
            }

            $db->FreeResult($result);

            return (1);
        }

        public function GetTableIndexDefinition($db, $table, $index, &$definition)
        {
            $index_name = mb_strtolower($index);

            if ('PRIMARY' == $index_name) {
                return ($db->SetError('Get table index definition', 'PRIMARY is an hidden index'));
            }

            if (!($result = $db->Query("SHOW INDEX FROM $table"))) {
                return (0);
            }

            if (!$db->GetColumnNames($result, $columns)) {
                $db->FreeResult($result);

                return (0);
            }

            if (!isset($columns[$column = 'non_unique'])
                || !isset($columns[$column = 'key_name'])
                || !isset($columns[$column = 'column_name'])
                || !isset($columns[$column = 'collation'])) {
                $db->FreeResult($result);

                return ($db->SetError('Get table index definition', "show index does not return the column $column"));
            }

            $non_unique_column = $columns['non_unique'];

            $key_name_column = $columns['key_name'];

            $column_name_column = $columns['column_name'];

            $collation_column = $columns['collation'];

            $definition = [];

            for ($index_row = 0; !$db->EndOfResult($result); $index_row++) {
                if (!$db->FetchResultArray($result, $row, $index_row)) {
                    $db->FreeResult($result);

                    return (0);
                }

                $key_name = $row[$key_name_column];

                if (!strcmp($index_name, $key_name)) {
                    if (!$row[$non_unique_column]) {
                        $definition['unique'] = 1;
                    }

                    $column_name = $row[$column_name_column];

                    $definition['FIELDS'][$column_name] = [];

                    if (isset($row[$collation_column])) {
                        $definition['FIELDS'][$column_name]['sorting'] = ('A' == $row[$collation_column] ? 'ascending' : 'descending');
                    }
                }
            }

            $db->FreeResult($result);

            return (isset($definition['FIELDS']) ? 1 : $db->SetError('Get table index definition', 'it was not specified an existing table index'));
        }

        public function ListSequences($db, &$sequences)
        {
            if (!$db->QueryColumn('SHOW TABLES', $table_names)) {
                return (0);
            }

            $prefix_length = mb_strlen($db->sequence_prefix);

            for ($sequences = [], $table = 0, $tableMax = count($table_names); $table < $tableMax; $table++) {
                if (mb_substr($table_names[$table], 0, $prefix_length) == $db->sequence_prefix) {
                    $sequences[] = mb_substr($table_names[$table], $prefix_length);
                }
            }

            return (1);
        }

        public function GetSequenceDefinition($db, $sequence, &$definition)
        {
            if (!$db->QueryColumn('SHOW TABLES', $table_names)) {
                return (0);
            }

            $prefix_length = mb_strlen($db->sequence_prefix);

            for ($table = 0, $tableMax = count($table_names); $table < $tableMax; $table++) {
                if (mb_substr($table_names[$table], 0, $prefix_length) == $db->sequence_prefix
                    && !strcmp(mb_substr($table_names[$table], $prefix_length), $sequence)) {
                    if (!$db->QueryField('SELECT MAX(sequence) FROM ' . $table_names[$table], $start)) {
                        return (0);
                    }

                    $definition = ['start' => $start + 1];

                    return (1);
                }
            }

            return ($db->SetError('Get sequence definition', 'it was not specified an existing sequence'));
        }

        public function CreateSequence($db, $name, $start)
        {
            if (!$this->VerifyTransactionalTableType($db, $db->default_table_type)) {
                return (0);
            }

            if (!$db->Query("CREATE TABLE _sequence_$name (sequence INT DEFAULT 0 NOT NULL AUTO_INCREMENT, PRIMARY KEY (sequence))" . (mb_strlen($db->default_table_type) ? ' TYPE=' . $db->default_table_type : ''))) {
                return (0);
            }

            if (1 == $start
                || $db->Query("INSERT INTO _sequence_$name (sequence) VALUES (" . ($start - 1) . ')')) {
                return (1);
            }

            $error = $db->Error();

            if (!$db->Query("DROP TABLE _sequence_$name")) {
                $db->warning = 'could not drop inconsistent sequence table';
            }

            return ($db->SetError('Create sequence', $error));
        }

        public function DropSequence($db, $name)
        {
            return ($db->Query("DROP TABLE _sequence_$name"));
        }

        public function GetSequenceCurrentValue($db, $name, &$value)
        {
            return ($db->QueryField('SELECT MAX(sequence) FROM ' . $db->sequence_prefix . $name, $value, 'integer'));
        }

        public function CreateIndex($db, $table, $name, &$definition)
        {
            $query = "ALTER TABLE $table ADD " . (isset($definition['unique']) ? 'UNIQUE' : 'INDEX') . " $name (";

            for ($field = 0, reset($definition['FIELDS']), $fieldMax = count($definition['FIELDS']); $field < $fieldMax; $field++, next($definition['FIELDS'])) {
                if ($field > 0) {
                    $query .= ',';
                }

                $query .= key($definition['FIELDS']);
            }

            $query .= ')';

            return ($db->Query($query));
        }

        public function DropIndex($db, $table, $name)
        {
            return ($db->Query("ALTER TABLE $table DROP INDEX $name"));
        }
    }
}
