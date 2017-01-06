<?php

/**
 * Plugin Name: Theme Mentor For ThemeForest
 * Description: Theme Mentor is a cousing of the Theme-Check plugin getting deeper into the code analysis.
 * It's using different approaches to monitor for common problems regarding theme reviews from the
 * WordPress Theme Reviewers Team. It is prone to fault analysis, so use only as a reference for improving
 * your code base even further.
 * Plugin URI: https://github.com/Ataurr/Theme-Mentor-For-Themeforest
 * Version: 0.1
 * Author:  Ataurr, nofearinc
 * Author URI: http://xpeedstudio.com/
 * License: GPLv2 or later
 *
 */
/*
 * Copyright (C) 2013 Mario Peshev

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * */

define( 'TM_PLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'TM_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'TM_INC_PATH', trailingslashit( TM_PLUGIN_PATH . 'inc' ) );
define( 'TM_INC_URL', trailingslashit( TM_PLUGIN_URL . 'inc' ) );


// if isset the option for complex checks, load them as well in the process of evaluation

/**
 * The main class for the plugin, initializing everything needed and including all tests
 *
 * @author nofearinc
 *
 */
class Mentor_Themeforest {

	private $templates			 = array();
	private $includes			 = array();
	private $theme_path			 = '';
	public static $validations	 = array();

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'theme_mentor_page' ) );
		add_action( 'dx_theme_mentor_before_tests_list', array( $this, 'display_theme_name_tested' ) );
		// TODO: temporary
