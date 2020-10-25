<?php
/*
 * metabase_manager.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/metabase/metabase_manager.php,v 1.71 2003/01/06 05:02:24 mlemos Exp $
 *
 */

class metabase_manager_class
{
    public $fail_on_invalid_names = 1;

    public $error = '';

    public $warnings = [];

    public $database = 0;

    public $database_definition = [
        'name' => '',
        'create' => 0,
        'TABLES' => [],
    ];

    public function SetupDatabase($arguments)
    {
        if (isset($arguments['Debug'])) {
            $this->debug = $arguments['Debug'];
        }

        if (strcmp($error = MetabaseSetupDatabase($arguments, $this->database), '')) {
            return ($error);
        }

        if (!isset($arguments['Debug'])) {
            MetabaseCaptureDebugOutput($this->database, 1);
        }

        return ('');
    }

    public function CloseSetup()
    {
        if (0 != $this->database) {
            MetabaseCloseSetup($this->database);
        }
    }

    public function GetField(&$field, $field_name, $declaration, &$query)
    {
        if (!strcmp($field_name, '')) {
            return ("it was not specified a valid field name (\"$field_name\")");
        }

        switch ($field['type']) {
            case 'integer':
                if ($declaration) {
                    $query = MetabaseGetIntegerFieldTypeDeclaration($this->database, $field_name, $field);
                } else {
                    $query = $field_name;
                }
                break;
            case 'text':
                if ($declaration) {
                    $query = MetabaseGetTextFieldTypeDeclaration($this->database, $field_name, $field);
                } else {
                    $query = $field_name;
                }
                break;
            case 'clob':
                if ($declaration) {
                    $query = MetabaseGetCLOBFieldTypeDeclaration($this->database, $field_name, $field);
                } else {
                    $query = $field_name;
                }
                break;
            case 'blob':
                if ($declaration) {
                    $query = MetabaseGetBLOBFieldTypeDeclaration($this->database, $field_name, $field);
                } else {
                    $query = $field_name;
                }
                break;
            case 'boolean':
                if ($declaration) {
                    $query = MetabaseGetBooleanFieldTypeDeclaration($this->database, $field_name, $field);
                } else {
                    $query = $field_name;
                }
                break;
            case 'date':
                if ($declaration) {
                    $query = MetabaseGetDateFieldTypeDeclaration($this->database, $field_name, $field);
                } else {
                    $query = $field_name;
                }
                break;
            case 'timestamp':
                if ($declaration) {
                    $query = MetabaseGetTimestampFieldTypeDeclaration($this->database, $field_name, $field);
                } else {
                    $query = $field_name;
                }
                break;
            case 'time':
                if ($declaration) {
                    $query = MetabaseGetTimeFieldTypeDeclaration($this->database, $field_name, $field);
                } else {
                    $query = $field_name;
                }
                break;
            case 'float':
                if ($declaration) {
                    $query = MetabaseGetFloatFieldTypeDeclaration($this->database, $field_name, $field);
                } else {
                    $query = $field_name;
                }
                break;
            case 'decimal':
                if ($declaration) {
                    $query = MetabaseGetDecimalFieldTypeDeclaration($this->database, $field_name, $field);
                } else {
                    $query = $field_name;
                }
                break;
            default:
                return ('type "' . $field['type'] . '" is not yet supported');
        }

        return ('');
    }

    public function GetFieldList($fields, $declaration, &$query_fields)
    {
        for ($query_fields = '', reset($fields), $field_number = 0, $field_numberMax = count($fields); $field_number < $field_numberMax; $field_number++, next($fields)) {
            if ($field_number > 0) {
                $query_fields .= ',';
            }

            $field_name = key($fields);

            if (strcmp($error = $this->GetField($fields[$field_name], $field_name, $declaration, $query), '')) {
                return ($error);
            }

            $query_fields .= $query;
        }

        return ('');
    }

    public function GetFields($table, &$fields)
    {
        return ($this->GetFieldList($this->database_definition['TABLES'][$table]['FIELDS'], 0, $fields));
    }

    public function CreateTable($table_name, $table)
    {
        MetabaseDebug($this->database, 'Create table: ' . $table_name);

        if (!MetabaseCreateTable($this->database, $table_name, $table['FIELDS'])) {
            return (MetabaseError($this->database));
        }

        $success = 1;

        $error = '';

        if (isset($table['initialization'])) {
            $instructions = $table['initialization'];

            for (reset($instructions), $instruction = 0; $success && $instruction < count($instructions); $instruction++, next($instructions)) {
                switch ($instructions[$instruction]['type']) {
                    case 'insert':
                        $fields = $instructions[$instruction]['FIELDS'];
                        for ($query_fields = $query_values = '', reset($fields), $field_number = 0, $field_numberMax = count($fields); $field_number < $field_numberMax; $field_number++, next($fields)) {
                            if ($field_number > 0) {
                                $query_fields .= ',';

                                $query_values .= ',';
                            }

                            $field_name = key($fields);

                            $field = $table['FIELDS'][$field_name];

                            if (strcmp($error = $this->GetField($field, $field_name, 0, $query), '')) {
                                return ($error);
                            }

                            $query_fields .= $query;

                            $query_values .= '?';
                        }
                        if (($success = ($prepared_query = MetabasePrepareQuery($this->database, "INSERT INTO $table_name ($query_fields) VALUES ($query_values)")))) {
                            for ($lobs = [], reset($fields), $field_number = 0, $field_numberMax = count($fields); $field_number < $field_numberMax; $field_number++, next($fields)) {
                                $field_name = key($fields);

                                $field = $table['FIELDS'][$field_name];

                                if (strcmp($error = $this->GetField($field, $field_name, 0, $query), '')) {
                                    return ($error);
                                }

                                switch ($field['type']) {
                                    case 'integer':
                                        $success = MetabaseQuerySetInteger($this->database, $prepared_query, $field_number + 1, (int)$fields[$field_name]);
                                        break;
                                    case 'text':
                                        $success = MetabaseQuerySetText($this->database, $prepared_query, $field_number + 1, $fields[$field_name]);
                                        break;
                                    case 'clob':
                                        $lob_definition = [
                                            'Database' => $this->database,
                                            'Error' => '',
                                            'Data' => $fields[$field_name],
                                        ];
                                        $lob = count($lobs);
                                        if (!($success = MetabaseCreateLOB($lob_definition, $lobs[$lob]))) {
                                            $error = $lob_definition['Error'];

                                            break;
                                        }
                                        $success = MetabaseQuerySetCLOB($this->database, $prepared_query, $field_number + 1, $lobs[$lob], $field_name);
                                        break;
                                    case 'blob':
                                        $lob_definition = [
                                            'Database' => $this->database,
                                            'Error' => '',
                                            'Data' => $fields[$field_name],
                                        ];
                                        $lob = count($lobs);
                                        if (!($success = MetabaseCreateLOB($lob_definition, $lobs[$lob]))) {
                                            $error = $lob_definition['Error'];

                                            break;
                                        }
                                        $success = MetabaseQuerySetBLOB($this->database, $prepared_query, $field_number + 1, $lobs[$lob], $field_name);
                                        break;
                                    case 'boolean':
                                        $success = MetabaseQuerySetBoolean($this->database, $prepared_query, $field_number + 1, (int)$fields[$field_name]);
                                        break;
                                    case 'date':
                                        $success = MetabaseQuerySetDate($this->database, $prepared_query, $field_number + 1, $fields[$field_name]);
                                        break;
                                    case 'timestamp':
                                        $success = MetabaseQuerySetTimestamp($this->database, $prepared_query, $field_number + 1, $fields[$field_name]);
                                        break;
                                    case 'time':
                                        $success = MetabaseQuerySetTime($this->database, $prepared_query, $field_number + 1, $fields[$field_name]);
                                        break;
                                    case 'float':
                                        $success = MetabaseQuerySetFloat($this->database, $prepared_query, $field_number + 1, (float)$fields[$field_name]);
                                        break;
                                    case 'decimal':
                                        $success = MetabaseQuerySetDecimal($this->database, $prepared_query, $field_number + 1, $fields[$field_name]);
                                        break;
                                    default:
                                        $error = 'type "' . $field['type'] . '" is not yet supported';
                                        $success = 0;
                                        break;
                                }

                                if (!$success
                                    && '' == $error) {
                                    $error = MetabaseError($this->database);

                                    break;
                                }
                            }

                            if ($success
                                && !($success = MetabaseExecuteQuery($this->database, $prepared_query))) {
                                $error = MetabaseError($this->database);
                            }

                            for ($lob = 0, $lobMax = count($lobs); $lob < $lobMax; $lob++) {
                                MetabaseDestroyLOB($lobs[$lob]);
                            }

                            MetabaseFreePreparedQuery($this->database, $prepared_query);
                        } else {
                            $error = MetabaseError($this->database);
                        }
                        break;
                }
            }
        }

        if ($success
            && isset($table['INDEXES'])) {
            if (!MetabaseSupport($this->database, 'Indexes')) {
                return ('indexes are not supported');
            }

            $indexes = $table['INDEXES'];

            for ($index = 0, reset($indexes), $indexMax = count($indexes); $index < $indexMax; next($indexes), $index++) {
                if (!MetabaseCreateIndex($this->database, $table_name, key($indexes), $indexes[key($indexes)])) {
                    $error = MetabaseError($this->database);

                    $success = 0;

                    break;
                }
            }
        }

        if (!$success) {
            if (!MetabaseDropTable($this->database, $table_name)) {
                $error = "could not initialize the table \"$table_name\" ($error) and then could not drop the table (" . MetabaseError($this->database) . ')';
            }
        }

        return ($error);
    }

