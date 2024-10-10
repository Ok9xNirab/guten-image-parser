<?php

namespace KodeZen\GutenImageParser\Factories;

use KodeZen\GutenImageParser\Parsers\WP\CoreImageParser;
use KodeZen\GutenImageParser\Parsers\WP\GroupParser;

class ImageParserFactory {

	private array $parsers = array();

	private function __construct() {
		$this->set_parsers();
	}

	private function set_parsers() {
		$this->parsers = apply_filters( 'guten_image_parsers', array(
			'core/group' => GroupParser::class,
			'core/image' => CoreImageParser::class,
		) );
	}

	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	public function parse( array $blocks ): array {
		$parsed_blocks = array();
		foreach ( $blocks as $block ) {
			if ( ! array_key_exists( $block['blockName'], $this->parsers ) ) {
				$parsed_blocks[] = $block;
				continue;
			}
			if ( count( $block['innerBlocks'] ) > 0 ) {
				$block['innerBlocks'] = $this->parse( $block['innerBlocks'] );
			}

			$parsed_blocks[] = $this->process( $block );
		}

		return $parsed_blocks;
	}

	private function process( array $block ): array {
		return ( new $this->parsers[ $block['blockName'] ]( $block ) )->doParse();
	}
}