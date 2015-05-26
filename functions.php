<?php

/* JPEG compression */
function jpeg_quality_callback( $arg ) {
	return (int)75;
}
add_filter( 'jpeg_quality', 'jpeg_quality_callback' );
add_filter( 'wp_editor_set_quality', 'jpeg_quality_callback' );

/* Remove unnecessary header information */
function remove_header_info() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'remove_header_info' );

/* Remove wp version meta tag and from rss feed */
add_filter('the_generator', '__return_false');

/* Remove wp version param from any enqueued scripts */
function remove_wp_ver_css_js( $src ) {
	if ( strpos( $src, '?ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'style_loader_src', 'remove_wp_ver_css_js', 10, 2 );
add_filter( 'script_loader_src', 'remove_wp_ver_css_js', 10, 2 );

/* Disable ping back scanner and complete xmlrpc class. */
add_filter( 'wp_xmlrpc_server_class', '__return_false' );
add_filter( 'xmlrpc_enabled', '__return_false' );

/* Remove xpingback header */
function remove_x_pingback( $headers ) {
    unset( $headers['X-Pingback'] );
    return $headers;
}
add_filter( 'wp_headers', 'remove_x_pingback' );

/*
* User login by e-mail
* ispired by http://blog.doprofilu.cz/tipy-triky/wordpress-prihlaseni-pomoci-emailu.html
* and http://wordpress.stackexchange.com/questions/161709/customize-the-registration-complete-please-check-your-e-mail-message-on-wp-4
*/
function email_login_auth( $user, $username, $password ) {
	if ( is_email( $username ) ) {
		$user_by_email = get_user_by( 'email', $username );
		if ( $user_by_email instanceof WP_User ) {
			$user = null;
			$username = $user_by_email->user_login;
		}
	}
	return wp_authenticate_username_password( $user, $username, $password );
}
add_filter( 'authenticate', 'email_login_auth', 20, 3 );

function email_login_auth_label( $translated_text, $untranslated_text, $domain ) {
	if ( $untranslated_text == 'Username' ) {
		$translated_text .= ' / Email';
		remove_filter( current_filter(), __FUNCTION__ );
	}
	return $translated_text;
}
add_filter( 'login_init',
	function() {
		add_filter( 'gettext', 'email_login_auth_label', 99, 3 );
	}
);
