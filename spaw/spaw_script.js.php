<?php
header('Content-Type: application/x-javascript');

require __DIR__ . '/config/spaw_control.config.php';
require __DIR__ . '/class/util.class.php';

if ('Gecko' == SPAW_Util::getBrowser()) {
    require __DIR__ . '/class/script_gecko.js.php';
} else {
    require __DIR__ . '/class/script.js.php';
}
?> 

