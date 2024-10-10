<?php

namespace KodeZen\GutenImageParser\Parsers\WP;

use IvoPetkov\HTML5DOMDocument;
use KodeZen\GutenImageParser\Abstracts\ImageParser;
use WP_Error;

class CoreImageParser extends ImageParser {

	public function parse(): array {
		$html5   = new HTML5DOMDocument();
		$imgHTML = $this->block['innerHTML'];
		$html5->loadHTML( $imgHTML );
		$imgURL        = $html5->querySelector( 'img' )->getAttribute( 'src' );
		$attachment_id = $this->upload_file( $imgURL );

		if ( $attachment_id instanceof WP_Error ) {
			// TODO: error handling ...
			exit();
		}

		$attachment_url = wp_get_attachment_url( $attachment_id );

		$this->block['attrs']['id']  = $attachment_id;
		$this->block['innerHTML']    = str_replace( $imgURL, $attachment_url, $this->block['innerHTML'] );
		$this->block['innerContent'] = array( str_replace( $imgURL, $attachment_url, $this->block['innerHTML'] ) );

		return $this->block;
	}
}