    public function DropTable($table_name)
    {
        return (MetabaseDropTable($this->database, $table_name) ? '' : MetabaseError($this->database));
    }

    public function CreateSequence($sequence_name, $sequence, $created_on_table)
    {
        if (!MetabaseSupport($this->database, 'Sequences')) {
            return ('sequences are not supported');
        }

        MetabaseDebug($this->database, 'Create sequence: ' . $sequence_name);

        if (!isset($sequence_name)
            || !strcmp($sequence_name, '')) {
            return ('it was not specified a valid sequence name');
        }

        $start = $sequence['start'];

        if (isset($sequence['on'])
            && !$created_on_table) {
            $table = $sequence['on']['table'];

            $field = $sequence['on']['field'];

            if (MetabaseSupport($this->database, 'SummaryFunctions')) {
                $field = "MAX($field)";
            }

            if (!($result = MetabaseQuery($this->database, "SELECT $field FROM $table"))) {
                return (MetabaseError($this->database));
            }

            if (($rows = MetabaseNumberOfRows($this->database, $result))) {
                for ($row = 0; $row < $rows; $row++) {
                    if (!MetabaseResultIsNull($this->database, $result, $row, 0)
                        && ($value = MetabaseFetchResult($this->database, $result, $row, 0) + 1) > $start) {
                        $start = $value;
                    }
                }
            }

            MetabaseFreeResult($this->database, $result);
        }

        if (!MetabaseCreateSequence($this->database, $sequence_name, $start)) {
            return (MetabaseError($this->database));
        }

        return ('');
    }

    public function DropSequence($sequence_name)
    {
        return (MetabaseDropSequence($this->database, $sequence_name) ? '' : MetabaseError($this->database));
    }

    public function CreateDatabase()
    {
        if (!isset($this->database_definition['name'])
            || !strcmp($this->database_definition['name'], '')) {
            return ('it was not specified a valid database name');
        }

        $create = (isset($this->database_definition['create']) && $this->database_definition['create']);

        if ($create) {
            MetabaseDebug($this->database, 'Create database: ' . $this->database_definition['name']);

            if (!MetabaseCreateDatabase($this->database, $this->database_definition['name'])) {
                $error = MetabaseError($this->database);

                MetabaseDebug($this->database, 'Create database error: ' . $error);

                return ($error);
            }
        }

        $previous_database_name = MetabaseSetDatabase($this->database, $this->database_definition['name']);

        if (($support_transactions = MetabaseSupport($this->database, 'Transactions'))
            && !MetabaseAutoCommitTransactions($this->database, 0)) {
            return (MetabaseError($this->database));
        }

        $created_objects = 0;

        for ($error = '', reset($this->database_definition['TABLES']), $table = 0, $tableMax = count($this->database_definition['TABLES']); $table < $tableMax; next($this->database_definition['TABLES']), $table++) {
            $table_name = key($this->database_definition['TABLES']);

            if (strcmp($error = $this->CreateTable($table_name, $this->database_definition['TABLES'][$table_name]), '')) {
                break;
            }

            $created_objects++;
        }

        if (!strcmp($error, '')
            && isset($this->database_definition['SEQUENCES'])) {
            for ($error = '', reset($this->database_definition['SEQUENCES']), $sequence = 0, $sequenceMax = count($this->database_definition['SEQUENCES']); $sequence < $sequenceMax; next($this->database_definition['SEQUENCES']), $sequence++) {
                $sequence_name = key($this->database_definition['SEQUENCES']);

                if (strcmp($error = $this->CreateSequence($sequence_name, $this->database_definition['SEQUENCES'][$sequence_name], 1), '')) {
                    break;
                }

                $created_objects++;
            }
        }

        if (strcmp($error, '')) {
            if ($created_objects) {
                if ($support_transactions) {
                    if (!MetabaseRollbackTransaction($this->database)) {
                        $error = 'Could not rollback the partially created database alterations: Rollback error: ' . MetabaseError($this->database) . " Creation error: $error";
                    }
                } else {
                    $error = "the database was only partially created: $error";
                }
            }
        } else {
            if ($support_transactions) {
                if (!MetabaseAutoCommitTransactions($this->database, 1)) {
                    $error = 'Could not end transaction after successfully created the database: ' . MetabaseError($this->database);
                }
            }
        }

        MetabaseSetDatabase($this->database, $previous_database_name);

        if (strcmp($error, '')
            && $create
            && !MetabaseDropDatabase($this->database, $this->database_definition['name'])) {
            $error = 'Could not drop the created database after unsuccessful creation attempt: ' . MetabaseError($this->database) . ' Creation error: ' . $error;
        }

        return ($error);
    }

    public function AddDefinitionChange(&$changes, $definition, $item, $change)
    {
        if (!isset($changes[$definition][$item])) {
            $changes[$definition][$item] = [];
        }

        for ($change_number = 0, reset($change), $change_numberMax = count($change); $change_number < $change_numberMax; next($change), $change_number++) {
            $name = key($change);

            if (!strcmp(gettype($change[$name]), 'array')) {
                if (!isset($changes[$definition][$item][$name])) {
                    $changes[$definition][$item][$name] = [];
                }

                $change_parts = $change[$name];

                for ($change_part = 0, reset($change_parts), $change_partMax = count($change_parts); $change_part < $change_partMax; next($change_parts), $change_part++) {
                    $changes[$definition][$item][$name][key($change_parts)] = $change_parts[key($change_parts)];
                }
            } else {
                $changes[$definition][$item][key($change)] = $change[key($change)];
            }
        }
    }

