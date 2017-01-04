<p><?php _e(' Pick a theme from your theme directories.', 'dx_theme_mentor' ); ?></p>

<div id="theme_mentor_admin_wrapper">
	<form id="dx_theme_mentor_form" action="" method="POST">
		<p>
			<label for="dx_theme"><?php _e( 'Themes:', 'dx_theme_mentor' ); ?></label>
			<select id="dx_theme" name="dx_theme">
				<?php foreach( $themes as $theme => $details ) {  ?>
					<option value="<?php echo $theme; ?>" <?php selected( $details['Stylesheet'], $selected ); ?>><?php echo $details->get('Name'); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			Extra options (if any, like complex enabled)
		</p>
		<?php
		submit_button( __( 'Do the Twist!', 'dx_theme_mentor' ) );
		?>
	</form> <!-- end of #dx_theme_mentor -->
</div>
