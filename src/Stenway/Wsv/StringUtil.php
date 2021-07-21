<?php

namespace Stenway\Wsv;

use \IntlChar as IntlChar;

abstract class StringUtil {
	static function getCodePoints(string $text) : array {
		return array_map("IntlChar::ord", mb_str_split($text));
	}
	
	static function fromCodePoints(array $codePoints) : string {
		return implode(array_map("IntlChar::chr", $codePoints));
	}
}

?>