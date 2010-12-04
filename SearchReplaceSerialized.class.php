<?php 

/**
 * @file Search and replace in PHP serialized data.
 * @license http://opensource.org/licenses/lgpl-3.0.html The GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @author Raphael Huefner http://www.raphaelhuefner.com
 * Sponsored by Affinity Bridge http://www.affinitybridge.com
 */

class SearchReplaceSerialized extends TransformSerialized {
  protected $_search = '';
  protected $_replace = '';

  public function __construct($input='', $search='', $replace='') {
    parent::__construct($input);
    $this->_search = $search;
    $this->_replace = $replace;
  }

  protected function _handleString($string, array $context=array()) {
    $new_str = str_replace($this->_search, $this->_replace, $string);
    return parent::_handleString($new_str, $context);
  }
}

class TransformSerialized {
  protected $_index = 0;
  protected $_input = '';

  public function __construct($input='') {
    $this->_input = $input;
  }

  public function run() {
    return $this->_match();
  }

  protected function _matchRegEx($regEx) {
    $matches = array();
    $number_of_matches = preg_match('{' . $regEx . '}', $this->_input, $matches, PREG_OFFSET_CAPTURE, $this->_index);
    if (
      (0 == $number_of_matches)
      ||
      ($this->_index != $matches[0][1])
    ) {
      throw new Exception('Did not find something matching the expected pattern /' . $regEx . '/ --> found this instead: ' . $this->_input);
    }
    $found = $matches[0][0];
    $found_len = strlen($found);
    $this->_index += $found_len;
    return $found;
  }

  protected function _matchString($string) {
    $len = strlen($string);
    $found = substr($this->_input, $this->_index, $len);
    if ($found != $string) {
      throw new Exception('Found "' . $found . '", but expected "' . $string . '".');
    }
    $this->_index += $len;
    return $found;
  }

  protected function _matchType() {
    return $this->_matchRegEx('[bidsarRON]');
  }

  protected function _matchColon() {
    return $this->_matchString(':');
  }

  protected function _matchSemicolon() {
    return $this->_matchString(';');
  }

  protected function _matchQuote() {
    return $this->_matchString('"');
  }

  protected function _matchBraceOpen() {
    return $this->_matchString('{');
  }

  protected function _matchBraceClose() {
    return $this->_matchString('}');
  }

  protected function _matchIntegerStringRepresentation() {
    return $this->_matchRegEx('-?\d+');
  }

  protected function _matchDoubleStringRepresentation() {
    return $this->_matchRegEx('[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?');
  }

  protected function _matchBooleanStringRepresentation() {
    return $this->_matchRegEx('[01]');
  }

  protected function _matchStringByLength($len) {
    $found = substr($this->_input, $this->_index, $len);
    $this->_index += $len;
    return $found;
  }

  protected function _outputBooleanSerialized($booleanStringRepresentation) {
    return 'b:' . $booleanStringRepresentation . ';';
  }

  protected function _outputIntegerSerialized($integerStringRepresentation) {
    return 'i:' . $integerStringRepresentation . ';';
  }

  protected function _outputDoubleSerialized($doubleStringRepresentation) {
    return 'd:' . $doubleStringRepresentation . ';';
  }

  protected function _outputStringSerialized($string) {
    return 's:' . strlen($string) . ':"' . $string . '";';
  }

  protected function _outputArraySerialized(array $subThings) {
    if (0 != (count($subThings) % 2)) {
      throw new Exception('Must have even number of subThings to output array.');
    }
    return 'a:' . (count($subThings)/2) . ':{' . implode('', $subThings) . '}';
  }

  protected function _outputRecursionSerialized($recursionId) {
    return 'r:' . $recursionId . ';';
  }

  protected function _outputRecursionSerializedCapitalR($recursionId) {
    return 'R:' . $recursionId . ';';
  }

  protected function _outputObjectSerialized($className, array $subThings) {
    if (0 != (count($subThings) % 2)) {
      throw new Exception('Must have even number of subThings to output object.');
    }
    return 'O:' . strlen($className) . ':"' . $className . '":' . (count($subThings)/2) . ':{' . implode('', $subThings) . '}';
  }

  protected function _outputNullSerialized() {
    return 'N;';
  }

  protected function _handleBoolean($booleanStringRepresentation, array $context=array()) {
    return $this->_outputBooleanSerialized($booleanStringRepresentation);
  }