    public function CompareDefinitions(&$previous_definition, &$changes)
    {
        $changes = [];

        for ($defined_tables = [], reset($this->database_definition['TABLES']), $table = 0, $tableMax = count($this->database_definition['TABLES']); $table < $tableMax; next($this->database_definition['TABLES']), $table++) {
            $table_name = key($this->database_definition['TABLES']);

            $was_table_name = $this->database_definition['TABLES'][$table_name]['was'];

            if (isset($previous_definition['TABLES'][$table_name])
                && isset($previous_definition['TABLES'][$table_name]['was'])
                && !strcmp($previous_definition['TABLES'][$table_name]['was'], $was_table_name)) {
                $was_table_name = $table_name;
            }

            if (isset($previous_definition['TABLES'][$was_table_name])) {
                if (strcmp($was_table_name, $table_name)) {
                    $this->AddDefinitionChange($changes, 'TABLES', $was_table_name, ['name' => $table_name]);

                    MetabaseDebug($this->database, "Renamed table '$was_table_name' to '$table_name'");
                }

                if (isset($defined_tables[$was_table_name])) {
                    return ("the table '$was_table_name' was specified as base of more than of table of the database");
                }

                $defined_tables[$was_table_name] = 1;

                $fields = $this->database_definition['TABLES'][$table_name]['FIELDS'];

                $previous_fields = $previous_definition['TABLES'][$was_table_name]['FIELDS'];

                for ($defined_fields = [], reset($fields), $field = 0, $fieldMax = count($fields); $field < $fieldMax; next($fields), $field++) {
                    $field_name = key($fields);

                    $was_field_name = $fields[$field_name]['was'];

                    if (isset($previous_fields[$field_name])
                        && isset($previous_fields[$field_name]['was'])
                        && !strcmp($previous_fields[$field_name]['was'], $was_field_name)) {
                        $was_field_name = $field_name;
                    }

                    if (isset($previous_fields[$was_field_name])) {
                        if (strcmp($was_field_name, $field_name)) {
                            $field_declaration = $fields[$field_name];

                            if (strcmp($error = $this->GetField($field_declaration, $field_name, 1, $query), '')) {
                                return ($error);
                            }

                            $this->AddDefinitionChange(
                                $changes,
                                'TABLES',
                                $was_table_name,
                                [
                                    'RenamedFields' => [
                                        $was_field_name => [
                                            'name' => $field_name,
                                            'Declaration' => $query,
                                        ],
                                    ],
                                ]
                            );

                            MetabaseDebug($this->database, "Renamed field '$was_field_name' to '$field_name' in table '$table_name'");
                        }

                        if (isset($defined_fields[$was_field_name])) {
                            return ("the field '$was_field_name' was specified as base of more than one field of table '$table_name'");
                        }

                        $defined_fields[$was_field_name] = 1;

                        $change = [];

                        if (!strcmp($fields[$field_name]['type'], $previous_fields[$was_field_name]['type'])) {
                            switch ($fields[$field_name]['type']) {
                                case 'integer':
                                    $previous_unsigned = isset($previous_fields[$was_field_name]['unsigned']);
                                    $unsigned = isset($fields[$field_name]['unsigned']);
                                    if (strcmp($previous_unsigned, $unsigned)) {
                                        $change['unsigned'] = $unsigned;

                                        MetabaseDebug(
                                            $this->database,
                                            "Changed field '$field_name' type from '" . ($previous_unsigned ? 'unsigned ' : '') . $previous_fields[$was_field_name]['type'] . "' to '" . ($unsigned ? 'unsigned ' : '') . $fields[$field_name]['type'] . "' in table '$table_name'"
                                        );
                                    }
                                    break;
                                case 'text':
                                case 'clob':
                                case 'blob':
                                    $previous_length = ($previous_fields[$was_field_name]['length'] ?? 0);
                                    $length = ($fields[$field_name]['length'] ?? 0);
                                    if (strcmp($previous_length, $length)) {
                                        $change['length'] = $length;

                                        MetabaseDebug(
                                            $this->database,
                                            "Changed field '$field_name' length from '"
                                            . $previous_fields[$was_field_name]['type']
                                            . (0 == $previous_length ? ' no length' : "($previous_length)")
                                            . "' to '"
                                            . $fields[$field_name]['type']
                                            . (0 == $length ? ' no length' : "($length)")
                                            . "' in table '$table_name'"
                                        );
                                    }
                                    break;
                                case 'date':
                                case 'timestamp':
                                case 'time':
                                case 'boolean':
                                case 'float':
                                case 'decimal':
                                    break;
                                default:
                                    return ('type "' . $fields[$field_name]['type'] . '" is not yet supported');
                            }

                            $previous_notnull = isset($previous_fields[$was_field_name]['notnull']);

                            $notnull = isset($fields[$field_name]['notnull']);

                            if ($previous_notnull != $notnull) {
                                $change['ChangedNotNull'] = 1;

                                if ($notnull) {
                                    $change['notnull'] = isset($fields[$field_name]['notnull']);
                                }

                                MetabaseDebug($this->database, "Changed field '$field_name' notnull from $previous_notnull to $notnull in table '$table_name'");
                            }

                            $previous_default = isset($previous_fields[$was_field_name]['default']);

                            $default = isset($fields[$field_name]['default']);

                            if (strcmp($previous_default, $default)) {
                                $change['ChangedDefault'] = 1;

                                if ($default) {
                                    $change['default'] = $fields[$field_name]['default'];
                                }

                                MetabaseDebug(
                                    $this->database,
                                    "Changed field '$field_name' default from " . ($previous_default ? "'" . $previous_fields[$was_field_name]['default'] . "'" : 'NULL') . ' TO ' . ($default ? "'" . $fields[$field_name]['default'] . "'" : 'NULL') . " IN TABLE '$table_name'"
                                );
                            } else {
                                if ($default
                                    && strcmp($previous_fields[$was_field_name]['default'], $fields[$field_name]['default'])) {
                                    $change['ChangedDefault'] = 1;

                                    $change['default'] = $fields[$field_name]['default'];

                                    MetabaseDebug($this->database, "Changed field '$field_name' default from '" . $previous_fields[$was_field_name]['default'] . "' to '" . $fields[$field_name]['default'] . "' in table '$table_name'");
                                }
                            }
                        } else {
                            $change['type'] = $fields[$field_name]['type'];

                            MetabaseDebug($this->database, "Changed field '$field_name' type from '" . $previous_fields[$was_field_name]['type'] . "' to '" . $fields[$field_name]['type'] . "' in table '$table_name'");
                        }

                        if (count($change)) {
                            $field_declaration = $fields[$field_name];

                            if (strcmp($error = $this->GetField($field_declaration, $field_name, 1, $query), '')) {
                                return ($error);
                            }

                            $change['Declaration'] = $query;

                            $change['Definition'] = $field_declaration;

                            $this->AddDefinitionChange($changes, 'TABLES', $was_table_name, ['ChangedFields' => [$field_name => $change]]);
                        }
                    } else {
                        if (strcmp($field_name, $was_field_name)) {
                            return ("it was specified a previous field name ('$was_field_name') for field '$field_name' of table '$table_name' that does not exist");
                        }

                        $field_declaration = $fields[$field_name];

                        if (strcmp($error = $this->GetField($field_declaration, $field_name, 1, $query), '')) {
                            return ($error);
                        }

                        $field_declaration['Declaration'] = $query;

                        $this->AddDefinitionChange($changes, 'TABLES', $table_name, ['AddedFields' => [$field_name => $field_declaration]]);

                        MetabaseDebug($this->database, "Added field '$field_name' to table '$table_name'");
                    }
                }

                for (reset($previous_fields), $field = 0, $fieldMax = count($previous_fields); $field < $fieldMax; next($previous_fields), $field++) {
                    $field_name = key($previous_fields);

                    if (!isset($defined_fields[$field_name])) {
                        $this->AddDefinitionChange($changes, 'TABLES', $table_name, ['RemovedFields' => [$field_name => []]]);

                        MetabaseDebug($this->database, "Removed field '$field_name' from table '$table_name'");
                    }
                }

                $indexes = ($this->database_definition['TABLES'][$table_name]['INDEXES'] ?? []);

                $previous_indexes = ($previous_definition['TABLES'][$was_table_name]['INDEXES'] ?? []);

                for ($defined_indexes = [], reset($indexes), $index = 0, $indexMax = count($indexes); $index < $indexMax; next($indexes), $index++) {
                    $index_name = key($indexes);

                    $was_index_name = $indexes[$index_name]['was'];

                    if (isset($previous_indexes[$index_name])
                        && isset($previous_indexes[$index_name]['was'])
                        && !strcmp($previous_indexes[$index_name]['was'], $was_index_name)) {
                        $was_index_name = $index_name;
                    }

                    if (isset($previous_indexes[$was_index_name])) {
                        $change = [];

                        if (strcmp($was_index_name, $index_name)) {
                            $change['name'] = $was_index_name;

                            MetabaseDebug($this->database, "Changed index '$was_index_name' name to '$index_name' in table '$table_name'");
                        }

                        if (isset($defined_indexes[$was_index_name])) {
                            return ("the index '$was_index_name' was specified as base of more than one index of table '$table_name'");
                        }

                        $defined_indexes[$was_index_name] = 1;

                        $previous_unique = isset($previous_indexes[$was_index_name]['unique']);

                        $unique = isset($indexes[$index_name]['unique']);

                        if ($previous_unique != $unique) {
                            $change['ChangedUnique'] = 1;

                            if ($unique) {
                                $change['unique'] = $unique;
                            }

                            MetabaseDebug($this->database, "Changed index '$index_name' unique from $previous_unique to $unique in table '$table_name'");
                        }

                        $fields = $indexes[$index_name]['FIELDS'];

                        $previous_fields = $previous_indexes[$was_index_name]['FIELDS'];

                        for ($defined_fields = [], reset($fields), $field = 0, $fieldMax = count($fields); $field < $fieldMax; next($fields), $field++) {
                            $field_name = key($fields);

                            if (isset($previous_fields[$field_name])) {
                                $defined_fields[$field_name] = 1;

                                $sorting = ($fields[$field_name]['sorting'] ?? '');

                                $previous_sorting = ($previous_fields[$field_name]['sorting'] ?? '');

                                if (strcmp($sorting, $previous_sorting)) {
                                    MetabaseDebug($this->database, "Changed index field '$field_name' sorting default from '$previous_sorting' to '$sorting' in table '$table_name'");

                                    $change['ChangedFields'] = 1;
                                }
                            } else {
                                $change['ChangedFields'] = 1;

                                MetabaseDebug($this->database, "Added field '$field_name' to index '$index_name' of table '$table_name'");
                            }
                        }

                        for (reset($previous_fields), $field = 0, $fieldMax = count($previous_fields); $field < $fieldMax; next($previous_fields), $field++) {
                            $field_name = key($previous_fields);

                            if (!isset($defined_fields[$field_name])) {
                                $change['ChangedFields'] = 1;

                                MetabaseDebug($this->database, "Removed field '$field_name' from index '$index_name' of table '$table_name'");
                            }
                        }

                        if (count($change)) {
                            $this->AddDefinitionChange($changes, 'INDEXES', $table_name, ['ChangedIndexes' => [$index_name => $change]]);
                        }
                    } else {
                        if (strcmp($index_name, $was_index_name)) {
                            return ("it was specified a previous index name ('$was_index_name') for index '$index_name' of table '$table_name' that does not exist");
                        }

                        $this->AddDefinitionChange($changes, 'INDEXES', $table_name, ['AddedIndexes' => [$index_name => $indexes[$index_name]]]);

                        MetabaseDebug($this->database, "Added index '$index_name' to table '$table_name'");
                    }
                }

                for (reset($previous_indexes), $index = 0, $indexMax = count($previous_indexes); $index < $indexMax; next($previous_indexes), $index++) {
                    $index_name = key($previous_indexes);

                    if (!isset($defined_indexes[$index_name])) {
                        $this->AddDefinitionChange($changes, 'INDEXES', $table_name, ['RemovedIndexes' => [$index_name => 1]]);

                        MetabaseDebug($this->database, "Removed index '$index_name' from table '$table_name'");
                    }
                }
            } else {
                if (strcmp($table_name, $was_table_name)) {
                    return ("it was specified a previous table name ('$was_table_name') for table '$table_name' that does not exist");
                }

                $this->AddDefinitionChange($changes, 'TABLES', $table_name, ['Add' => 1]);

                MetabaseDebug($this->database, "Added table '$table_name'");
            }
        }

        for (reset($previous_definition['TABLES']), $table = 0, $tableMax = count($previous_definition['TABLES']); $table < $tableMax; next($previous_definition['TABLES']), $table++) {
            $table_name = key($previous_definition['TABLES']);

            if (!isset($defined_tables[$table_name])) {
                $this->AddDefinitionChange($changes, 'TABLES', $table_name, ['Remove' => 1]);

                MetabaseDebug($this->database, "Removed table '$table_name'");
            }
        }

        if (isset($this->database_definition['SEQUENCES'])) {
            for ($defined_sequences = [], reset($this->database_definition['SEQUENCES']), $sequence = 0, $sequenceMax = count($this->database_definition['SEQUENCES']); $sequence < $sequenceMax; next($this->database_definition['SEQUENCES']), $sequence++) {
                $sequence_name = key($this->database_definition['SEQUENCES']);

                $was_sequence_name = $this->database_definition['SEQUENCES'][$sequence_name]['was'];

                if (isset($previous_definition['SEQUENCES'][$sequence_name])
                    && isset($previous_definition['SEQUENCES'][$sequence_name]['was'])
                    && !strcmp($previous_definition['SEQUENCES'][$sequence_name]['was'], $was_sequence_name)) {
                    $was_sequence_name = $sequence_name;
                }

                if (isset($previous_definition['SEQUENCES'][$was_sequence_name])) {
                    if (strcmp($was_sequence_name, $sequence_name)) {
                        $this->AddDefinitionChange($changes, 'SEQUENCES', $was_sequence_name, ['name' => $sequence_name]);

                        MetabaseDebug($this->database, "Renamed sequence '$was_sequence_name' to '$sequence_name'");
                    }

                    if (isset($defined_sequences[$was_sequence_name])) {
                        return ("the sequence '$was_sequence_name' was specified as base of more than of sequence of the database");
                    }

                    $defined_sequences[$was_sequence_name] = 1;

                    $change = [];

                    if (strcmp($this->database_definition['SEQUENCES'][$sequence_name]['start'], $previous_definition['SEQUENCES'][$was_sequence_name]['start'])) {
                        $change['start'] = $this->database_definition['SEQUENCES'][$sequence_name]['start'];

                        MetabaseDebug($this->database, "Changed sequence '$sequence_name' start from '" . $previous_definition['SEQUENCES'][$was_sequence_name]['start'] . "' to '" . $this->database_definition['SEQUENCES'][$sequence_name]['start'] . "'");
                    }

                    if (strcmp($this->database_definition['SEQUENCES'][$sequence_name]['on']['table'], $previous_definition['SEQUENCES'][$was_sequence_name]['on']['table'])
                        || strcmp($this->database_definition['SEQUENCES'][$sequence_name]['on']['field'], $previous_definition['SEQUENCES'][$was_sequence_name]['on']['field'])) {
                        $change['on'] = $this->database_definition['SEQUENCES'][$sequence_name]['on'];

                        MetabaseDebug(
                            $this->database,
                            "Changed sequence '$sequence_name' on table field from '"
                            . $previous_definition['SEQUENCES'][$was_sequence_name]['on']['table']
                            . '.'
                            . $previous_definition['SEQUENCES'][$was_sequence_name]['on']['field']
                            . "' to '"
                            . $this->database_definition['SEQUENCES'][$sequence_name]['on']['table']
                            . '.'
                            . $this->database_definition['SEQUENCES'][$sequence_name]['on']['field']
                            . "'"
                        );
                    }

                    if (count($change)) {
                        $this->AddDefinitionChange($changes, 'SEQUENCES', $was_sequence_name, ['Change' => [$sequence_name => [$change]]]);
                    }
                } else {
                    if (strcmp($sequence_name, $was_sequence_name)) {
                        return ("it was specified a previous sequence name ('$was_sequence_name') for sequence '$sequence_name' that does not exist");
                    }

                    $this->AddDefinitionChange($changes, 'SEQUENCES', $sequence_name, ['Add' => 1]);

                    MetabaseDebug($this->database, "Added sequence '$sequence_name'");
                }
            }
        }

        if (isset($previous_definition['SEQUENCES'])) {
            for (reset($previous_definition['SEQUENCES']), $sequence = 0, $sequenceMax = count($previous_definition['SEQUENCES']); $sequence < $sequenceMax; next($previous_definition['SEQUENCES']), $sequence++) {
                $sequence_name = key($previous_definition['SEQUENCES']);

                if (!isset($defined_sequences[$sequence_name])) {
                    $this->AddDefinitionChange($changes, 'SEQUENCES', $sequence_name, ['Remove' => 1]);

                    MetabaseDebug($this->database, "Removed sequence '$sequence_name'");
                }
            }
        }

        return ('');
    }

