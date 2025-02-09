<?php

namespace KodeZen\GutenImageParser\Parsers\WP;

use DOMDocument;
use KodeZen\GutenImageParser\Abstracts\ImageParser;
use WP_Error;

class CoreImageParser extends ImageParser {


	public function parse(): array {
		$dom = new DOMDocument();
		// Suppress warnings for malformed HTML.
		libxml_use_internal_errors(true);
		$dom->loadHTML($this->block['innerHTML']);
		libxml_clear_errors();
		$imageHTML = $dom->getElementsByTagName('img')->item(0);
		if ( ! $imageHTML ) {
			return $this->block;
		}

		$imgSRC = $imageHTML->attributes->getNamedItem('src');
		if ( ! $imgSRC ) {
			return $this->block;
		}

        // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Calling a property from native class `DOMDocument`.
		$imgURL = $imgSRC->nodeValue;
		if ( ! $this->check_external_url($imgURL) ) {
			return $this->block;
		}

		$attachment_id = $this->upload_file($imgURL);

		if ( $attachment_id instanceof WP_Error ) {
			// TODO: error handling ...
			exit();
		}

		$attachment_url = wp_get_attachment_url($attachment_id);

		$this->block['attrs']['id']  = $attachment_id;
		$this->block['innerHTML']    = str_replace($imgURL, $attachment_url, $this->block['innerHTML']);
		$this->block['innerContent'] = array( str_replace($imgURL, $attachment_url, $this->block['innerHTML']) );

		return $this->block;
	}
}