  protected function _handleInteger($integerStringRepresentation, array $context=array()) {
    return $this->_outputIntegerSerialized($integerStringRepresentation);
  }

  protected function _handleDouble($doubleStringRepresentation, array $context=array()) {
    return $this->_outputDoubleSerialized($doubleStringRepresentation);
  }

  protected function _handleString($string, array $context=array()) {
    return $this->_outputStringSerialized($string);
  }

  protected function _handleArray(array $subThings, array $context=array()) {
    return $this->_outputArraySerialized($subThings);
  }

  protected function _handleRecursion($recursionId, array $context=array()) {
    return $this->_outputRecursionSerialized($recursionId);
  }

  protected function _handleRecursionCapitalR($recursionId, array $context=array()) {
    return $this->_outputRecursionSerializedCapitalR($recursionId);
  }

  protected function _handleObject($className, array $subThings, array $context=array()) {
    return $this->_outputObjectSerialized($className, $subThings);
  }

  protected function _handleNull(array $context=array()) {
    return $this->_outputNullSerialized();
  }

  protected function _match(array $context=array()) {
    $type = $this->_matchType();
    switch ($type) {
      case 'b': // boolean
        $this->_matchColon();
        $booleanStringRepresentation = $this->_matchBooleanStringRepresentation();
        $this->_matchSemicolon();
        return $this->_handleBoolean($booleanStringRepresentation, $context);
      case 'i': // integer
        $this->_matchColon();
        $integerStringRepresentation = $this->_matchIntegerStringRepresentation();
        $this->_matchSemicolon();
        return $this->_handleInteger($integerStringRepresentation, $context);
      case 'd': // double (also known as float)
        $this->_matchColon();
        $doubleStringRepresentation = $this->_matchDoubleStringRepresentation();
        $this->_matchSemicolon();
        return $this->_handleDouble($doubleStringRepresentation, $context);
      case 's': // string
        $this->_matchColon();
        $len = (int) $this->_matchIntegerStringRepresentation();
        $this->_matchColon();
        $this->_matchQuote();
        $string = $this->_matchStringByLength($len);
        $this->_matchQuote();
        $this->_matchSemicolon();
        return $this->_handleString($string, $context);
      case 'a': // array
        $this->_matchColon();
        $len = (int) $this->_matchIntegerStringRepresentation();
        $this->_matchColon();
        $this->_matchBraceOpen();
        $subThings = array();
        for ($i = 0; $i < $len; $i++) {
          $key = $this->_match(array('isArrayKey' => true, 'parentContext' => $context));
          $value = $this->_match(array('isArrayValue' => true, 'transformedKey' => $key, 'parentContext' => $context));
          $subThings[] = $key;
          $subThings[] = $value;
        }
        $this->_matchBraceClose();
        return $this->_handleArray($subThings, $context);
      case 'r': // recursion
        $this->_matchColon();
        $recursionId = $this->_matchIntegerStringRepresentation();
        $this->_matchSemicolon();
        return $this->_handleRecursion($recursionId, $context);
      case 'R': // same as "r", but we better keep the capitalization, just in case... :-)
        $this->_matchColon();
        $recursionId = $this->_matchIntegerStringRepresentation();
        $this->_matchSemicolon();
        return $this->_handleRecursionCapitalR($recursionId, $context);
      case 'O': // object
        $this->_matchColon();
        $classNameLen = (int) $this->_matchIntegerStringRepresentation();
        $this->_matchColon();
        $this->_matchQuote();
        $className = $this->_matchStringByLength($classNameLen);
        $this->_matchQuote();
        $this->_matchColon();
        $len = (int) $this->_matchIntegerStringRepresentation();
        $this->_matchColon();
        $this->_matchBraceOpen();
        $subThings = array();
        for ($i = 0; $i < $len; $i++) {
          $key = $this->_match(array('isObjectKey' => true, 'className' => $className, 'parentContext' => $context));
          $value = $this->_match(array('isObjectValue' => true, 'className' => $className, 'transformedKey' => $key, 'parentContext' => $context));
          $subThings[] = $key;
          $subThings[] = $value;
        }
        $this->_matchBraceClose();
        return $this->_handleObject($className, $subThings, $context);
      case 'N': // null
        $this->_matchSemicolon();
        return $this->_handleNull($context);
      default:
        throw new Exception("Encountered unknown type [" . $type . "]");
    }
    if ('' != $this->_input) {
      throw new Exception("Found garbage at end.");
    }
  }
}
