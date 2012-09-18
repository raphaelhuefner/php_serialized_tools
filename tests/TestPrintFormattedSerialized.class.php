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
array( /* recursion id is 1 */
  'hello' => 'world' /* recursion id is 2 */,
  0 => 'value_with_numeric_key' /* recursion id is 3 */,
  'nested' => array( /* recursion id is 4 */
    0 => 1 /* recursion id is 5 */,
    1 => 2 /* recursion id is 6 */,
    2 => 3 /* recursion id is 7 */,
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

  public function testRecursiveArrays() {
    $a = array(
      'a_hello' => 'world',
      'a_value_with_numeric_key',
      'a_nested' => array(
        1,
        2,
        3,
      ),
    );
    $b = array(
      'b_hello' => 'world',
      'b_value_with_numeric_key',
      'b_nested' => array(
        1,
        2,
        3,
      ),
    );
    $b['b_recursive'] = & $a;
    $a['a_recursive'] = & $b;

    $expected_string = <<< EOD
array( /* recursion id is 1 */
  'a_hello' => 'world' /* recursion id is 2 */,
  0 => 'a_value_with_numeric_key' /* recursion id is 3 */,
  'a_nested' => array( /* recursion id is 4 */
    0 => 1 /* recursion id is 5 */,
    1 => 2 /* recursion id is 6 */,
    2 => 3 /* recursion id is 7 */,
  ),
  'a_recursive' => array( /* recursion id is 8 */
    'b_hello' => 'world' /* recursion id is 9 */,
    0 => 'b_value_with_numeric_key' /* recursion id is 10 */,
    'b_nested' => array( /* recursion id is 11 */
      0 => 1 /* recursion id is 12 */,
      1 => 2 /* recursion id is 13 */,
      2 => 3 /* recursion id is 14 */,
    ),
    'b_recursive' => array( /* recursion id is 15 */
      'a_hello' => 'world' /* recursion id is 16 */,
      0 => 'a_value_with_numeric_key' /* recursion id is 17 */,
      'a_nested' => array( /* recursion id is 18 */
        0 => 1 /* recursion id is 19 */,
        1 => 2 /* recursion id is 20 */,
        2 => 3 /* recursion id is 21 */,
      ),
      'a_recursive' => Recursion::getReference(8) /* recursion id is 22 */,
    ),
  ),
)
EOD;

    $this->basicTestScalar(
      $a,
      trim($expected_string)
    );
  }

  public function testRecursiveObjects() {
    $a = new stdClass();
    $b = new stdClass();
    $c = new stdClass();

    $a->a_hello = 'world';
    $b->b_hello = 'world';
    $c->c_hello = 'world';

    $a->a_recursive = $b;
    $b->b_recursive = $c;
    $c->c_recursive = $a;

    $expected_string = <<< EOD
stdClass::__set_state(array( /* recursion id is 1 */
  'a_hello' => 'world' /* recursion id is 2 */,
  'a_recursive' => stdClass::__set_state(array( /* recursion id is 3 */
    'b_hello' => 'world' /* recursion id is 4 */,
    'b_recursive' => stdClass::__set_state(array( /* recursion id is 5 */
      'c_hello' => 'world' /* recursion id is 6 */,
      'c_recursive' => Recursion::get(1) /* recursion id is 7 */,
    )),
  )),
))
EOD;
    $this->basicTestScalar(
      $a,
      trim($expected_string)
    );
  }

  public function testRecursiveObjects2() {
    $a = new stdClass();
    $b = new stdClass();
    $c = new stdClass();

    $a->a_hello = 'world';
    $b->b_hello = 'world';
    $c->c_hello = 'world';

    $a->a_recursive = $b;
    $b->b_recursive = $c;
    $c->c_recursive = $b;

    $expected_string = <<< EOD
stdClass::__set_state(array( /* recursion id is 1 */
  'a_hello' => 'world' /* recursion id is 2 */,
  'a_recursive' => stdClass::__set_state(array( /* recursion id is 3 */
    'b_hello' => 'world' /* recursion id is 4 */,
    'b_recursive' => stdClass::__set_state(array( /* recursion id is 5 */
      'c_hello' => 'world' /* recursion id is 6 */,
      'c_recursive' => Recursion::get(3) /* recursion id is 7 */,
    )),
  )),
))
EOD;
    $this->basicTestScalar(
      $a,
      trim($expected_string)
    );
  }

  public function testRecursiveObjects3() {
    $a = new stdClass();
    $b = new stdClass();
    $c = new stdClass();
    $d = new stdClass();

    $a->a_hello = 'world';
    $b->b_hello = 'world';
    $c->c_hello = 'world';
    $d->d_hello = 'world';

    $a->a_recursive = $b;
    $b->b_recursive = $a;
    $c->c_recursive = $d;
    $d->d_recursive = $c;

    $all = new stdClass();
    $all->a = $a;
    $all->b = $b;
    $all->c = $c;
    $all->d = $d;

    $expected_string = <<< EOD
stdClass::__set_state(array( /* recursion id is 1 */
  'a' => stdClass::__set_state(array( /* recursion id is 2 */
    'a_hello' => 'world' /* recursion id is 3 */,
    'a_recursive' => stdClass::__set_state(array( /* recursion id is 4 */
      'b_hello' => 'world' /* recursion id is 5 */,
      'b_recursive' => Recursion::get(2) /* recursion id is 6 */,
    )),
  )),
  'b' => Recursion::get(4) /* recursion id is 7 */,
  'c' => stdClass::__set_state(array( /* recursion id is 8 */
    'c_hello' => 'world' /* recursion id is 9 */,
    'c_recursive' => stdClass::__set_state(array( /* recursion id is 10 */
      'd_hello' => 'world' /* recursion id is 11 */,
      'd_recursive' => Recursion::get(8) /* recursion id is 12 */,
    )),
  )),
  'd' => Recursion::get(10) /* recursion id is 13 */,
))
EOD;
    $this->basicTestScalar(
      $all,
      trim($expected_string)
    );
  }
}
