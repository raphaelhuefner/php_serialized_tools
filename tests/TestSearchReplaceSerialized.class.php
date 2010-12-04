<?php 

/**
 * @file Search and replace in PHP serialized data.
 * @license http://opensource.org/licenses/lgpl-3.0.html The GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @author Raphael Huefner http://www.raphaelhuefner.com
 * Sponsored by Affinity Bridge http://www.affinitybridge.com
 */

class TestSearchReplaceSerialized {

  public function runAllTests() {
    foreach (get_class_methods(get_class($this)) as $methodName) {
      if ('test' == substr($methodName, 0, 4)) {
        try {
          print 'Running ' . $methodName . PHP_EOL;
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
    $this->basicTestScalar('˙∆˚¨ˆ¥¨ˆ˙©∆©˙ƒ©ç∫ç≈ß∂®´∑®´∑´†®®†¥¥¨˙˚∆˙∆∫˜µ∫µ∫˚∆˙✞✞✚✜✠₧€₠¤₯₪฿£¶ÆÉË삅삹샖');
  }

  public function testArray() {
    $this->basicTestScalar(array(
      'hello' => 'world', 
      'value_with_numeric_key', 
      'ÆÉË삅' => 'ˆ˙©∆©˙ƒ©',
      '´∑´†®®†' => 123.6712376517235E4,
      '¥¨ˆ˙©∆' => '£¶ÆÉË삅삹샖',
      17 => '∑´†®®†¥',
    ));
  }

}