    public function AlterDatabase(&$previous_definition, &$changes)
    {
        if (isset($changes['TABLES'])) {
            for ($change = 0, reset($changes['TABLES']), $changeMax = count($changes['TABLES']); $change < $changeMax; next($changes['TABLES']), $change++) {
                $table_name = key($changes['TABLES']);

                if (isset($changes['TABLES'][$table_name]['Add'])
                    || isset($changes['TABLES'][$table_name]['Remove'])) {
                    continue;
                }

                if (!MetabaseAlterTable($this->database, $table_name, $changes['TABLES'][$table_name], 1)) {
                    return ('database driver is not able to perform the requested alterations: ' . MetabaseError($this->database));
                }
            }
        }

        if (isset($changes['SEQUENCES'])) {
            if (!MetabaseSupport($this->database, 'Sequences')) {
                return ('sequences are not supported');
            }

            for ($change = 0, reset($changes['SEQUENCES']), $changeMax = count($changes['SEQUENCES']); $change < $changeMax; next($changes['SEQUENCES']), $change++) {
                $sequence_name = key($changes['SEQUENCES']);

                if (isset($changes['SEQUENCES'][$sequence_name]['Add'])
                    || isset($changes['SEQUENCES'][$sequence_name]['Remove'])
                    || isset($changes['SEQUENCES'][$sequence_name]['Change'])) {
                    continue;
                }

                return ('some sequences changes are not yet supported');
            }
        }

        if (isset($changes['INDEXES'])) {
            if (!MetabaseSupport($this->database, 'Indexes')) {
                return ('indexes are not supported');
            }

            for ($change = 0, reset($changes['INDEXES']), $changeMax = count($changes['INDEXES']); $change < $changeMax; next($changes['INDEXES']), $change++) {
                $table_name = key($changes['INDEXES']);

                $table_changes = count($changes['INDEXES'][$table_name]);

                if (isset($changes['INDEXES'][$table_name]['AddedIndexes'])) {
                    $table_changes--;
                }

                if (isset($changes['INDEXES'][$table_name]['RemovedIndexes'])) {
                    $table_changes--;
                }

                if (isset($changes['INDEXES'][$table_name]['ChangedIndexes'])) {
                    $table_changes--;
                }

                if ($table_changes) {
                    return ('index alteration not yet supported');
                }
            }
        }

        $previous_database_name = MetabaseSetDatabase($this->database, $this->database_definition['name']);

        if (($support_transactions = MetabaseSupport($this->database, 'Transactions'))
            && !MetabaseAutoCommitTransactions($this->database, 0)) {
            return (MetabaseError($this->database));
        }

        $error = '';

        $alterations = 0;

        if (isset($changes['INDEXES'])) {
            for ($change = 0, reset($changes['INDEXES']), $changeMax = count($changes['INDEXES']); $change < $changeMax; next($changes['INDEXES']), $change++) {
                $table_name = key($changes['INDEXES']);

                if (isset($changes['INDEXES'][$table_name]['RemovedIndexes'])) {
                    $indexes = $changes['INDEXES'][$table_name]['RemovedIndexes'];

                    for ($index = 0, reset($indexes), $indexMax = count($indexes); $index < $indexMax; next($indexes), $index++) {
                        if (!MetabaseDropIndex($this->database, $table_name, key($indexes))) {
                            $error = MetabaseError($this->database);

                            break;
                        }

                        $alterations++;
                    }
                }

                if (!strcmp($error, '')
                    && isset($changes['INDEXES'][$table_name]['ChangedIndexes'])) {
                    $indexes = $changes['INDEXES'][$table_name]['ChangedIndexes'];

                    for ($index = 0, reset($indexes), $indexMax = count($indexes); $index < $indexMax; next($indexes), $index++) {
                        $name = key($indexes);

                        $was_name = ($indexes[$name]['name'] ?? $name);

                        if (!MetabaseDropIndex($this->database, $table_name, $was_name)) {
                            $error = MetabaseError($this->database);

                            break;
                        }

                        $alterations++;
                    }
                }

                if (strcmp($error, '')) {
                    break;
                }
            }
        }

        if (!strcmp($error, '')
            && isset($changes['TABLES'])) {
            for ($change = 0, reset($changes['TABLES']), $changeMax = count($changes['TABLES']); $change < $changeMax; next($changes['TABLES']), $change++) {
                $table_name = key($changes['TABLES']);

                if (isset($changes['TABLES'][$table_name]['Remove'])) {
                    if (!strcmp($error = $this->DropTable($table_name), '')) {
                        $alterations++;
                    }
                } else {
                    if (!isset($changes['TABLES'][$table_name]['Add'])) {
                        if (!MetabaseAlterTable($this->database, $table_name, $changes['TABLES'][$table_name], 0)) {
                            $error = MetabaseError($this->database);
                        } else {
                            $alterations++;
                        }
                    }
                }

                if (strcmp($error, '')) {
                    break;
                }
            }

            for ($change = 0, reset($changes['TABLES']), $changeMax = count($changes['TABLES']); $change < $changeMax; next($changes['TABLES']), $change++) {
                $table_name = key($changes['TABLES']);

                if (isset($changes['TABLES'][$table_name]['Add'])) {
                    if (!strcmp($error = $this->CreateTable($table_name, $this->database_definition['TABLES'][$table_name]), '')) {
                        $alterations++;
                    }
                }

                if (strcmp($error, '')) {
                    break;
                }
            }
        }

        if (!strcmp($error, '')
            && isset($changes['SEQUENCES'])) {
            for ($change = 0, reset($changes['SEQUENCES']), $changeMax = count($changes['SEQUENCES']); $change < $changeMax; next($changes['SEQUENCES']), $change++) {
                $sequence_name = key($changes['SEQUENCES']);

                if (isset($changes['SEQUENCES'][$sequence_name]['Add'])) {
                    $created_on_table = 0;

                    if (isset($this->database_definition['SEQUENCES'][$sequence_name]['on'])) {
                        $table = $this->database_definition['SEQUENCES'][$sequence_name]['on']['table'];

                        if (isset($changes['TABLES'])
                            && isset($changes['TABLES'][$table_name])
                            && isset($changes['TABLES'][$table_name]['Add'])) {
                            $created_on_table = 1;
                        }
                    }

                    if (!strcmp($error = $this->CreateSequence($sequence_name, $this->database_definition['SEQUENCES'][$sequence_name], $created_on_table), '')) {
                        $alterations++;
                    }
                } else {
                    if (isset($changes['SEQUENCES'][$sequence_name]['Remove'])) {
                        if (!strcmp($error = $this->DropSequence($sequence_name), '')) {
                            $alterations++;
                        }
                    } else {
                        if (isset($changes['SEQUENCES'][$sequence_name]['Change'])) {
                            $created_on_table = 0;

                            if (isset($this->database_definition['SEQUENCES'][$sequence_name]['on'])) {
                                $table = $this->database_definition['SEQUENCES'][$sequence_name]['on']['table'];

                                if (isset($changes['TABLES'])
                                    && isset($changes['TABLES'][$table_name])
                                    && isset($changes['TABLES'][$table_name]['Add'])) {
                                    $created_on_table = 1;
                                }
                            }

                            if (!strcmp($error = $this->DropSequence($this->database_definition['SEQUENCES'][$sequence_name]['was']), '')
                                && !strcmp($error = $this->CreateSequence($sequence_name, $this->database_definition['SEQUENCES'][$sequence_name], $created_on_table), '')) {
                                $alterations++;
                            }
                        } else {
                            $error = 'changing sequences is not yet supported';
                        }
                    }
                }

                if (strcmp($error, '')) {
                    break;
                }
            }
        }

        if (!strcmp($error, '')
            && isset($changes['INDEXES'])) {
            for ($change = 0, reset($changes['INDEXES']), $changeMax = count($changes['INDEXES']); $change < $changeMax; next($changes['INDEXES']), $change++) {
                $table_name = key($changes['INDEXES']);

                if (isset($changes['INDEXES'][$table_name]['ChangedIndexes'])) {
                    $indexes = $changes['INDEXES'][$table_name]['ChangedIndexes'];

                    for ($index = 0, reset($indexes), $indexMax = count($indexes); $index < $indexMax; next($indexes), $index++) {
                        if (!MetabaseCreateIndex($this->database, $table_name, key($indexes), $this->database_definition['TABLES'][$table_name]['INDEXES'][key($indexes)])) {
                            $error = MetabaseError($this->database);

                            break;
                        }

                        $alterations++;
                    }
                }

                if (!strcmp($error, '')
                    && isset($changes['INDEXES'][$table_name]['AddedIndexes'])) {
                    $indexes = $changes['INDEXES'][$table_name]['AddedIndexes'];

                    for ($index = 0, reset($indexes), $indexMax = count($indexes); $index < $indexMax; next($indexes), $index++) {
                        if (!MetabaseCreateIndex($this->database, $table_name, key($indexes), $this->database_definition['TABLES'][$table_name]['INDEXES'][key($indexes)])) {
                            $error = MetabaseError($this->database);

                            break;
                        }

                        $alterations++;
                    }
                }

                if (strcmp($error, '')) {
                    break;
                }
            }
        }

        if ($alterations
            && strcmp($error, '')) {
            if ($support_transactions) {
                if (!MetabaseRollbackTransaction($this->database)) {
                    $error = 'Could not rollback the partially implemented the requested database alterations: Rollback error: ' . MetabaseError($this->database) . " Alterations error: $error";
                }
            } else {
                $error = "the requested database alterations were only partially implemented: $error";
            }
        }

        if ($support_transactions) {
            if (!MetabaseAutoCommitTransactions($this->database, 1)) {
                $this->warnings[] = 'Could not end transaction after successfully implemented the requested database alterations: ' . MetabaseError($this->database);
            }
        }

        MetabaseSetDatabase($this->database, $previous_database_name);

        return ($error);
    }

