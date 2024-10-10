<?php

namespace KodeZen\GutenImageParser\Parsers\WP;

use KodeZen\GutenImageParser\Abstracts\ImageParser;

class GroupParser extends ImageParser {

	public function parse(): array {
		$attrs     = $this->block['attrs'];
		$bg_exists = isset( $attrs['style'], $attrs['style']['background'], $attrs['style']['background']['backgroundImage'] );

		if ( $bg_exists ) {
			$imgUrl         = $attrs['style']['background']['backgroundImage']['url'];

			if ( $this->check_external_url($imgUrl) ) {
				$attachment_id  = $this->upload_file( $imgUrl );
				$attachment_url = wp_get_attachment_url( $attachment_id );
				$this->block['attrs']['style']['background']['backgroundImage']['id']  = $attachment_id;
				$this->block['attrs']['style']['background']['backgroundImage']['url'] = $attachment_url;
			}
		}

		return $this->block;
	}

}