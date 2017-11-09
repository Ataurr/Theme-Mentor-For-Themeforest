<?php


class Header_Validations implements Theme_Mentor_Executor {

	private $wp_head_found        = false;
	// random defaults - need to compare further
	private $wp_head_line         = -1;
	private $head_close_tag_line  = -1;
	private $file                 = array();
	private $error_message        = [];

	public function crawl( $filename, $file ) {
		if ( false !== strpos( $filename, 'header.php' ) ) {
			$this->file = $file;

			$this->manage_wphead_placement( $file );
			$this->does_wp_title_exist( $file );
		}
	}

	/**
	 * Aggregating the data if needed, like stats, some array management, etc
	 */
	public function execute() {
		$this->aggregate_wphead_placement();
	}

	/**
	 * Describe to the regular human being what's going on, if anything
	 */
	public function get_description() {
		if ( empty( $this->error_message ) ) {
			return '';
		}

		$out = '';
		if ( is_array( $this->error_message ) ) {
			foreach ( $this->error_message as $error ) {
				$out .= $error;
			}
		}

		return $out;
	}

	/*
	 * 
	 * Crawler methods
	 * 
	 */

	/**
	 * Can we find wphead before the closing </head> tag?
	 * @param string $file content
	 */
	private function manage_wphead_placement( $file ) {
		// do the header twist
		$lines_found = preg_grep( '/wp_head\(\)/', $file );

		// wp_head found
		// TODO: we need to abstract these 5-liners with 1-liner helper function.
		if ( !empty( $lines_found ) ) {
			$this->wp_head_found = true;
			foreach ( $lines_found as $line => $snippet ) {
				$this->wp_head_line = $line;
			}
		}

		// lookup for closing body tag
		if ( $this->wp_head_found ) {
			$lines_found = preg_grep( '/<\/head>/', $file );

			if ( !empty( $lines_found ) ) {
				foreach ( $lines_found as $line => $snippet ) {
					$this->head_close_tag_line = $line;
				}
			}
		}
	}

	/**
	 * Does it really? Hmm...
	 * @param string $file content, check if wp_title() is between <title> and </title>
	 */
	private function does_wp_title_exist( $file ) {
		// two steps, don't bother with file_get_contents for this
		$lines_found = preg_grep( '/<title(.*)>(.*)<\/title>/', $file );

		// title found
		if ( !empty( $lines_found ) && is_array( $lines_found ) ) {
			foreach ( $lines_found as $line => $snippet ) {

				$error_text				 = __( 'Title tag found in header.php, Please add add_theme_support( \'title-tag\' ) in after_setup_theme action hook.', 'dx_theme_mentor' );
				$this->error_message[]	 = sprintf( '<div class="tm_report_row"><span class="tm_message">%s</span> at file <span class="tm_file">%s</span>, line <span class="tm_line">%d</span></div>', $error_text, 'header.php', $line + 1 );
			}
		}
	}

	/*
	 *
	 * Aggregating methods
	 *
	 */

	/**
	 * Manage the wphead placement factor 
	 */
	private function aggregate_wphead_placement() {
		if ( -1 != $this->wp_head_line && -1 != $this->head_close_tag_line ) {
			$diff = $this->head_close_tag_line - $this->wp_head_line;
			if ( $diff < 0 || $diff > 1 ) {
				// edge case for closing PHP tag between wp_footer and closing body
				if ( $diff > 2 ||
				empty( $this->file ) ||
				!isset( $this->file[ $this->head_close_tag_line - 1 ] ) ||
				false === strpos( $this->file[ $this->head_close_tag_line - 1 ], '?>' ) ) {
					$error_text = __( 'wp_head call should be right before the closing head tag.', 'dx_theme_mentor' );

					$this->error_message[] = sprintf( '<div class="tm_report_row"><span class="tm_message">%s</span> at file <span class="tm_file">%s</span>, line <span class="tm_line">%d</span></div>', $error_text, 'header.php', $this->head_close_tag_line + 1 );
				}
			}
		} else {
			$error_text				 = __( 'No wp_head or closing head tag found', 'dx_theme_mentor' );
			$this->error_message[]	 = sprintf( '<div class="tm_report_row"><span class="tm_message">%s</span> at file <span class="tm_file">%s</span></div>', $error_text, 'header.php' );
		}
	}

}

Mentor_Themeforest::$validations[] = new Header_Validations();
