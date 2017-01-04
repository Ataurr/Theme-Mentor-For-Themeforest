<?php

/**
 * Interface for the complex executions
 * 
 * Reads all files during their iteration and gathers data
 * 
 * @author nofearinc
 *
 */
interface Theme_Mentor_Executor {
		/**
		 * Reading the file (or files, if called a couple times) and collecting data in an array
		 * @param string $filename filename
		 * @param string $file content of the file
		 */
		public function crawl( $filename, $file );
		
		/**
		 * Aggregating the data if needed after the iteration process, like stats, some array management, etc
		 */
		public function execute( );
		
		/**
		 * Describe to the regular human being what's going on, if anything
		 */
		public function get_description();
}