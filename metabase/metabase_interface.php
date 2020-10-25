<?php
/*
 * metabase_interface.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/metabase/metabase_interface.php,v 1.68 2002/12/11 22:52:24 mlemos Exp $
 *
 */

function MetabaseSetupDatabase($arguments, &$database)
{
    global $metabase_databases;

    $database = count($metabase_databases) + 1;

    if (strcmp($error = MetabaseSetupInterface($arguments, $metabase_databases[$database]), '')) {
        unset($metabase_databases[$database]);

        $database = 0;
    } else {
        $metabase_databases[$database]->database = $database;
    }

    return ($error);
}

function MetabaseQuery($database, $query)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->Query($query));
}

function MetabaseQueryField($database, $query, &$field, $type = 'text')
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QueryField($query, $field, $type));
}

function MetabaseQueryRow($database, $query, &$row, $types = '')
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QueryRow($query, $row, $types));
}

function MetabaseQueryColumn($database, $query, &$column, $type = 'text')
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QueryColumn($query, $column, $type));
}

function MetabaseQueryAll($database, $query, &$all, $types = '')
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QueryAll($query, $all, $types));
}

function MetabaseReplace($database, $table, &$fields)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->Replace($table, $fields));
}

function MetabasePrepareQuery($database, $query)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->PrepareQuery($query));
}

function MetabaseFreePreparedQuery($database, $prepared_query)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FreePreparedQuery($prepared_query));
}

function MetabaseExecuteQuery($database, $prepared_query)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->ExecuteQuery($prepared_query));
}

function MetabaseQuerySet($database, $prepared_query, $parameter, $type, $value, $is_null = 0, $field = '')
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QuerySet($prepared_query, $parameter, $type, $value, $is_null, $field));
}

function MetabaseQuerySetNull($database, $prepared_query, $parameter, $type)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QuerySetNull($prepared_query, $parameter, $type));
}

function MetabaseQuerySetText($database, $prepared_query, $parameter, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QuerySetText($prepared_query, $parameter, $value));
}

function MetabaseQuerySetCLOB($database, $prepared_query, $parameter, $value, $field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QuerySetCLOB($prepared_query, $parameter, $value, $field));
}

function MetabaseQuerySetBLOB($database, $prepared_query, $parameter, $value, $field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QuerySetBLOB($prepared_query, $parameter, $value, $field));
}

function MetabaseQuerySetInteger($database, $prepared_query, $parameter, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QuerySetInteger($prepared_query, $parameter, $value));
}

function MetabaseQuerySetBoolean($database, $prepared_query, $parameter, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QuerySetBoolean($prepared_query, $parameter, $value));
}

function MetabaseQuerySetDate($database, $prepared_query, $parameter, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QuerySetDate($prepared_query, $parameter, $value));
}

function MetabaseQuerySetTimestamp($database, $prepared_query, $parameter, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QuerySetTimestamp($prepared_query, $parameter, $value));
}

function MetabaseQuerySetTime($database, $prepared_query, $parameter, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QuerySetTime($prepared_query, $parameter, $value));
}

function MetabaseQuerySetFloat($database, $prepared_query, $parameter, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QuerySetFloat($prepared_query, $parameter, $value));
}

function MetabaseQuerySetDecimal($database, $prepared_query, $parameter, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->QuerySetDecimal($prepared_query, $parameter, $value));
}

function MetabaseAffectedRows($database, &$affected_rows)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->AffectedRows($affected_rows));
}

function MetabaseFetchResult($database, $result, $row, $field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchResult($result, $row, $field));
}

function MetabaseFetchCLOBResult($database, $result, $row, $field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchCLOBResult($result, $row, $field));
}

function MetabaseFetchBLOBResult($database, $result, $row, $field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchBLOBResult($result, $row, $field));
}

function MetabaseDestroyResultLOB($database, $lob)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->DestroyResultLOB($lob));
}

function MetabaseEndOfResultLOB($database, $lob)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->EndOfResultLOB($lob));
}

function MetabaseReadResultLOB($database, $lob, &$data, $length)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->ReadResultLOB($lob, $data, $length));
}

function MetabaseResultIsNull($database, $result, $row, $field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->ResultIsNull($result, $row, $field));
}

function MetabaseFetchDateResult($database, $result, $row, $field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchDateResult($result, $row, $field));
}

function MetabaseFetchTimestampResult($database, $result, $row, $field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchTimestampResult($result, $row, $field));
}

function MetabaseFetchTimeResult($database, $result, $row, $field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchTimeResult($result, $row, $field));
}

function MetabaseFetchBooleanResult($database, $result, $row, $field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchBooleanResult($result, $row, $field));
}

function MetabaseFetchFloatResult($database, $result, $row, $field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchFloatResult($result, $row, $field));
}

function MetabaseFetchDecimalResult($database, $result, $row, $field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchDecimalResult($result, $row, $field));
}

function MetabaseFetchResultField($database, $result, &$field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchResultField($result, $field));
}

function MetabaseFetchResultArray($database, $result, &$array, $row)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchResultArray($result, $array, $row));
}

function MetabaseFetchResultRow($database, $result, &$row)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchResultRow($result, $row));
}

function MetabaseFetchResultColumn($database, $result, &$column)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchResultColumn($result, $column));
}

function MetabaseFetchResultAll($database, $result, &$all)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FetchResultAll($result, $all));
}

function MetabaseNumberOfRows($database, $result)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->NumberOfRows($result));
}

