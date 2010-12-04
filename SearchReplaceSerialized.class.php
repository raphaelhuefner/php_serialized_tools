<?php 

/**
 * @file Search and replace in PHP serialized data.
 * @license http://opensource.org/licenses/lgpl-3.0.html The GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @author Raphael Huefner http://www.raphaelhuefner.com
 * Sponsored by Affinity Bridge http://www.affinitybridge.com
 */

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TransformSerialized.class.php');

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
