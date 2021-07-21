<?php

namespace Stenway\Wsv;

use \Stenway\ReliableTxt\ReliableTxtEncoding as ReliableTxtEncoding;
use \Stenway\ReliableTxt\ReliableTxtDocument as ReliableTxtDocument;

class WsvDocument {
	private int $encoding = ReliableTxtEncoding::UTF_8;
	public array $lines = [];
	
	function setEncoding(int $encoding) {
		$this->encoding = $encoding;
	}
	
	function getEncoding() : int {
		return $this->encoding;
	}
	
	function addLine(WsvLine $line) {
		array_push($this->lines, $line);
	}
	
	function __toString() : string {
		return $this->toString();
	}
	
	function toString() : string {
		return WsvSerializer::serializeDocument($this);
	}
	
	function save(string $filePath) {
		$content = self::toString();
		$file = new ReliableTxtDocument($content, $this->encoding);
		$file->save($filePath);
	}
	
	static function parse(string $content) : WsvDocument {
		return WsvParser::parseDocument($content);
	}
	
	static function load(string $filePath) : WsvDocument {
		$file = ReliableTxtDocument::load($filePath);
		$content = $file->getText();
		$document = self::parse($content);
		$document->setEncoding($file->getEncoding());
		return $document;
	}
}

?>