<?php

namespace Stenway\Wsv;

class WsvLine {
	public ?array $values;
	private ?array $whitespaces;
	private ?string $comment;
	
	function _set($values, $whitespaces, $comment) {
		$this->values = $values;
		$this->whitespaces = $whitespaces;
		$this->comment = $comment;
	}
	
	function getWhitespaces() : array {
		return $this->whitespaces;
	}
	
	function getComment() : ?string {
		return $this->comment;
	}
	
	function hasValues() : bool {
		return $this->values !== null && count($this->values) > 0;
	}
	
	function __toString() : string {
		return $this->toString();
	}
	
	function toString() : string {
		return WsvSerializer::serializeLine($this);
	}
	
	static function parse(string $content) : WsvLine {
		return WsvParser::parseLine($content);
	}
}

?>