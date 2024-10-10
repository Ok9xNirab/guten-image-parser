<?php

use KodeZen\GutenImageParser\Factories\ImageParserFactory;

function guten_image_parser( string $block_content ): array {
	$blocks = parse_blocks( $block_content );

	return ImageParserFactory::instance()->parse( $blocks );
}