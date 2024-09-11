<?php
namespace App\Pbx;

class Text {
	public $text = "(test)";
	public $entities = [];

	public function appendText($text) {
		$this->text = $this->text.$text;
		return $this;
	}

	public function appendEntity($text, $type) {
		$this->entities[] = [
			"type" => $type,
			"offset" => iconv_strlen($this->text, "UTF-8"),
			"length" => iconv_strlen($text, "UTF-8")
		];
		$this->appendText($text);
		return $this;
	}

	public function endl() {
		return $this->appendText("\n");
	}
}