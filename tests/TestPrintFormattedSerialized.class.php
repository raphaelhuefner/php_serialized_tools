<?php 

/**
 * @file Search and replace in PHP serialized data.
 * @license http://opensource.org/licenses/lgpl-3.0.html The GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @author Raphael Huefner http://www.raphaelhuefner.com
 * Sponsored by Affinity Bridge http://www.affinitybridge.com
 */

class TestPrintFormattedSerialized {

  public function __construct($input='') {
  }

  public function runAllTests() {
    foreach (get_class_methods(get_class($this)) as $methodName) {
      if ('test' == substr($methodName, 0, 4)) {
        try {
          print 'Running ' . get_class($this) . '::' . $methodName . PHP_EOL;
          $this->$methodName();
        } catch (Exception $e) {
          print $e->getMessage() . PHP_EOL;
          return false;
        }
      }
    }
    print 'All tests passed.' . PHP_EOL;
    return true;
  }

  public function basicTestScalar($input, $expected) {
    $serialized = serialize($input);
    $parser = new PrintFormattedSerialized($serialized);
    $parsed = $parser->run();
    if ($parsed != $expected) {
      var_dump($input);
      var_dump($serialized);
      var_dump($parsed);
      var_dump($expected);
      throw new Exception('Parsing ' . gettype($input) . ' failed.');
    }
  }

  public function testBoolean() {
    $this->basicTestScalar(false, 'FALSE');
    $this->basicTestScalar(true, 'TRUE');
  }

  public function testInt() {
    $this->basicTestScalar(-123, '-123');
    $this->basicTestScalar(0, '0');
    $this->basicTestScalar(123, '123');
  }

  public function testFloat() {
    $this->basicTestScalar(-123.4567, '-123.4567');
    $this->basicTestScalar(0.1234567, '0.1234567');
    $this->basicTestScalar(0.0, '0.0');
    $this->basicTestScalar(123.4567E-12, '123.4567E-12');
    $this->basicTestScalar(123.4567E12, '123.4567E12');
    $this->basicTestScalar(-123.4567E12, '-123.4567E12');
    $this->basicTestScalar(-123.4567E-12, '-123.4567E-12');
  }

  public function testString() {
    $this->basicTestScalar('hello', "'hello'");
  }

  public function testArray() {
    $array_string = <<< EOD
array(
  'hello' => 'world',
  0 => 'value_with_numeric_key',
  'nested' => array(
    0 => 1,
    1 => 2,
    2 => 3,
  ),
)
EOD;
    $this->basicTestScalar(
      array(
        'hello' => 'world', 
        'value_with_numeric_key',
        'nested' => array(1, 2, 3), 
      ),
      trim($array_string)
    );
  }
}
