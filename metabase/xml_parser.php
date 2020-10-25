<?php
/*
 * xml_parser.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/PHPlibrary/xml_parser.php,v 1.18 2003/01/14 16:57:32 mlemos Exp $
 *
 */

/*
 * Parser error numbers:
 *
 * 1 - Could not create the XML parser
 * 2 - Could not parse data
 * 3 - Could not read from input stream
 *
 */

$xml_parserHandlers = [];

function xml_parser_start_elementHandler($parser, $name, $attrs)
{
    global $xml_parserHandlers;

    if (!strcmp($xml_parserHandlers[$parser]->error, '')) {
        $xml_parserHandlers[$parser]->StartElement($xml_parserHandlers[$parser], $name, $attrs);
    }
}

function xml_parser_end_elementHandler($parser, $name)
{
    global $xml_parserHandlers;

    if (!strcmp($xml_parserHandlers[$parser]->error, '')) {
        $xml_parserHandlers[$parser]->EndElement($xml_parserHandlers[$parser], $name);
    }
}

function xml_parser_character_dataHandler($parser, $data)
{
    global $xml_parserHandlers;

    if (!strcmp($xml_parserHandlers[$parser]->error, '')) {
        $xml_parserHandlers[$parser]->CharacterData($xml_parserHandlers[$parser], $data);
    }
}

class xml_parserHandler_class
{
    public $xml_parser;

    public $error_number = 0;

    public $error = '';

    public $error_code = 0;

    public $error_line;

    public $error_column;

    public $error_byte_index;

    public $structure = [];

    public $positions = [];

    public $path = '';

    public $store_positions = 0;

    public $simplified_xml = 0;

    public $fail_on_non_simplified_xml = 0;

    public function SetError($object, $error_number, $error)
    {
        $object->error_number = $error_number;

        $object->error = $error;

        $object->error_line = xml_get_current_line_number($object->xml_parser);

        $object->error_column = xml_get_current_column_number($object->xml_parser);

        $object->error_byte_index = xml_get_current_byte_index($object->xml_parser);
    }

    public function SetElementData($object, $path, $data)
    {
        $object->structure[$path] = $data;

        if ($object->store_positions) {
            $object->positions[$path] = [
                'Line' => xml_get_current_line_number($object->xml_parser),
                'Column' => xml_get_current_column_number($object->xml_parser),
                'Byte' => xml_get_current_byte_index($object->xml_parser),
            ];
        }
    }

    public function StartElement($object, $name, $attrs)
    {
        if (strcmp($this->path, '')) {
            $element = $object->structure[$this->path]['Elements'];

            $object->structure[$this->path]['Elements']++;

            $this->path .= ",$element";
        } else {
            $element = 0;

            $this->path = '0';
        }

        $data = [
            'Tag' => $name,
            'Elements' => 0,
        ];

        if ($object->simplified_xml) {
            if ($object->fail_on_non_simplified_xml
                && count($attrs) > 0) {
                $this->SetError($object, 2, 'Simplified XML can not have attributes in tags');

                return;
            }
        } else {
            $data['Attributes'] = $attrs;
        }

        $this->SetElementData($object, $this->path, $data);
    }

    public function EndElement(&$object, $name)
    {
        $this->path = (($position = mb_strrpos($this->path, ',')) ? mb_substr($this->path, 0, $position) : '');
    }

    public function CharacterData($object, $data)
    {
        $element = $object->structure[$this->path]['Elements'];

        $previous = $this->path . ',' . (string)($element - 1);

        if ($element > 0
            && 'string' == gettype($object->structure[$previous])) {
            $object->structure[$previous] .= $data;
        } else {
            $this->SetElementData($object, $this->path . ",$element", $data);

            $object->structure[$this->path]['Elements']++;
        }
    }
}

class xml_parser_class
{
    public $xml_parser = 0;

    public $parserHandler;

    public $error = '';

    public $error_number = 0;

    public $error_line = 0;

    public $error_column = 0;

    public $error_byte_index = 0;

    public $error_code = 0;

    public $stream_buffer_size = 4096;

    public $structure = [];

    public $positions = [];

    public $store_positions = 0;

    public $case_folding = 0;

    public $target_encoding = 'ISO-8859-1';

    public $simplified_xml = 0;

    public $fail_on_non_simplified_xml = 0;

    public function xml_parser_start_elementHandler($parser, $name, $attrs)
    {
        if (!strcmp($this->error, '')) {
            $this->parserHandler->StartElement($this, $name, $attrs);
        }
    }

