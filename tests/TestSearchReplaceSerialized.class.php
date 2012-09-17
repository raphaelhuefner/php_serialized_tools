<?php 

/**
 * @file Search and replace in PHP serialized data.
 * @license http://opensource.org/licenses/lgpl-3.0.html The GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @author Raphael Huefner http://www.raphaelhuefner.com
 * Sponsored by Affinity Bridge http://www.affinitybridge.com
 */

class TestSearchReplaceSerialized {
  protected $_utf8 = array();

  public function __construct($input='') {
    $this->_utf8 = array(
      '读万卷书不如行万里路。',
      '以古讽今',
      '玉不琢不成器',
      '三十年河东三十年河西',
      '防人之心不可无',
      '害人之心不可有',
    );
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

  public function basicTestScalar($input, $search='search', $replace='replace', $expected=null) {
    $serialized = serialize($input);
    $parser = new SearchReplaceSerialized($serialized, $search, $replace);
    $parsed = $parser->run();
    if (unserialize($parsed) != (is_null($expected)?$input:$expected)) {
      var_dump($input);
      var_dump($serialized);
      var_dump($parsed);
      throw new Exception('Parsing ' . gettype($input) . ' failed.');
    }
  }

  public function testBoolean() {
    $this->basicTestScalar(false);
    $this->basicTestScalar(true);
  }

  public function testInt() {
    $this->basicTestScalar(-123);
    $this->basicTestScalar(0);
    $this->basicTestScalar(123);
  }

  public function testFloat() {
    $this->basicTestScalar(-123.4567);
    $this->basicTestScalar(0.1234567);
    $this->basicTestScalar(0.0);
    $this->basicTestScalar(123.4567E-12);
    $this->basicTestScalar(123.4567E12);
  }

  public function testString() {
    $this->basicTestScalar('hello');
    $this->basicTestScalar($this->_utf8[0]);
  }

  public function testArray() {
    $this->basicTestScalar(array(
      'hello' => 'world', 
      'value_with_numeric_key', 
      $this->_utf8[1] => $this->_utf8[2],
      $this->_utf8[3] => 123.6712376517235E4,
      $this->_utf8[4] => '(fang ren zhi xin bu ke wu)',
      17 => $this->_utf8[5],
    ));
  }
}