    public function EscapeSpecialCharacters($string)
    {
        if ('string' != gettype($string)) {
            $string = (string)$string;
        }

        for ($escaped = '', $character = 0, $characterMax = mb_strlen($string); $character < $characterMax; $character++) {
            switch ($string[$character]) {
                case '"':
                case '>':
                case '<':
                    $code = ord($string[$character]);
                    break;
                default:
                    $code = ord($string[$character]);
                    if ($code < 32
                        || $code > 127) {
                        break;
                    }
                    $escaped .= $string[$character];
                    continue 2;
            }

            $escaped .= "&#$code;";
        }

        return ($escaped);
    }

    public function DumpSequence($sequence_name, $output, $eol, $dump_definition)
    {
        $sequence_definition = $this->database_definition['SEQUENCES'][$sequence_name];

        if ($dump_definition) {
            $start = $sequence_definition['start'];
        } else {
            if (MetabaseSupport($this->database, 'GetSequenceCurrentValue')) {
                if (!MetabaseGetSequenceCurrentValue($this->database, $sequence_name, $start)) {
                    return (0);
                }

                $start++;
            } else {
                if (!MetabaseGetSequenceNextValue($this->database, $sequence_name, $start)) {
                    return (0);
                }

                $this->warnings[] = 'database does not support getting current sequence value, the sequence value was incremented';
            }
        }

        $output("$eol <sequence>$eol  <name>$sequence_name</name>$eol  <start>$start</start>$eol");

        if (isset($sequence_definition['on'])) {
            $output("  <on>$eol   <table>" . $sequence_definition['on']['table'] . "</table>$eol   <field>" . $sequence_definition['on']['field'] . "</field>$eol  </on>$eol");
        }

        $output(" </sequence>$eol");

        return (1);
    }