    public function xml_parser_end_elementHandler($parser, $name)
    {
        if (!strcmp($this->error, '')) {
            $this->parserHandler->EndElement($this, $name);
        }
    }

    public function xml_parser_character_dataHandler($parser, $data)
    {
        if (!strcmp($this->error, '')) {
            $this->parserHandler->CharacterData($this, $data);
        }
    }

    public function SetErrorPosition($error_number, $error, $line, $column, $byte_index)
    {
        $this->error_number = $error_number;

        $this->error = $error;

        $this->error_line = $line;

        $this->error_column = $column;

        $this->error_byte_index = $byte_index;
    }

    public function SetError($error_number, $error)
    {
        $this->error_number = $error_number;

        $this->error = $error;

        if ($this->xml_parser) {
            $line = xml_get_current_line_number($this->xml_parser);

            $column = xml_get_current_column_number($this->xml_parser);

            $byte_index = xml_get_current_byte_index($this->xml_parser);
        } else {
            $line = $column = 1;

            $byte_index = 0;
        }

        $this->SetErrorPosition($error_number, $error, $line, $column, $byte_index);
    }

    public function Parse($data, $end_of_data)
    {
        global $xml_parserHandlers;

        if (strcmp($this->error, '')) {
            return ($this->error);
        }

        if (!$this->xml_parser) {
            if (!function_exists('xml_parser_create')) {
                $this->SetError(1, 'XML support is not available in this PHP configuration');

                return ($this->error);
            }

            if (!($this->xml_parser = xml_parser_create())) {
                $this->SetError(1, 'Could not create the XML parser');

                return ($this->error);
            }

            xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, $this->case_folding);

            xml_parser_set_option($this->xml_parser, XML_OPTION_TARGET_ENCODING, $this->target_encoding);

            if (function_exists('xml_set_object')) {
                xml_set_object($this->xml_parser, $this);

                $this->parserHandler = new xml_parserHandler_class();

                $this->structure = [];

                $this->positions = [];
            } else {
                $xml_parserHandlers[$this->xml_parser] = new xml_parserHandler_class();

                $xml_parserHandlers[$this->xml_parser]->xml_parser = $this->xml_parser;

                $xml_parserHandlers[$this->xml_parser]->store_positions = $this->store_positions;

                $xml_parserHandlers[$this->xml_parser]->simplified_xml = $this->simplified_xml;

                $xml_parserHandlers[$this->xml_parser]->fail_on_non_simplified_xml = $this->fail_on_non_simplified_xml;
            }

            xml_set_elementHandler($this->xml_parser, 'xml_parser_start_elementHandler', 'xml_parser_end_elementHandler');

            xml_set_character_dataHandler($this->xml_parser, 'xml_parser_character_dataHandler');
        }

        $parser_ok = xml_parse($this->xml_parser, $data, $end_of_data);

        if (!function_exists('xml_set_object')) {
            $this->error = $xml_parserHandlers[$this->xml_parser]->error;
        }

        if (!strcmp($this->error, '')) {
            if ($parser_ok) {
                if ($end_of_data) {
                    if (function_exists('xml_set_object')) {
                        unset($this->parserHandler);
                    } else {
                        $this->structure = $xml_parserHandlers[$this->xml_parser]->structure;

                        $this->positions = $xml_parserHandlers[$this->xml_parser]->positions;

                        unset($xml_parserHandlers[$this->xml_parser]);
                    }

                    xml_parser_free($this->xml_parser);

                    $this->xml_parser = 0;
                }
            } else {
                $this->SetError(2, 'Could not parse data: ' . xml_error_string($this->error_code = xml_get_error_code($this->xml_parser)));
            }
        } else {
            if (!function_exists('xml_set_object')) {
                $this->error_number = $xml_parserHandlers[$this->xml_parser]->error_number;

                $this->error_code = $xml_parserHandlers[$this->xml_parser]->error_code;

                $this->error_line = $xml_parserHandlers[$this->xml_parser]->error_line;

                $this->error_column = $xml_parserHandlers[$this->xml_parser]->error_column;

                $this->error_byte_index = $xml_parserHandlers[$this->xml_parser]->error_byte_index;
            }
        }

