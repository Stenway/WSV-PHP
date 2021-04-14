<?php

namespace Stenway\Wsv;

class WsvParser {
	private static function _parseLine(WsvCharIterator $iterator) : WsvLine {
		$values = array();
		$whitespaces = array();
		
		$whitespace = $iterator->readWhitespaceOrNull();
		array_push($whitespaces, $whitespace);

		while (!$iterator->isChar(0x0A) && !$iterator->isEndOfText()) {
			$value = null;
			if($iterator->isChar(0x23)) {
				break;
			} else if($iterator->tryReadChar(0x22)) {
				$value = $iterator->readString();
			} else {
				$value = $iterator->readValue();
				if ($value === "-") {
					$value = null;
				}
			}
			array_push($values, $value);

			$whitespace = $iterator->readWhitespaceOrNull();
			if ($whitespace === null) {
				break;
			}
			array_push($whitespaces, $whitespace);
		}
		
		$comment = null;
		if ($iterator->tryReadChar(0x23)) {
			$comment = $iterator->readCommentText();
			if ($whitespace == null) {
				array_push($whitespaces, null);
			}
		}
		
		$newLine = new WsvLine();
		$newLine->_set($values, $whitespaces, $comment);
		return $newLine;
	}
	
	static function parseLine(string $content) : WsvLine {
		$iterator = new WsvCharIterator($content);
		$newLine = WsvParser::_parseLine($iterator);
		if ($iterator->isChar(0x0A)) {
			throw $iterator->getException("Multiple WSV lines not allowed");
		} else if (!$iterator->isEndOfText()) {
			throw $iterator->getException("WSV line not parsed completely");
		}
		return $newLine;
	}
	
	static function parseDocument(string $content) : WsvDocument {
		$document = new WsvDocument();
		
		$iterator = new WsvCharIterator($content);
		
		while (true) {
			$newLine = WsvParser::_parseLine($iterator);
			$document->addLine($newLine);
			
			if ($iterator->isEndOfText()) {
				break;
			} else if(!$iterator->tryReadChar(0x0A)) {
				throw $iterator->getException("Invalid WSV document");
			}
		}
		
		if (!$iterator->isEndOfText()) {
			throw $iterator->getException("WSV document not parsed completely");
		}

		return $document;
	}
}

?>