    public function DumpDatabase($arguments)
    {
        if (!isset($arguments['Output'])) {
            return ('it was not specified a valid output function');
        }

        $output = $arguments['Output'];

        $eol = ($arguments['EndOfLine'] ?? "\n");

        $dump_definition = isset($arguments['Definition']);

        $sequences = [];

        if (isset($this->database_definition['SEQUENCES'])) {
            for ($error = '', reset($this->database_definition['SEQUENCES']), $sequence = 0, $sequenceMax = count($this->database_definition['SEQUENCES']); $sequence < $sequenceMax; next($this->database_definition['SEQUENCES']), $sequence++) {
                $sequence_name = key($this->database_definition['SEQUENCES']);

                if (isset($this->database_definition['SEQUENCES'][$sequence_name]['on'])) {
                    $table = $this->database_definition['SEQUENCES'][$sequence_name]['on']['table'];
                } else {
                    $table = '';
                }

                $sequences[$table][] = $sequence_name;
            }
        }

        $previous_database_name = (strcmp($this->database_definition['name'], '') ? MetabaseSetDatabase($this->database, $this->database_definition['name']) : '');

        $output("<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>$eol");

        $output("<database>$eol$eol <name>" . $this->database_definition['name'] . "</name>$eol <create>" . $this->database_definition['create'] . "</create>$eol");

        for ($error = '', reset($this->database_definition['TABLES']), $table = 0, $tableMax = count($this->database_definition['TABLES']); $table < $tableMax; next($this->database_definition['TABLES']), $table++) {
            $table_name = key($this->database_definition['TABLES']);

            $output("$eol <table>$eol$eol  <name>$table_name</name>$eol");

            $output("$eol  <declaration>$eol");

            $fields = $this->database_definition['TABLES'][$table_name]['FIELDS'];

            for (reset($fields), $field_number = 0, $field_numberMax = count($fields); $field_number < $field_numberMax; $field_number++, next($fields)) {
                $field_name = key($fields);

                $field = $fields[$field_name];

                if (!isset($field['type'])) {
                    return ("it was not specified the type of the field \"$field_name\" of the table \"$table_name\"");
                }

                $output("$eol   <field>$eol    <name>$field_name</name>$eol    <type>" . $field['type'] . "</type>$eol");

                switch ($field['type']) {
                    case 'integer':
                        if (isset($field['unsigned'])) {
                            $output("    <unsigned>1</unsigned>$eol");
                        }
                        break;
                    case 'text':
                    case 'clob':
                    case 'blob':
                        if (isset($field['length'])) {
                            $output('    <length>' . $field['length'] . "</length>$eol");
                        }
                        break;
                    case 'boolean':
                    case 'date':
                    case 'timestamp':
                    case 'time':
                    case 'float':
                    case 'decimal':
                        break;
                    default:
                        return ('type "' . $field['type'] . '" is not yet supported');
                }

                if (isset($field['notnull'])) {
                    $output("    <notnull>1</notnull>$eol");
                }

                if (isset($field['default'])) {
                    $output('    <default>' . $this->EscapeSpecialCharacters($field['default']) . "</default>$eol");
                }

                $output("   </field>$eol");
            }

            if (isset($this->database_definition['TABLES'][$table_name]['INDEXES'])) {
                $indexes = $this->database_definition['TABLES'][$table_name]['INDEXES'];

                for (reset($indexes), $index_number = 0, $index_numberMax = count($indexes); $index_number < $index_numberMax; $index_number++, next($indexes)) {
                    $index_name = key($indexes);

                    $index = $indexes[$index_name];

                    $output("$eol   <index>$eol    <name>$index_name</name>$eol");

                    if (isset($indexes[$index_name]['unique'])) {
                        $output("    <unique>1</unique>$eol");
                    }

                    for (reset($index['FIELDS']), $field_number = 0, $field_numberMax = count($index['FIELDS']); $field_number < $field_numberMax; $field_number++, next($index['FIELDS'])) {
                        $field_name = key($index['FIELDS']);

                        $field = $index['FIELDS'][$field_name];

                        $output("    <field>$eol     <name>$field_name</name>$eol");

                        if (isset($field['sorting'])) {
                            $output('     <sorting>' . $field['sorting'] . "</sorting>$eol");
                        }

                        $output("    </field>$eol");
                    }

                    $output("   </index>$eol");
                }
            }

            $output("$eol  </declaration>$eol");

            if ($dump_definition) {
                if (isset($this->database_definition['TABLES'][$table_name]['initialization'])) {
                    $output("$eol  <initialization>$eol");

                    $instructions = $this->database_definition['TABLES'][$table_name]['initialization'];

                    for (reset($instructions), $instruction = 0, $instructionMax = count($instructions); $instruction < $instructionMax; $instruction++, next($instructions)) {
                        switch ($instructions[$instruction]['type']) {
                            case 'insert':
                                $output("$eol   <insert>$eol");
                                $fields = $instructions[$instruction]['FIELDS'];
                                for (reset($fields), $field_number = 0, $field_numberMax = count($fields); $field_number < $field_numberMax; $field_number++, next($fields)) {
                                    $field_name = key($fields);

                                    $output("$eol    <field>$eol     <name>$field_name</name>$eol     <value>" . $this->EscapeSpecialCharacters($fields[$field_name]) . "</value>$eol    </field>$eol");
                                }
                                $output("$eol   </insert>$eol");
                                break;
                        }
                    }

                    $output("$eol  </initialization>$eol");
                }
            } else {
                if (0 == count($this->database_definition['TABLES'][$table_name]['FIELDS'])) {
                    return ("the definition of the table \"$table_name\" does not contain any fields");
                }

                if (strcmp($error = $this->GetFields($table_name, $query_fields), '')) {
                    return ($error);
                }

                if (($support_summary_functions = MetabaseSupport($this->database, 'SummaryFunctions'))) {
                    if (0 == ($result = MetabaseQuery($this->database, "SELECT COUNT(*) FROM $table_name"))) {
                        return (MetabaseError($this->database));
                    }

                    $rows = MetabaseFetchResult($this->database, $result, 0, 0);

                    MetabaseFreeResult($this->database, $result);
                }

                if (0 == ($result = MetabaseQuery($this->database, "SELECT $query_fields FROM $table_name"))) {
                    return (MetabaseError($this->database));
                }

                if (!$support_summary_functions) {
                    $rows = MetabaseNumberOfRows($this->database, $result);
                }

                if ($rows > 0) {
                    $output("$eol  <initialization>$eol");

                    for ($row = 0; $row < $rows; $row++) {
                        $output("$eol   <insert>$eol");

                        for (reset($fields), $field_number = 0, $field_numberMax = count($fields); $field_number < $field_numberMax; $field_number++, next($fields)) {
                            $field_name = key($fields);

                            if (!MetabaseResultIsNull($this->database, $result, $row, $field_name)) {
                                $field = $fields[$field_name];

                                $output("$eol    <field>$eol     <name>$field_name</name>$eol     <value>");

                                switch ($field['type']) {
                                    case 'integer':
                                    case 'text':
                                        $output($this->EscapeSpecialCharacters(MetabaseFetchResult($this->database, $result, $row, $field_name)));
                                        break;
                                    case 'clob':
                                        if (!($lob = MetabaseFetchCLOBResult($this->database, $result, $row, $field_name))) {
                                            return (MetabaseError($this->database));
                                        }
                                        while (!MetabaseEndOfLOB($lob)) {
                                            if (MetabaseReadLOB($lob, $data, 8000) < 0) {
                                                return (MetabaseLOBError($lob));
                                            }

                                            $output($this->EscapeSpecialCharacters($data));
                                        }
                                        MetabaseDestroyLOB($lob);
                                        break;
                                    case 'blob':
                                        if (!($lob = MetabaseFetchBLOBResult($this->database, $result, $row, $field_name))) {
                                            return (MetabaseError($this->database));
                                        }
                                        while (!MetabaseEndOfLOB($lob)) {
                                            if (MetabaseReadLOB($lob, $data, 8000) < 0) {
                                                return (MetabaseLOBError($lob));
                                            }

                                            $output(bin2hex($data));
                                        }
                                        MetabaseDestroyLOB($lob);
                                        break;
                                    case 'float':
                                        $output($this->EscapeSpecialCharacters(MetabaseFetchFloatResult($this->database, $result, $row, $field_name)));
                                        break;
                                    case 'decimal':
                                        $output($this->EscapeSpecialCharacters(MetabaseFetchDecimalResult($this->database, $result, $row, $field_name)));
                                        break;
                                    case 'boolean':
                                        $output($this->EscapeSpecialCharacters(MetabaseFetchBooleanResult($this->database, $result, $row, $field_name)));
                                        break;
                                    case 'date':
                                        $output($this->EscapeSpecialCharacters(MetabaseFetchDateResult($this->database, $result, $row, $field_name)));
                                        break;
                                    case 'timestamp':
                                        $output($this->EscapeSpecialCharacters(MetabaseFetchTimestampResult($this->database, $result, $row, $field_name)));
                                        break;
                                    case 'time':
                                        $output($this->EscapeSpecialCharacters(MetabaseFetchTimeResult($this->database, $result, $row, $field_name)));
                                        break;
                                    default:
                                        return ('type "' . $field['type'] . '" is not yet supported');
                                }

                                $output("</value>$eol    </field>$eol");
                            }
                        }

                        $output("$eol   </insert>$eol");
                    }

                    $output("$eol  </initialization>$eol");
                }

                MetabaseFreeResult($this->database, $result);
            }

            $output("$eol </table>$eol");

            if (isset($sequences[$table_name])) {
                for ($sequence = 0, $sequenceMax = count($sequences[$table_name]); $sequence < $sequenceMax; $sequence++) {
                    if (!$this->DumpSequence($sequences[$table_name][$sequence], $output, $eol, $dump_definition)) {
                        return (MetabaseError($this->database));
                    }
                }
            }
        }

        if (isset($sequences[''])) {
            for ($sequence = 0, $sequenceMax = count($sequences['']); $sequence < $sequenceMax; $sequence++) {
                if (!$this->DumpSequence($sequences[''][$sequence], $output, $eol, $dump_definition)) {
                    return (MetabaseError($this->database));
                }
            }
        }

        $output("$eol</database>$eol");

        if (strcmp($previous_database_name, '')) {
            MetabaseSetDatabase($this->database, $previous_database_name);
        }

        return ($error);
    }

