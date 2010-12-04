<?php 

/**
 * @file Search and replace in PHP serialized data.
 * @license http://opensource.org/licenses/lgpl-3.0.html The GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @author Raphael Huefner http://www.raphaelhuefner.com
 * Sponsored by Affinity Bridge http://www.affinitybridge.com
 */

class SearchReplaceSerialized {
  protected $_input = '';
  protected $_search = '';
  protected $_replace = '';

  public function __construct($input='', $search='', $replace='') {
    $this->_input = $input;
    $this->_search = $search;
    $this->_replace = $replace;
  }

  public function run() {
    return $this->_eat();
  }

  protected function _eatRegEx($regEx) {
    $matches = array();
    if (0 == preg_match('/^' . $regEx . '/', $this->_input, $matches)) {
      throw new Exception('Did not find something matching the expected pattern /^' . $regEx . '/ --> found this instead: ' . $this->_input);
    }
    $found = $matches[0];
    $found_len = strlen($found);
    $this->_input = substr($this->_input, $found_len);
    return $found;
  }

  protected function _eatType() {
    return $this->_eatRegEx('[bidsarRON]');
  }

  protected function _eatColon() {
    return $this->_eatRegEx(':');
  }

  protected function _eatSemicolon() {
    return $this->_eatRegEx(';');
  }

  protected function _eatQuote() {
    return $this->_eatRegEx('"');
  }

  protected function _eatBraceOpen() {
    return $this->_eatRegEx('\{');
  }

  protected function _eatBraceClose() {
    return $this->_eatRegEx('\}');
  }

  protected function _eatInt() {
    return $this->_eatRegEx('-?\d+');
  }

  protected function _eatFloat() {
    return $this->_eatRegEx('[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?');
  }

  protected function _eatBool() {
    return $this->_eatRegEx('[01]');
  }

  protected function _eatString($len) {
    $found = substr($this->_input, 0, $len);
    $this->_input = substr($this->_input, $len);
    return $found;
  }

  protected function _eat() {
    $type = $this->_eatType();
    switch ($type) {
      case 'b':
        $this->_eatColon();
        $bool = $this->_eatBool();
        $this->_eatSemicolon();
        return 'b:' . $bool . ';';
      case 'i':
        $this->_eatColon();
        $int = $this->_eatInt();
        $this->_eatSemicolon();
        return 'i:' . $int . ';';
      case 'd':
        $this->_eatColon();
        $float = $this->_eatFloat();
        $this->_eatSemicolon();
        return 'd:' . $float . ';';
      case 's':
        $this->_eatColon();
        $len = $this->_eatInt();
        $this->_eatColon();
        $this->_eatQuote();
        $str = $this->_eatString($len);
        $this->_eatQuote();
        $this->_eatSemicolon();
        $new_str = str_replace($this->_search, $this->_replace, $str);
        $new_len = strlen($new_str);
        return 's:' . $new_len . ':"' . $new_str . '";';
      case 'a':
        $this->_eatColon();
        $len = $this->_eatInt();
        $this->_eatColon();
        $this->_eatBraceOpen();
        $subThings = '';
        for ($i = 0; $i < ($len*2); $i++) {
          $subThings .= $this->_eat();
        }
        $this->_eatBraceClose();
        return 'a:' . $len . ':{' . $subThings . '}';
      case 'r':
        $this->_eatColon();
        $recursionId = $this->_eatInt();
        $this->_eatSemicolon();
        return 'r:' . $recursionId . ';';
      case 'R': // same as "r", but we better keep the capitalization, just in case... :-)
        $this->_eatColon();
        $recursionId = $this->_eatInt();
        $this->_eatSemicolon();
        return 'R:' . $recursionId . ';';
      case 'O':
        $this->_eatColon();
        $classNameLen = $this->_eatInt();
        $this->_eatColon();
        $this->_eatQuote();
        $className = $this->_eatString($classNameLen);
        $this->_eatQuote();
        $this->_eatColon();
        $len = $this->_eatInt();
        $this->_eatColon();
        $this->_eatBraceOpen();
        $subThings = '';
        for ($i = 0; $i < ($len*2); $i++) {
          $subThings .= $this->_eat();
        }
        $this->_eatBraceClose();
        return 'O:' . $classNameLen . ':"' . $className . '":' . $len . ':{' . $subThings . '}';
      case 'N':
        $this->_eatSemicolon();
        return 'N;';
      default:
        throw new Exception("Encountered unknown type [" . $type . "]");
    }
    if ('' != $this->_input) {
      throw new Exception("Found garbage at end.");
    }
  }
}
