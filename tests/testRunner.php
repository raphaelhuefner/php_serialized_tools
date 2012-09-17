<?php
define('BASE_DIR', dirname(dirname(__FILE__)));

require_once(BASE_DIR . DIRECTORY_SEPARATOR . 'SearchReplaceSerialized.class.php');
require_once(BASE_DIR . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'TestSearchReplaceSerialized.class.php');

$tester = new TestSearchReplaceSerialized();
$tester->runAllTests();

require_once(BASE_DIR . DIRECTORY_SEPARATOR . 'PrintFormattedSerialized.class.php');
require_once(BASE_DIR . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'TestPrintFormattedSerialized.class.php');
$tester = new TestPrintFormattedSerialized();
$tester->runAllTests();

