<?php

namespace KodeZen\GutenImageParser\Parsers\WP;

use KodeZen\GutenImageParser\Abstracts\ImageParser;

class GroupParser extends ImageParser {
	public function parse(): array {
		return $this->block;
	}
}