// 		$this->do_everything();
	}

	public function run_tests() {
		// all the heavy lifting for picking up proper files from the theme folder
		// for templates and includes, that is
		$this->iterate_theme_folder( $this->theme_path, 0 );

		// swap functions.php as it's include-alike
		$functions_file = $this->theme_path . 'functions.php';
		foreach ( $this->templates as $index => $template ) {
			if ( $template === $functions_file ) {
				unset( $this->templates[ $index ] );
				$this->includes[] = $this->theme_path . 'functions.php';
			}
		}
		// Include check files
		include TM_INC_PATH . 'general-theme-validations.php';
		$general_validations = new General_Theme_Validations();

		// Include complex checks
		include TM_PLUGIN_PATH . 'theme-mentor-executor.php';
		$dir = 'inc/complex';
		foreach ( glob( dirname( __FILE__ ) . "/{$dir}/*.php" ) as $file ) {
			include $file;
		}

		// iterate all templates
		foreach ( $this->templates as $index => $template ) {
			// only unique theme stuff
			$template_unique_only = str_replace( $this->theme_path, '', $template );

			// read the files, keep the file number as it matters, you know
			$file = file( $template, FILE_IGNORE_NEW_LINES );
			if ( false === $file ) {
				continue;
			}

			// General
			foreach ( $general_validations->common_validations as $pattern => $message ) {
				$this->iterate_data( $pattern, $message, $template_unique_only, $file );
			}

			foreach ( $general_validations->template_validations as $pattern => $message ) {
				$this->iterate_data( $pattern, $message, $template_unique_only, $file );
			}

			foreach ( self::$validations as $validation ) {
				$validation->crawl( $template, $file );
			}
		}

		// iterate includes
		foreach ( $this->includes as $index => $functional ) {
			// only unique theme stuff
			$functional_unique_only = str_replace( $this->theme_path, '', $functional );

			if ( !file_exists( $functional ) ) {
				continue;
			}
			// read the files, keep the file number as it matters, you know
			$file = file( $functional, FILE_IGNORE_NEW_LINES );
			if ( false === $file ) {
				continue;
			}

			// General
			foreach ( $general_validations->common_validations as $pattern => $message ) {
				$this->iterate_data( $pattern, $message, $functional_unique_only, $file );
			}

			foreach ( $general_validations->include_validations as $pattern => $message ) {
				$this->iterate_data( $pattern, $message, $functional_unique_only, $file );
			}
		}

		// display complex validations errors
		foreach ( self::$validations as $validation ) {
			$validation->execute();
			$validation_description = $validation->get_description();

			if ( !empty( $validation_description ) ) {
				echo $validation_description;
			}
		}
	}

	/**
	 * Adapt the Theme Mentor page
	 */
	public function theme_mentor_page() {
		$page = add_theme_page( 'Mentor Themeforest', 'Mentor Themeforest', 'manage_options', 'mentor_themeforest', array( $this, 'theme_mentor_page_cb' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'styles_theme_mentor' ) );
	}

	/**
	 * Admin page callback
	 */
	public function theme_mentor_page_cb() {
		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __( 'We all know you shouldn\'t be here', 'dx_theme_mentor' ) );
		}

		// get stylesheet to pick the selected theme
		$stylesheet	 = get_stylesheet();
		// default activated theme is selected atfirst
		$selected	 = $stylesheet;
		// list all themes
		$themes		 = wp_get_themes();

		echo '<div class="wrap xs-wrap">';
		echo '<div class="xs-header">';
		echo '<h2>' . __( 'Theme Mentor For Themeforest', 'dx_theme_mentor' ) . '</h2>';

		echo '<div class="xs-clear"></div><div class="xs-info">';
		// is the form submitted
		//Get the theme name for add within select box
		if ( isset( $_POST[ 'dx_theme' ] ) ) {
			$theme_name = $_POST[ 'dx_theme' ];
			if ( isset( $themes[ $theme_name ] ) ) {
				$theme		 = $themes[ $theme_name ];
				// selected is the last submitted to $_POST
				$selected	 = $theme->get_stylesheet();
			}
		}
		//Print the validations issue
		do_action( 'dx_theme_mentor_before_admin_page' );
		include_once 'inc/templates/admin-template.php';
		do_action( 'dx_theme_mentor_after_admin_page' );
		echo '</div>';

		// add screenshot 
		if ( isset( $_POST[ 'dx_theme' ] ) ) {
			$screenshot = trailingslashit( $themes[ $theme_name ]->get_template_directory_uri() );

			echo '<div id="icon-edit" class="icon32-base-template theme-screenshot"><br><img class="xs-screenshot" src="' . $screenshot . 'screenshot.png" alt="" /></div>';
		}
		echo '</div>';


		do_action( 'dx_theme_mentor_before_tests_list' );

		// is the form submitted
		if ( isset( $_POST[ 'dx_theme' ] ) ) {
			$theme_name = $_POST[ 'dx_theme' ];

			if ( isset( $themes[ $theme_name ] ) ) {
				$theme				 = $themes[ $theme_name ];
				$this->theme_path	 = trailingslashit( $theme->get_template_directory() );

				$this->run_tests();
			}
		}



		echo '</div>';
	}

	/**
	 * Iterate theme folder and assign templates and includes
	 * @param string $folder folder path
	 * @param int $level depth of the nesting
	 */
	public function iterate_theme_folder( $folder, $level = 0 ) {
		// get all templates
		$folder		 = trailingslashit( $folder );
		$directory	 = dir( $folder );

		/* A list of folders excluded from the check. */
		$excluded_folders = apply_filters( 'theme_mentory_excluded_folders', array() );

		if ( in_array( basename( $folder ), $excluded_folders ) ) {
			return;
		}

		while ( false !== ( $entry = $directory->read() ) ) {
			// drop all empty folders, hidden folders/files and parents
			if ( ( $entry[ 0 ] == "." ) )
				continue;

			// includes should be there
			if ( is_dir( $folder . $entry ) ) {
				// iterate the next level
				$this->iterate_theme_folder( $folder . $entry, $level + 1 );
			} else {
				// read only PHP files
				if ( substr( $entry, -4, 4 ) === '.php' ) {
					if ( $level === 0 ) {
						// templates on level 0
						$this->templates[] = $folder . $entry;
					} else {
						// includes
						$this->includes[] = $folder . $entry;
					}
				}
			}
		}
	}

	/**
	 * Do the regex for the possibly dangerous snippets
	 *
	 * @param regex $pattern
	 * @param error message text $message
	 * @param path to file when something happened $file_path
	 * @param file to run after $file
	 */
	public function iterate_data( $pattern, $message, $file_path, $file ) {
		$lines_found = preg_grep( $pattern, $file );
		if ( !empty( $lines_found ) ) {
			foreach ( $lines_found as $line => $snippet ) {
				printf( '<div class="tm_report_row"><span class="tm_message">%s</span> at file <span class="tm_file">%s</span>, line <span class="tm_line">%d</span>: <span class="tm_snippet">%s</span></div>', $message, $file_path, $line + 1, esc_html( $snippet ) );
			}
		}
	}

	// list all errors
	// lookout for the errors
	// admin panel for theme list

	/**
	 * Enqueue styles for admin
	 */
	public function styles_theme_mentor() {
		wp_enqueue_style( 'theme-mentor', TM_PLUGIN_URL . 'css/theme-mentor.css' );
	}

	public function display_theme_name_tested() {
		if ( !empty( $_POST[ 'dx_theme' ] ) ) {
			$testing_word = __( 'Testing', 'dx_theme_mentor' );
			printf( '<h2>%s %s...</h2>', $testing_word, $_POST[ 'dx_theme' ] );
		}
	}

}

// Make things happen.
new Mentor_Themeforest();
