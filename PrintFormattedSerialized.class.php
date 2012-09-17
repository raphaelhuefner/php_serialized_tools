<?php 

/**
 * @file Print formatted PHP serialized data.
 * @license http://opensource.org/licenses/lgpl-3.0.html The GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @author Raphael Huefner http://www.raphaelhuefner.com
 * Sponsored by Affinity Bridge http://www.affinitybridge.com
 */

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TransformSerialized.class.php');

class PrintFormattedSerialized extends TransformSerialized {
  protected function _indentExtraLines($string, $indent = '  ') {
    return str_replace(PHP_EOL, PHP_EOL . $indent, $string);
  }

  protected function _outputBooleanSerialized($booleanStringRepresentation) {
    return ('0' == $booleanStringRepresentation) ? 'FALSE' : 'TRUE';
  }

  protected function _outputIntegerSerialized($integerStringRepresentation) {
    return $integerStringRepresentation;
  }

  protected function _outputDoubleSerialized($doubleStringRepresentation) {
    return $doubleStringRepresentation;
  }

  protected function _outputStringSerialized($string) {
    return "'" . $string . "'";
  }

  protected function _outputArraySerialized(array $subThings) {
    if (0 != (count($subThings) % 2)) {
      throw new Exception('Must have even number of subThings to output array.');
    }
    $output = 'array(' . PHP_EOL;
    for ($i = 0; $i < count($subThings); $i += 2) {
      $output .= '  ';
      $output .= $this->_indentExtraLines($subThings[$i]);
      $output .= ' => ';
      $output .= $this->_indentExtraLines($subThings[$i+1]);
      $output .= ',';
      $output .= PHP_EOL;
    }
    $output .= ')';
    return $output;
  }

  protected function _outputRecursionSerialized($recursionId) {
    return 'recursion(' . $recursionId . ", 'r')";
  }

  protected function _outputRecursionSerializedCapitalR($recursionId) {
    return 'recursion(' . $recursionId . ", 'R')";
  }

  protected function _outputObjectSerialized($className, array $subThings) {
    if (0 != (count($subThings) % 2)) {
      throw new Exception('Must have even number of subThings to output object.');
    }
    $output = $className . '::__set_state(array(' . PHP_EOL;
    for ($i = 0; $i < count($subThings); $i += 2) {
      $output .= '  ';
      $output .= $this->_indentExtraLines($subThings[$i]);
      $output .= ' => ';
      $output .= $this->_indentExtraLines($subThings[$i+1]);
      $output .= ',';
      $output .= PHP_EOL;
    }
    $output .= '))';
    return $output;
  }

  protected function _outputNullSerialized() {
    return 'NULL';
  }
}
