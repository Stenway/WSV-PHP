<?php

namespace Stenway\Wsv;

use \Exception as Exception;
use \IntlChar as IntlChar;

class WsvCharIterator {
	private array $chars;
	private int $index;
	
	function __construct(string $text) {
		$this->chars = StringUtil::getCodePoints($text);
		$this->index = 0;
	}
	
	function getText() : string {
		return String::fromCodePoints(this->chars);
	}
	
	function getLineInfoString() : string {
		$lineInfo = $this->getLineInfo();
		return sprintf("(%d, %d)", $lineInfo[1] + 1, $lineInfo[2] + 1);
	}
	
	function getLineInfo() : array {
		$lineIndex = 0;
		$linePosition = 0;
		for ($i=0; $i<$this->index; $i++) {
			if ($this->chars[$i] === 0x0A) {
				$lineIndex++;
				$linePosition = 0;
			} else {
				$linePosition++;
			}
		}
		return [$this->index, $lineIndex, $linePosition];
	}
	
	function isEndOfText() : bool {
		return $this->index >= count($this->chars);
	}

	function isChar(int $c) : bool {
		if ($this->isEndOfText()) return false;
		return $this->chars[$this->index] == $c;
	}
	
	function isWhitespace() : bool {
		if ($this->isEndOfText()) return false;
		return WsvChar::isWhitespace($this->chars[$this->index]);
	}
	
	function tryReadChar(int $c) : bool {
		if (!$this->isChar($c)) return false;
		$this->index++;
		return true;
	}
	
	function readCommentText() : string {
		$comment = "";
		while (true) {
			if ($this->isEndOfText()) break;
			$curChar = $this->chars[$this->index];
			if ($curChar === 0x0A) break;
			$comment .= IntlChar::chr($curChar);
			$this->index++;
		}
		return $comment;
	}

	function readWhitespaceOrNull() : ?string {
		$result = "";
		while (true) {
			if ($this->isEndOfText()) break;
			$curChar = $this->chars[$this->index];
			if ($curChar === 0x0A) break;
			if (!WsvChar::isWhitespace($curChar)) break;
			$result .= IntlChar::chr($curChar);
			$this->index++;
		}
		if ($result === "") return null;
		return $result;
	}

	function readString() : string {
		$result = "";
		while (true) {
			if ($this->isEndOfText() || $this->isChar(0x0A)) {
				throw $this->getException("String not closed");
			}
			$curChar = $this->chars[$this->index];
			if ($curChar === 0x22) {
				$this->index++;
				if ($this->tryReadChar(0x22)) {
					$result .= IntlChar::chr(0x22);
				} else if($this->tryReadChar(0x2F)) {
					if (!$this->tryReadChar(0x22)) {
						throw $this->getException("Invalid string line break");
					}
					$result .= IntlChar::chr(0x0A);
				} else if ($this->isWhitespace() || $this->isChar(0x0A) || $this->isChar(0x23) || $this->isEndOfText()) {
					break;
				} else {
					throw $this->getException("Invalid character after string");
				}
			} else {
				$result .= IntlChar::chr($curChar);
				$this->index++;
			}
		}
		return $result;
	}

	function readValue() : string {
		$result = "";
		while (true) {
			if ($this->isEndOfText()) {
				break;
			}
			$curChar = $this->chars[$this->index];
			if (WsvChar::isWhitespace($curChar) || $this->isChar(0x0A) || $curChar === 0x23) {
				break;
			}
			if ($curChar === 0x22) {
				throw $this->getException("Invalid double quote in value");
			}
			$result .= IntlChar::chr($curChar);
			$this->index++;
		}
		if ($result === "") {
			throw $this->getException("Invalid value");
		}
		return $result;
	}
	
	function getException(string $message) {
		return new Exception($message . " " . $this->getLineInfoString());
	}
}

?>