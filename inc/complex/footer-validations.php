<?php

class Footer_Validations implements Theme_Mentor_Executor {
	private $wp_footer_found = false;
	
	// random defaults - need to compare further
	private $wp_footer_line = -1;
	private $body_close_tag_line = -1;
	
	private $file = array();
	
	private $error_message = '';
	
	public function crawl( $filename, $file ) { 
		if( false !== strpos( $filename, 'footer.php') ) {
			$this->file = $file;
			
			// do the footer twist
			$lines_found = preg_grep( '/wp_footer\(\)/', $file );
			
			// wp_footer found
			// TODO: we need to abstract these 5-liners with 1-liner helper function.
			if( ! empty( $lines_found ) ) {
				$this->wp_footer_found = true;
				foreach( $lines_found as $line => $snippet ) {
					$this->wp_footer_line = $line;				
				}
			}
			
			// lookup for closing body tag
			if( $this->wp_footer_found ) {
				$lines_found = preg_grep( '/<\/body>/', $file );
				
				if( ! empty( $lines_found ) ) {
					foreach( $lines_found as $line => $snippet ) {
						$this->body_close_tag_line = $line;
					}
				}
			}
		} 
	}
	
	/**
	 * Aggregating the data if needed, like stats, some array management, etc
	*/
	public function execute( ) {
		if( -1 != $this->wp_footer_line && -1 != $this->body_close_tag_line ) {
			$diff = $this->body_close_tag_line - $this->wp_footer_line;
			if( $diff < 0 || $diff > 1 ) {
				// edge case for closing PHP tag between wp_footer and closing body
				if( $diff > 2 ||
						empty( $this->file ) || 
						! isset( $this->file[$this->body_close_tag_line - 1] ) ||
						false === strpos( $this->file[$this->body_close_tag_line - 1], '?>' ) ) {
					$error_text = __( 'wp_footer call should be right before the closing body tag.', 'dx_theme_mentor' );
					$this->error_message[] = sprintf( '<div class="tm_report_row"><span class="tm_message">%s</span> at file <span class="tm_file">%s</span>, line <span class="tm_line">%d</span></div>',
			$error_text, 'footer.php', $this->body_close_tag_line + 1 );
				} 
			}
		} else {
			$error_text = __( 'No wp_footer or closing body tag found', 'dx_theme_mentor' );
			$this->error_message[] = sprintf( '<div class="tm_report_row"><span class="tm_message">%s</span> at file <span class="tm_file">%s</span></div>',
					$error_text, 'footer.php' );
		}
	}
	
	/**
	 * Describe to the regular human being what's going on, if anything
	*/
	public function get_description() {
		if( empty( $this->error_message ) ) {
			return '';
		}
		
		$out = '';
		if( is_array( $this->error_message ) ) {
			foreach( $this->error_message as $error ) {
				$out .= $error;
			}
		}
		
		return $out;
	}
	
}

Mentor_Themeforest::$validations[] = new Footer_Validations();