    public function ParseDatabaseDefinitionFile($input_file, &$database_definition, $variables, $fail_on_invalid_names = 1)
    {
        if (!($file = fopen($input_file, 'rb'))) {
            return ("Could not open input file \"$input_file\"");
        }

        $parser = new metabase_parser_class();

        $parser->variables = $variables;

        $parser->fail_on_invalid_names = $fail_on_invalid_names;

        if (strcmp($error = $parser->ParseStream($file), '')) {
            $error .= ' Line ' . $parser->error_line . ' Column ' . $parser->error_column . ' Byte index ' . $parser->error_byte_index;
        } else {
            $database_definition = $parser->database;
        }

        fclose($file);

        return ($error);
    }

    public function DumpDatabaseChanges(&$changes)
    {
        if (isset($changes['TABLES'])) {
            for ($change = 0, reset($changes['TABLES']), $changeMax = count($changes['TABLES']); $change < $changeMax; next($changes['TABLES']), $change++) {
                $table_name = key($changes['TABLES']);

                MetabaseDebug($this->database, "$table_name:");

                if (isset($changes['tables'][$table_name]['Add'])) {
                    MetabaseDebug($this->database, "\tAdded table '$table_name'");
                } else {
                    if (isset($changes['TABLES'][$table_name]['Remove'])) {
                        MetabaseDebug($this->database, "\tRemoved table '$table_name'");
                    } else {
                        if (isset($changes['TABLES'][$table_name]['name'])) {
                            MetabaseDebug($this->database, "\tRenamed table '$table_name' to '" . $changes['TABLES'][$table_name]['name'] . "'");
                        }

                        if (isset($changes['TABLES'][$table_name]['AddedFields'])) {
                            $fields = $changes['TABLES'][$table_name]['AddedFields'];

                            for ($field = 0, reset($fields), $fieldMax = count($fields); $field < $fieldMax; $field++, next($fields)) {
                                MetabaseDebug($this->database, "\tAdded field '" . key($fields) . "'");
                            }
                        }

                        if (isset($changes['TABLES'][$table_name]['RemovedFields'])) {
                            $fields = $changes['TABLES'][$table_name]['RemovedFields'];

                            for ($field = 0, reset($fields), $fieldMax = count($fields); $field < $fieldMax; $field++, next($fields)) {
                                MetabaseDebug($this->database, "\tRemoved field '" . key($fields) . "'");
                            }
                        }

                        if (isset($changes['TABLES'][$table_name]['RenamedFields'])) {
                            $fields = $changes['TABLES'][$table_name]['RenamedFields'];

                            for ($field = 0, reset($fields), $fieldMax = count($fields); $field < $fieldMax; $field++, next($fields)) {
                                MetabaseDebug($this->database, "\tRenamed field '" . key($fields) . "' to '" . $fields[key($fields)]['name'] . "'");
                            }
                        }

                        if (isset($changes['TABLES'][$table_name]['ChangedFields'])) {
                            $fields = $changes['TABLES'][$table_name]['ChangedFields'];

                            for ($field = 0, reset($fields), $fieldMax = count($fields); $field < $fieldMax; $field++, next($fields)) {
                                $field_name = key($fields);

                                if (isset($fields[$field_name]['type'])) {
                                    MetabaseDebug($this->database, "\tChanged field '$field_name' type to '" . $fields[$field_name]['type'] . "'");
                                }

                                if (isset($fields[$field_name]['unsigned'])) {
                                    MetabaseDebug($this->database, "\tChanged field '$field_name' type to '" . ($fields[$field_name]['unsigned'] ? '' : 'not ') . "unsigned'");
                                }

                                if (isset($fields[$field_name]['length'])) {
                                    MetabaseDebug($this->database, "\tChanged field '$field_name' length to '" . (0 == $fields[$field_name]['length'] ? 'no length' : $fields[$field_name]['length']) . "'");
                                }

                                if (isset($fields[$field_name]['ChangedDefault'])) {
                                    MetabaseDebug($this->database, "\tChanged field '$field_name' default to " . (isset($fields[$field_name]['default']) ? "'" . $fields[$field_name]['default'] . "'" : 'NULL'));
                                }

                                if (isset($fields[$field_name]['ChangedNotNull'])) {
                                    MetabaseDebug($this->database, "\tChanged field '$field_name' notnull to " . (isset($fields[$field_name]['notnull']) ? "'1'" : '0'));
                                }
                            }
                        }
                    }
                }
            }
        }

        if (isset($changes['SEQUENCES'])) {
            for ($change = 0, reset($changes['SEQUENCES']), $changeMax = count($changes['SEQUENCES']); $change < $changeMax; next($changes['SEQUENCES']), $change++) {
                $sequence_name = key($changes['SEQUENCES']);

                MetabaseDebug($this->database, "$sequence_name:");

                if (isset($changes['SEQUENCES'][$sequence_name]['Add'])) {
                    MetabaseDebug($this->database, "\tAdded sequence '$sequence_name'");
                } else {
                    if (isset($changes['SEQUENCES'][$sequence_name]['Remove'])) {
                        MetabaseDebug($this->database, "\tRemoved sequence '$sequence_name'");
                    } else {
                        if (isset($changes['SEQUENCES'][$sequence_name]['name'])) {
                            MetabaseDebug($this->database, "\tRenamed sequence '$sequence_name' to '" . $changes['SEQUENCES'][$sequence_name]['name'] . "'");
                        }

                        if (isset($changes['SEQUENCES'][$sequence_name]['Change'])) {
                            $sequences = $changes['SEQUENCES'][$sequence_name]['Change'];

                            for ($sequence = 0, reset($sequences), $sequenceMax = count($sequences); $sequence < $sequenceMax; $sequence++, next($sequences)) {
                                $sequence_name = key($sequences);

                                if (isset($sequences[$sequence_name]['start'])) {
                                    MetabaseDebug($this->database, "\tChanged sequence '$sequence_name' start to '" . $sequences[$sequence_name]['start'] . "'");
                                }
                            }
                        }
                    }
                }
            }
        }

        if (isset($changes['INDEXES'])) {
            for ($change = 0, reset($changes['INDEXES']), $changeMax = count($changes['INDEXES']); $change < $changeMax; next($changes['INDEXES']), $change++) {
                $table_name = key($changes['INDEXES']);

                MetabaseDebug($this->database, "$table_name:");

                if (isset($changes['INDEXES'][$table_name]['AddedIndexes'])) {
                    $indexes = $changes['INDEXES'][$table_name]['AddedIndexes'];

                    for ($index = 0, reset($indexes), $indexMax = count($indexes); $index < $indexMax; next($indexes), $index++) {
                        MetabaseDebug($this->database, "\tAdded index '" . key($indexes) . "' of table '$table_name'");
                    }
                }

                if (isset($changes['INDEXES'][$table_name]['RemovedIndexes'])) {
                    $indexes = $changes['INDEXES'][$table_name]['RemovedIndexes'];

                    for ($index = 0, reset($indexes), $indexMax = count($indexes); $index < $indexMax; next($indexes), $index++) {
                        MetabaseDebug($this->database, "\tRemoved index '" . key($indexes) . "' of table '$table_name'");
                    }
                }

                if (isset($changes['INDEXES'][$table_name]['ChangedIndexes'])) {
                    $indexes = $changes['INDEXES'][$table_name]['ChangedIndexes'];

                    for ($index = 0, reset($indexes), $indexMax = count($indexes); $index < $indexMax; next($indexes), $index++) {
                        if (isset($indexes[key($indexes)]['name'])) {
                            MetabaseDebug($this->database, "\tRenamed index '" . key($indexes) . "' to '" . $indexes[key($indexes)]['name'] . "' on table '$table_name'");
                        }

                        if (isset($indexes[key($indexes)]['ChangedUnique'])) {
                            MetabaseDebug($this->database, "\tChanged index '" . key($indexes) . "' unique to '" . isset($indexes[key($indexes)]['unique']) . "' on table '$table_name'");
                        }

                        if (isset($indexes[key($indexes)]['ChangedFields'])) {
                            MetabaseDebug($this->database, "\tChanged index '" . key($indexes) . "' on table '$table_name'");
                        }
                    }
                }
            }
        }
    }