function MetabaseNumberOfColumns($database, $result)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->NumberOfColumns($result));
}

function MetabaseGetColumnNames($database, $result, &$column_names)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetColumnNames($result, $column_names));
}

function MetabaseSetResultTypes($database, $result, &$types)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->SetResultTypes($result, $types));
}

function MetabaseFreeResult($database, $result)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->FreeResult($result));
}

function MetabaseError($database)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->Error());
}

function MetabaseSetErrorHandler($database, $function)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->SetErrorHandler($function));
}

function MetabaseCreateDatabase($database, $name)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->CreateDatabase($name));
}

function MetabaseDropDatabase($database, $name)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->DropDatabase($name));
}

function MetabaseSetDatabase($database, $name)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->SetDatabase($name));
}

function MetabaseGetIntegerFieldTypeDeclaration($database, $name, &$field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetIntegerFieldTypeDeclaration($name, $field));
}

function MetabaseGetTextFieldTypeDeclaration($database, $name, &$field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetTextFieldTypeDeclaration($name, $field));
}

function MetabaseGetCLOBFieldTypeDeclaration($database, $name, &$field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetCLOBFieldTypeDeclaration($name, $field));
}

function MetabaseGetBLOBFieldTypeDeclaration($database, $name, &$field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetBLOBFieldTypeDeclaration($name, $field));
}

function MetabaseGetBooleanFieldTypeDeclaration($database, $name, &$field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetBooleanFieldTypeDeclaration($name, $field));
}

function MetabaseGetDateFieldTypeDeclaration($database, $name, &$field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetDateFieldTypeDeclaration($name, $field));
}

function MetabaseGetTimestampFieldTypeDeclaration($database, $name, &$field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetTimestampFieldTypeDeclaration($name, $field));
}

function MetabaseGetTimeFieldTypeDeclaration($database, $name, &$field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetTimeFieldTypeDeclaration($name, $field));
}

function MetabaseGetFloatFieldTypeDeclaration($database, $name, &$field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetFloatFieldTypeDeclaration($name, $field));
}

function MetabaseGetDecimalFieldTypeDeclaration($database, $name, &$field)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetDecimalFieldTypeDeclaration($name, $field));
}

function MetabaseGetTextFieldValue($database, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetTextFieldValue($value));
}

function MetabaseGetBooleanFieldValue($database, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetBooleanFieldValue($value));
}

function MetabaseGetDateFieldValue($database, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetDateFieldValue($value));
}

function MetabaseGetTimestampFieldValue($database, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetTimestampFieldValue($value));
}

function MetabaseGetTimeFieldValue($database, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetTimeFieldValue($value));
}

function MetabaseGetFloatFieldValue($database, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetFloatFieldValue($value));
}

function MetabaseGetDecimalFieldValue($database, $value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetDecimalFieldValue($value));
}

function MetabaseSupport($database, $feature)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->Support($feature));
}

function MetabaseCreateTable($database, $name, &$fields)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->CreateTable($name, $fields));
}

function MetabaseDropTable($database, $name)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->DropTable($name));
}

function MetabaseAlterTable($database, $name, &$changes, $check = 0)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->AlterTable($name, $changes, $check));
}

function MetabaseListTables($database, &$tables)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->ListTables($tables));
}

function MetabaseListTableFields($database, $table, &$fields)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->ListTableFields($table, $fields));
}

function MetabaseGetTableFieldDefinition($database, $table, $field, &$definition)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetTableFieldDefinition($table, $field, $definition));
}

function MetabaseListTableIndexes($database, $table, &$indexes)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->ListTableIndexes($table, $indexes));
}

function MetabaseGetTableIndexDefinition($database, $table, $index, &$definition)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetTableIndexDefinition($table, $index, $definition));
}

function MetabaseListSequences($database, &$sequences)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->ListSequences($sequences));
}

function MetabaseGetSequenceDefinition($database, $sequence, &$definition)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetSequenceDefinition($sequence, $definition));
}

function MetabaseCreateSequence($database, $name, $start)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->CreateSequence($name, $start));
}

function MetabaseDropSequence($database, $name)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->DropSequence($name));
}

function MetabaseGetSequenceNextValue($database, $name, &$value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetSequenceNextValue($name, $value));
}

function MetabaseGetSequenceCurrentValue($database, $name, &$value)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->GetSequenceCurrentValue($name, $value));
}

function MetabaseAutoCommitTransactions($database, $auto_commit)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->AutoCommitTransactions($auto_commit));
}

function MetabaseCommitTransaction($database)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->CommitTransaction());
}

function MetabaseRollbackTransaction($database)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->RollbackTransaction());
}

function MetabaseCreateIndex($database, $table, $name, $definition)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->CreateIndex($table, $name, $definition));
}

function MetabaseDropIndex($database, $table, $name)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->DropIndex($table, $name));
}

function MetabaseSetSelectedRowRange($database, $first, $limit)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->SetSelectedRowRange($first, $limit));
}

function MetabaseEndOfResult($database, $result)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->EndOfResult($result));
}

function MetabaseCaptureDebugOutput($database, $capture)
{
    global $metabase_databases;

    $metabase_databases[$database]->CaptureDebugOutput($capture);
}

function MetabaseDebugOutput($database)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->DebugOutput());
}

function MetabaseDebug($database, $message)
{
    global $metabase_databases;

    return ($metabase_databases[$database]->Debug($message));
}