        return ($this->error);
    }

    public function VerifyWhiteSpace($path)
    {
        if ($this->store_positions) {
            $line = $parser->positions[$path]['Line'];

            $column = $parser->positions[$path]['Column'];

            $byte_index = $parser->positions[$path]['Byte'];
        } else {
            $line = $column = 1;

            $byte_index = 0;
        }

        if (!isset($this->structure[$path])) {
            $this->SetErrorPosition(2, 'element path does not exist', $line, $column, $byte_index);

            return ($this->error);
        }

        if ('string' != gettype($this->structure[$path])) {
            $this->SetErrorPosition(2, 'element is not data', $line, $column, $byte_index);

            return ($this->error);
        }

        $data = $this->structure[$path];

        for ($previous_return = 0, $position = 0, $positionMax = mb_strlen($data); $position < $positionMax; $position++) {
            switch ($data[$position]) {
                case ' ':
                case "\t":
                    $column++;
                    $byte_index++;
                    $previous_return = 0;
                    break;
                case "\n":
                    if (!$previous_return) {
                        $line++;
                    }
                    $column = 1;
                    $byte_index++;
                    $previous_return = 0;
                    break;
                case "\r":
                    $line++;
                    $column = 1;
                    $byte_index++;
                    $previous_return = 1;
                    break;
                default:
                    $this->SetErrorPosition(2, 'data is not white space', $line, $column, $byte_index);

                    return ($this->error);
            }
        }

        return ('');
    }

    public function ParseStream($stream)
    {
        if (strcmp($this->error, '')) {
            return ($this->error);
        }

        do {
            if (!($data = fread($stream, $this->stream_buffer_size))) {
                if (!feof($stream)) {
                    $this->SetError(3, 'Could not read from input stream');

                    break;
                }
            }

            if (strcmp($error = $this->Parse($data, feof($stream)), '')) {
                break;
            }
        } while (!feof($stream));

        return ($this->error);
    }

    public function ParseFile($file)
    {
        if (!file_exists($file)) {
            return ("the XML file to parse ($file) does not exist");
        }

        if (!($definition = fopen($file, 'rb'))) {
            return ("could not open the XML file ($file)");
        }

        $error = $this->ParseStream($definition);

        fclose($definition);

        return ($error);
    }
}

function XMLParseFile(&$parser, $file, $store_positions, $cache = '', $case_folding = 0, $target_encoding = 'ISO-8859-1', $simplified_xml = 0, $fail_on_non_simplified_xml = 0)
{
    if (!file_exists($file)) {
        return ("the XML file to parse ($file) does not exist");
    }

    if (strcmp($cache, '')) {
        if (file_exists($cache)
            && filemtime($file) <= filemtime($cache)) {
            if (($cache_file = fopen($cache, 'rb'))) {
                if (function_exists('set_file_buffer')) {
                    stream_set_write_buffer($cache_file, 0);
                }

                if (!($cache_contents = fread($cache_file, filesize($cache)))) {
                    $error = "could not read from the XML cache file $cache";
                } else {
                    $error = '';
                }

                fclose($cache_file);

                if (!strcmp($error, '')) {
                    if ('object' == gettype($parser = unserialize($cache_contents))
                        && isset($parser->structure)) {
                        if (!isset($parser->simplified_xml)) {
                            $parser->simplified_xml = 0;
                        }

                        if (($simplified_xml
                             || !$parser->simplified_xml)
                            && (!$store_positions
                                || $parser->store_positions)) {
                            return ('');
                        }
                    } else {
                        $error = "it was not specified a valid cache object in XML file ($cache)";
                    }
                }
            } else {
                $error = "could not open cache XML file ($cache)";
            }

            if (strcmp($error, '')) {
                return ($error);
            }
        }
    }

    $parser = new xml_parser_class();

    $parser->store_positions = $store_positions;

    $parser->case_folding = $case_folding;

    $parser->target_encoding = $target_encoding;

    $parser->simplified_xml = $simplified_xml;

    $parser->fail_on_non_simplified_xml = $fail_on_non_simplified_xml;

    if (!strcmp($error = $parser->ParseFile($file), '')
        && strcmp($cache, '')) {
        if (($cache_file = fopen($cache, 'wb'))) {
            if (function_exists('set_file_buffer')) {
                stream_set_write_buffer($cache_file, 0);
            }

            if (!fwrite($cache_file, serialize($parser))) {
                $error = "could to write to the XML cache file ($cache)";
            }

            fclose($cache_file);

            if (strcmp($error, '')) {
                unlink($cache);
            }
        } else {
            $error = "could not open for writing to the cache file ($cache)";
        }
    }

    return ($error);
}
