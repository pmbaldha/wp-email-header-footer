<?php
/*
Plugin Name: Email Header Footer
Plugin URI:  https://github.com/pmbaldha/wp-current-location-on-map/
Description: Email Header Footer is a wordpress plugin that lets you customize email by setting header and footer of email.
Version:     1.0
Author:      Prashant Baldha
Author URI:  https://github.com/pmbaldha/
License:     GPL2
Email Header Footer is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Email Header Footer is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Email Header Footer. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/*
 * This function is for adding plugin setting configuration page under setting in wordpress admin panel
 */
add_action('admin_menu', 'ehf_add_setting_page');
function ehf_add_setting_page() {                        
    add_options_page('Email Header Footer', 'Email Header Footer', 'manage_options', 'ehf_setting', 'ehf_options_page'); //Add setting page in wordpress admin panel
}

/*
 * This function is for setting content of setting content of setting page
 */
function ehf_options_page() {
?>
	<div class="wrap">
		<h1>Email Header Footer</h1>
		<form method="POST" action="options.php">
			<?php settings_fields( 'ehf_setting' );	//pass slug name of page, also referred
													    //to in Settings API as option group name
			do_settings_sections( 'ehf_setting' ); 	//pass slug name of page
			submit_button();
			?>
		</form>
	</div>
<?php
}
/*
 * This function and hook are registering setting section, setting and all setting field
 */
add_action( 'admin_init', 'ehf_settings_api_init' );
function ehf_settings_api_init() {
	/*
	 * Add the section to ehf_setting setting page so we can add our
	 * fields to it
	 */
	add_settings_section(
		'ehf_setting_section',
		'Configure Email Header Footer',
		'ehf_setting_section_callback_function',
		'ehf_setting'
	);
	
	/*
	 * Register our setting so that $_POST handling is done for us and our configuration will be saved
	 */
	register_setting( 'ehf_setting', 'ehf_setting' );
	
	/*
	 * Add Email Header
	 */
	add_settings_field(
		'ehf_email_header',
		'Email Header',
		'ehf_email_header_callback_function',
		'ehf_setting',
		'ehf_setting_section'
	);
	
	/*
	 * Add Email Footer
	 */
	add_settings_field(
		'ehf_email_footer',
		'Email Footer',
		'ehf_email_footer_callback_function',
		'ehf_setting',
		'ehf_setting_section'
	);
 }
 
/*
 * ehf_setting_section callback function
 * This function is needed if we added a new section. This function 
 * will be run at the start of our section
 */
function ehf_setting_section_callback_function() {
	echo '<p>The below setting will be configured for email header footer</p>';
}
 
/*
 * Callback function for email header field
 * This function renders a WYSIWYG editor
 */
function ehf_email_header_callback_function() {
	$options = get_option('ehf_setting');
	if( isset($options['email_header']) ) {
		$email_header = $options['email_header'];
	} else {
		$email_header = '';
	}
	wp_editor( $email_header, 
				'email_header',
				 array(
						'wpautop' => false, 
						'textarea_name' => 'ehf_setting[email_header]',
						'textarea_rows' => 7,
						'tabindex' => 1,
					)
			);
	echo '<p class="description">This content will be set in header part of email.
			<br/>
			<b>Short Tags:</b> 
			<br/>
			&nbsp;&nbsp;&nbsp;
			[site-title] - places site title.
			<br/>
			&nbsp;&nbsp;&nbsp;
			[tag-line] - places site tag ine
			<br/>
			&nbsp;&nbsp;&nbsp;
			[site-url] - places site url
			<br/>
			&nbsp;&nbsp;&nbsp;
			[admin-email] - places admin email
			<br/>
			&nbsp;&nbsp;&nbsp;
			[year] - places current year
			</p>';
 }
 
/*
 * Callback function for email footer field
 * This function renders a WYSIWYG editor
 */
function ehf_email_footer_callback_function() {
	$options = get_option('ehf_setting');
	if( isset($options['email_footer']) ) {
		$email_footer =  $options['email_footer'] ;
	} else {
		$email_footer = '';
	}
	wp_editor( $email_footer, 
				'email_footer',
				array(
						'wpautop' => false, 
						'textarea_name' => 'ehf_setting[email_footer]',
						'textarea_rows' => 7,
						'tabindex' => 2,
					)
			);
	echo '<p class="description">This content will be set in footer part of email.</p>
			<b>Short Tags:</b> 
			<br/>
			&nbsp;&nbsp;&nbsp;
			[site-title] - places site title.
			<br/>
			&nbsp;&nbsp;&nbsp;
			[tag-line] - places site tag ine
			<br/>
			&nbsp;&nbsp;&nbsp;
			[site-url] - places site url
			<br/>
			&nbsp;&nbsp;&nbsp;
			[admin-email] - places admin email
			<br/>
			&nbsp;&nbsp;&nbsp;
			[year] - places current year
			</p>';
 }
 
/*
 * For short tag replacement
 */
function ehf_replace_short_tags( $content = '' ) {
	$arr_replace = array(
    				 'site-title'	=> get_bloginfo( 'title' ),
    				 'tag-line' 	=> get_bloginfo( 'description' ),
					 'site-url' 	=> '<a href="'.get_bloginfo( 'wpurl' ).'">'.get_bloginfo( 'wpurl' ).'</a>' ,
				     'admin-email'	=> get_option( 'admin_email' ),
   					 'year' 		=>  get_date_from_gmt( gmdate('Y-m-d H:i:s'), 'Y' ),
	);
	
	foreach( $arr_replace as $key=>$val ) {
		$content = str_replace('['.$key.']', $val, $content );
	}
	return $content;
}
	
/*
 * Add header and footer in mail when sending email
 */
function ehf_email_before_send( $orig_email ) {
	$email = $orig_email;
	$options = get_option('ehf_setting');
	if( isset($options['email_header']) ) {
		$email_header = ehf_replace_short_tags( $options['email_header'] );
	} else {
		$email_header = '';
	}
	if( isset($options['email_footer']) ) {
		$email_footer = ehf_replace_short_tags( $options['email_footer'] );
	} else {
		$email_footer = '';
	}
	
	$is_pre_need =  false;
	$lt_count = substr_count( '<', $email['message']);
	$gt_count = substr_count( '>', $email['message']);
	if( $lt_count == 0 && $gt_count == 0 ) {
		$is_pre_need =  true;
	}
	
	//Forcontent type  text/plain mails, make display email proper
	if( $is_pre_need ) {
		$orig_email['message'] = $email_header.'<pre>'.strip_tags($email['message']).'</pre>'.$email_footer;
	}
	else {
		$orig_email['message'] = $email_header.$email['message'].$email_footer;
	}
	
	return $orig_email;
}
add_filter( 'wp_mail', 'ehf_email_before_send' );

/**
 * Filter the mail content type.
 */
function ehf_set_html_mail_content_type( $content_type ) {	
	if( $content_type == 'text/plain' ) {
    	return 'text/html';
	}
	else {
		return $content_type;
	}
}
add_filter( 'wp_mail_content_type', 'ehf_set_html_mail_content_type' );

/*
 * Email retrieve password link not display resolution
 */
function ehf_retrieve_password_message( $message, $key ){
	return str_replace( array('<','>'), '', $message);
}
add_filter( 'retrieve_password_message', 'ehf_retrieve_password_message', 1, 2 );

