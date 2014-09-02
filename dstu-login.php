<?php
/**
 * @package dstu_login
 * @version 0.1b
 */
/*
Plugin Name: DSTU Login
Description: Login with a DSTU certificate
Author: Anton Martynenko, Ilya Petrov
Version: 0.1b
Author URI: http://dstu.enodev.org/
Text Domain: dstu-plugin
*/

function dstu_plugin_init() {
    $plugin_dir = basename(dirname(__FILE__)) . '/locales/';
    load_plugin_textdomain( 'dstu-login', false, $plugin_dir );
}

/**
 * Get response from dstu daemon as an array
 * 
 * @param string $url The URL of a validation daemon
 * @param string $post Data that should be sent with POST request to a daemon
 * @return array Parsed output transformed to array
 * */
function dstu_get_parsed($url, $post) {
    $result = array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $response = curl_exec($ch);

    foreach (explode("\n", $response) as $item) {
        $field = explode('=', $item);
        if ($field[0]) {
            $result[$field[0]] = $field[1];
        }
    }

    curl_close($ch);

    return $result;
}

function dstu_get_cert($cert_id) {

    $url = get_option('cert_base', DEFAULT_DSTU_CERT_BASE) . $cert_id;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CAINFO, plugin_dir_path( __FILE__ ) . "ca-bundle.crt");

    $res = curl_exec($ch);

    curl_close($ch);

    return $res;
}

function hide_login_form($classes) {
    array_push($classes, 'dstu-hidden');
    return $classes;
}

function dstu_login_form() {
    $app_id = get_option('app_id');

    if($app_id) {
        $wp_nonce = wp_create_nonce('dstu-login');
        echo '<p><a href="https://eusign.org/auth/' . $app_id. 
             '?state=' . $wp_nonce. '" class="dstu-button">' . 
             __('Sign with eU', 'dstu-login') . '</a></p>';
    }
}

function dstu_admin_init() {
    dstu_init_settings();
}

function dstu_init_settings() {
    register_setting('dstu-login-group', 'app_id');
    register_setting('dstu-login-group', 'auth_url');
    register_setting('dstu-login-group', 'cert_base');

    define('DEFAULT_DSTU_AUTH_URL', site_url('/wp-login.php', 'https'));
    define('DEFAULT_DSTU_CERT_BASE', 'https://eusign.org/api/1/certificates/');
}

function dstu_add_menu() {
    add_options_page(
        __('DSTU Login Settings', 'dstu-login'),
        __('DSTU Login plugin', 'dstu-login'),
        'manage_options',
        'dstu-login',
        'dstu_settings_page'
    );
}

function dstu_settings_page() {
    if(!current_user_can('manage_options'))
    {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    include(sprintf("%s/templates/settings.php", plugin_dir_path( __FILE__ )));
}


function dstu_authenticate($user) {
    if (!isset($_REQUEST['sign']) ||
        !isset($_REQUEST['cert_id']) ||
        !isset($_REQUEST['state']) ||
        !isset($_REQUEST['nonce'])) {
        return $user;
    }

    if(!wp_verify_nonce($_REQUEST['state'], 'dstu-login')) {
        return $user;
    }

    $sign = $_REQUEST['sign'];
    $sign = str_replace('-', '+', $sign);
    $sign = str_replace('_', '/', $sign);

    $cert = dstu_get_cert($_REQUEST['cert_id']);

    $data = $_REQUEST['nonce'] . '|'. get_option('auth_url', DEFAULT_DSTU_AUTH_URL);

    $for_api = "s=" . $sign . "&c=" . $cert . "&d=" . $data;

    $result = dstu_get_parsed('http://localhost:8013/api/0/check', $for_api);
    if(isset($result['CN'])) {
        $user = dstu_create_login($result);
    }

    return $user;
}

function dstu_create_login($result) {

    $salt = wp_salt('secure_auth');
    $uniq = null;
    $full_name = null;

    if(isset($result['1.2.804.2.1.1.1.11.1.4.1.1'])) {
        $uniq = $result['1.2.804.2.1.1.1.11.1.4.1.1'];
    } else {
        $uniq = $result['CN'];
    }

    if(isset($result['GN'])) {
        $full_name = $result['GN'];
    } else {
        $full_name = $result['CN'];
    }

    $login = 'dstu_' . md5($salt . $uniq);
    $name = explode(' ', $full_name);

    $userData = array(
        'user_login' => $login,
        'user_pass' => md5($login . $salt),
        'first_name' => $name[0],
        'last_name' => $result['SN'],
        'display_name' => $result['CN'],
        'nickname' => $result['CN'],
    );

    // check for an existing user
    $user = get_user_by('login', $login);
    // create if not exists
    if (!$user) {
        $user_id = wp_insert_user($userData);
    }
    // or get the ID of existing
    else {
        $user_id = $user_id->ID;
    }
    // authenticate and remember
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    return $user;
}

add_action('login_body_class', 'hide_login_form');
add_action('login_form', 'dstu_login_form');
add_action('authenticate', 'dstu_authenticate', 10, 1);

add_action('admin_init', 'dstu_admin_init');
add_action('admin_menu', 'dstu_add_menu');
add_action('plugins_loaded', 'dstu_plugin_init');