    public function UpdateDatabase($current_schema_file, $previous_schema_file, $arguments, $variables)
    {
        if (strcmp($error = $this->ParseDatabaseDefinitionFile($current_schema_file, $this->database_definition, $variables, $this->fail_on_invalid_names), '')) {
            $this->error = "Could not parse database schema file: $error";

            return (0);
        }

        if (strcmp($error = $this->SetupDatabase($arguments), '')) {
            $this->error = "Could not setup database: $error";

            return (0);
        }

        $copy = 0;

        if (file_exists($previous_schema_file)) {
            if (!strcmp($error = $this->ParseDatabaseDefinitionFile($previous_schema_file, $database_definition, $variables, 0), '')
                && !strcmp($error = $this->CompareDefinitions($database_definition, $changes), '')
                && count($changes)) {
                if (!strcmp($error = $this->AlterDatabase($database_definition, $changes), '')) {
                    $copy = 1;

                    $this->DumpDatabaseChanges($changes);
                }
            }
        } else {
            if (!strcmp($error = $this->CreateDatabase(), '')) {
                $copy = 1;
            }
        }

        if (strcmp($error, '')) {
            $this->error = "Could not install database: $error";

            return (0);
        }

        if ($copy
            && !copy($current_schema_file, $previous_schema_file)) {
            $this->error = 'could not copy the new database definition file to the current file';

            return (0);
        }

        return (1);
    }

    public function DumpDatabaseContents($schema_file, $setup_arguments, $dump_arguments, $variables)
    {
        if (strcmp($error = $this->ParseDatabaseDefinitionFile($schema_file, $database_definition, $variables, $this->fail_on_invalid_names), '')) {
            return ("Could not parse database schema file: $error");
        }

        $this->database_definition = $database_definition;

        if (strcmp($error = $this->SetupDatabase($setup_arguments), '')) {
            return ("Could not setup database: $error");
        }

        return ($this->DumpDatabase($dump_arguments));
    }

    public function GetDefinitionFromDatabase($arguments)
    {
        if (strcmp($error = $this->SetupDatabase($arguments), '')) {
            return ($this->error = "Could not setup database: $error");
        }

        MetabaseSetDatabase($this->database, $database = MetabaseSetDatabase($this->database, ''));

        if (0 == mb_strlen($database)) {
            return ('it was not specified a valid database name');
        }

        $this->database_definition = [
            'name' => $database,
            'create' => 1,
            'TABLES' => [],
        ];

        if (!MetabaseListTables($this->database, $tables)) {
            return (MetabaseError($this->database));
        }

        for ($table = 0, $tableMax = count($tables); $table < $tableMax; $table++) {
            $table_name = $tables[$table];

            if (!MetabaseListTableFields($this->database, $table_name, $fields)) {
                return (MetabaseError($this->database));
            }

            $this->database_definition['TABLES'][$table_name] = [
                'FIELDS' => [],
            ];

            for ($field = 0, $fieldMax = count($fields); $field < $fieldMax; $field++) {
                $field_name = $fields[$field];

                if (!MetabaseGetTableFieldDefinition($this->database, $table_name, $field_name, $definition)) {
                    return (MetabaseError($this->database));
                }

                $this->database_definition['TABLES'][$table_name]['FIELDS'][$field_name] = $definition[0];
            }

            if (!MetabaseListTableIndexes($this->database, $table_name, $indexes)) {
                return (MetabaseError($this->database));
            }

            if (count($indexes)) {
                $this->database_definition['TABLES'][$table_name]['INDEXES'] = [];

                for ($index = 0, $indexMax = count($indexes); $index < $indexMax; $index++) {
                    $index_name = $indexes[$index];

                    if (!MetabaseGetTableIndexDefinition($this->database, $table_name, $index_name, $definition)) {
                        return (MetabaseError($this->database));
                    }

                    $this->database_definition['TABLES'][$table_name]['INDEXES'][$index_name] = $definition;
                }
            }
        }

        if (!MetabaseListSequences($this->database, $sequences)) {
            return (MetabaseError($this->database));
        }

        for ($sequence = 0, $sequenceMax = count($sequences); $sequence < $sequenceMax; $sequence++) {
            $sequence_name = $sequences[$sequence];

            if (!MetabaseGetSequenceDefinition($this->database, $sequence_name, $definition)) {
                return (MetabaseError($this->database));
            }

            $this->database_definition['SEQUENCES'][$sequence_name] = $definition;
        }

        return ('');
    }
}

