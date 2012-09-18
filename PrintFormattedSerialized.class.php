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

  protected function _recursionCommentFromContext(array $context=array()) {
    if (isset($context['recursionId'])) {
      return ' /* recursion id is ' . $context['recursionId'] . ' */';
    }
    return '';
  }

  protected function _outputBooleanSerialized($booleanStringRepresentation, array $context=array()) {
    return (('0' == $booleanStringRepresentation) ? 'FALSE' : 'TRUE') . $this->_recursionCommentFromContext($context);
  }

  protected function _outputIntegerSerialized($integerStringRepresentation, array $context=array()) {
    return $integerStringRepresentation . $this->_recursionCommentFromContext($context);
  }

  protected function _outputDoubleSerialized($doubleStringRepresentation, array $context=array()) {
    return $doubleStringRepresentation . $this->_recursionCommentFromContext($context);
  }

  protected function _outputStringSerialized($string, array $context=array()) {
    return "'" . $string . "'" . $this->_recursionCommentFromContext($context);
  }

  protected function _outputArraySerialized(array $subThings, array $context=array()) {
    if (0 != (count($subThings) % 2)) {
      throw new Exception('Must have even number of subThings to output array.');
    }
    $output = 'array(' . $this->_recursionCommentFromContext($context) . PHP_EOL;
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

  protected function _outputRecursionSerialized($recursionId, array $context=array()) {
    return 'Recursion::get(' . $recursionId . ')' . $this->_recursionCommentFromContext($context);
  }

  protected function _outputRecursionSerializedCapitalR($recursionId, array $context=array()) {
    return 'Recursion::getReference(' . $recursionId . ')' . $this->_recursionCommentFromContext($context);
  }

  protected function _outputObjectSerialized($className, array $subThings, array $context=array()) {
    if (0 != (count($subThings) % 2)) {
      throw new Exception('Must have even number of subThings to output object.');
    }
    $output = $className . '::__set_state(array(' . $this->_recursionCommentFromContext($context) . PHP_EOL;
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

  protected function _outputNullSerialized(array $context=array()) {
    return 'NULL';
  }

  protected function _handleBoolean($booleanStringRepresentation, array $context=array()) {
    return $this->_outputBooleanSerialized($booleanStringRepresentation, $context);
  }

  protected function _handleInteger($integerStringRepresentation, array $context=array()) {
    return $this->_outputIntegerSerialized($integerStringRepresentation, $context);
  }

  protected function _handleDouble($doubleStringRepresentation, array $context=array()) {
    return $this->_outputDoubleSerialized($doubleStringRepresentation, $context);
  }

  protected function _handleString($string, array $context=array()) {
    return $this->_outputStringSerialized($string, $context);
  }

  protected function _handleArray(array $subThings, array $context=array()) {
    return $this->_outputArraySerialized($subThings, $context);
  }

  protected function _handleRecursion($recursionId, array $context=array()) {
    return $this->_outputRecursionSerialized($recursionId, $context);
  }

  protected function _handleRecursionCapitalR($recursionId, array $context=array()) {
    return $this->_outputRecursionSerializedCapitalR($recursionId, $context);
  }

  protected function _handleObject($className, array $subThings, array $context=array()) {
    return $this->_outputObjectSerialized($className, $subThings, $context);
  }

  protected function _handleNull(array $context=array()) {
    return $this->_outputNullSerialized($context);
  }

}
