<?php

namespace Stenway\Wsv;

abstract class WsvString {
	public static function isWhitespace(?string $str) : bool {
		if ($str === null || strlen($str) === 0) {
			return false;
		}
		$codePoints = StringUtil::getCodePoints($str);
		foreach ($codePoints as $c) {
			if (!WsvChar::isWhitespace($c)) {
				return false;
			}
		}
		return true;
	}
}

?>