<?php

namespace Stenway\Wsv;

use \IntlChar as IntlChar;

class WsvSerializer {
	private static function containsSpecialChar(string $value) : bool {
		$chars = StringUtil::getCodePoints($value);
		foreach ($chars as $c) {
			if ($c == 0x0A || WsvChar::isWhitespace($c) || $c == 0x22
					 || $c == 0x23) {
				return true;
			}
		}
		return false;
	}

	public static function serializeValue(?string $value) : string {
		if ($value===null) {
			return "-";
		} else if ($value === "") {
			return "\"\"";
		} else if ($value === "-") {
			return "\"-\"";
		} else if (WsvSerializer::containsSpecialChar($value)) {
			$result = "\"";
			$chars = StringUtil::getCodePoints($value);
			foreach ($chars as $c) {
				if ($c == 0x0A) {
					$result .= "\"/\"";
				} else if ($c == 0x22) {
					$result .= "\"\"";
				} else {
					$result .= IntlChar::chr($c);
				}
			}
			$result .= "\"";
			return $result;
		} else {
			return $value;
		}
	}
	
	private static function serializeWhitespace(?string $whitespace,
			bool $isRequired) : string {
		if ($whitespace != null && strlen($whitespace) > 0) {
			return $whitespace;
		} else if ($isRequired) {
			return " ";
		} else {
			return "";
		}
	}

	private static function serializeValuesWithWhitespace(WsvLine $line) : string {
		$result = "";
		$whitespaces = $line->getWhitespaces();
		$comment = $line->getComment();
		if ($line->values === null) {
			$whitespace = $whitespaces[0];
			$result .= WsvSerializer::serializeWhitespace($whitespace, false);
			return $result;
		}
		
		for ($i=0; $i<count($line->values); $i++) {
			$whitespace = null;
			if ($i < count($whitespaces)) {
				$whitespace = $whitespaces[$i];
			}
			if ($i == 0) {
				$result .= WsvSerializer::serializeWhitespace($whitespace, false);
			} else {
				$result .= WsvSerializer::serializeWhitespace($whitespace, true);
			}

			$result .= WsvSerializer::serializeValue($line->values[$i]);
		}
		
		if (count($whitespaces) >= count($line->values) + 1) {
			$whitespace = $whitespaces[count($line->values)];
			$result .= WsvSerializer::serializeWhitespace($whitespace, false);
		} else if ($comment !== null && count($line->Values) > 0) {
			$result .= " ";
		}
		return $result;
	}
	
	private static function serializeValuesWithoutWhitespace(WsvLine $line) : string {
		$result = "";
		if ($line->values === null) {
			return $result;
		}
		
		$isFollowingValue = false;
		foreach ($line->values as $value) {
			if ($isFollowingValue) {
				$result .= ' ';
			} else {
				$isFollowingValue = true;
			}
			$result .= WsvSerializer::serializeValue($value);
		}

		if ($line->getComment() !== null && count($line->values) > 0) {
			$result .= " ";
		}
		return $result;
	}
	
	public static function serializeLine(WsvLine $line) : string {
		$result = "";
		$whitespaces = $line->getWhitespaces();
		if ($whitespaces != null && count($whitespaces) > 0) {
			$result .= WsvSerializer::serializeValuesWithWhitespace($line);
		} else {
			$result .= WsvSerializer::serializeValuesWithoutWhitespace($line);
		}
		
		$comment = $line->getComment();
		if ($comment  !== null) {
			$result .= "#";
			$result .= $comment;
		}
		return $result;
	}
	
	public static function serializeLineValues(array $values) : string {
		$result = "";
		$isFirstValue = true;
		foreach ($values as $value) {
			if (!$isFirstValue) {
				$result .= " ";
			} else {
				$isFirstValue = false;
			}
			$result .= self::serializeValue($value);
		}
		return $result;
	}
	
	public static function serializeDocument(WsvDocument $document) : string {
		$result = "";
		$isFirstLine = true;
		foreach ($document->lines as $line) {
			if (!$isFirstLine) {
				$result .= "\n";
			} else {
				$isFirstLine = false;
			}
			$result .= WsvSerializer::serializeLine($line);
		}
		return $result;
	}
}

?>