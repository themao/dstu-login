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
*/

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

function parse_request() {
	if (isset($_REQUEST['dstu'])) {
		// much hardcode!
		$_REQUEST['dstu'] = 's=BECZOkOtnZyLFfk7nm20iMZ53Im6d93N+ABtVUUqI0QsOgHIPy+SkyqVHpYvZZldFGoYSBpuFqfMQ6NXjRkI7vQg&
d=http://enodev.org&
c=-----BEGIN CERTIFICATE-----
MIIGRzCCBe+gAwIBAgIUfPhaEUqGYbEEAAAA8UcHAJxDCAAwDQYLKoYkAgEBAQED
AQEwgasxJTAjBgNVBAoMHNCi0J7QkiAi0JDQoNCiLdCc0JDQodCi0JXQoCIxDzAN
BgNVBAsMBtCm0KHQmjE4MDYGA1UEAwwv0KbQodCaICJNQVNURVJLRVkiINCi0J7Q
kiAi0JDQoNCiLdCc0JDQodCi0JXQoCIxFzAVBgNVBAUMDlVBLTMwNDA0NzUwLTAx
MQswCQYDVQQGEwJVQTERMA8GA1UEBwwI0JrQuNGX0LIwHhcNMTMwODIxMTEyNTQz
WhcNMTUwODIxMTEyNTQzWjCCAR8xOTA3BgNVBAoMMNCf0LXRgtGA0L7QsiDQhtC7
0LvRjyDQntC70LXQutGB0LDQvdC00YDQvtCy0LjRhzEuMCwGA1UEDAwl0JLQu9Cw
0YHQvdC+0YDRg9GH0L3QuNC5INC/0ZbQtNC/0LjRgTEdMBsGA1UEAwwU0J/QtdGC
0YDQvtCyINCGLtCeLiAxFTATBgNVBAQMDNCf0LXRgtGA0L7QsjEsMCoGA1UEKgwj
0IbQu9C70Y8g0J7Qu9C10LrRgdCw0L3QtNGA0L7QstC40YcxDzANBgNVBAUMBjQ3
NzE2OTELMAkGA1UEBhMCVUExFzAVBgNVBAcMDtC8LiDQntC00LXRgdCwMRcwFQYD
VQQIDA7QntC00LXRgdGM0LrQsDCB8jCByQYLKoYkAgEBAQEDAQEwgbkwdTAHAgIB
AQIBDAIBAAQhEL7j22rqnh+GV4xFwSWU/5QjlKfXOPkYfmUVAXKU9M4BAiEAgAAA
AAAAAAAAAAAAAAAAAGdZITrxgumH0+F3FJB9Rw0EIbYP0tjc6Kk0I8YQG8qRxHoA
fmwwCybNVWybDn0g7ykqAARAqdbrRfE8cIKAxJZ7Ix9erfZY66TANykdONlr8CXK
Thf46XINxhW0OiiXXwvB3qNkOLVk6iwXn9ASPm24+sV5BAMkAAQh/UwNiU5OFqLH
7aTCYarjT5BUxKM6e7rZgHfSZuQeih0Bo4IC2zCCAtcwKQYDVR0OBCIEINayMui1
IM7yDgGClgq+RKdnjmNtuaYM8kbs0ShGz98PMCsGA1UdIwQkMCKAIHz4WhFKhmGx
W256tv1VLaUx3vuP8o8IrYly6IU4vNloMC8GA1UdEAQoMCagERgPMjAxMzA4MjEx
MTI1NDNaoREYDzIwMTUwODIxMTEyNTQzWjAOBgNVHQ8BAf8EBAMCA8gwGwYDVR0l
AQH/BBEwDwYNKoYkAgEBAQuOv+EOKzAZBgNVHSABAf8EDzANMAsGCSqGJAIBAQEC
AjAMBgNVHRMBAf8EAjAAMB4GCCsGAQUFBwEDAQH/BA8wDTALBgkqhiQCAQEBAgEw
eAYDVR0RBHEwb6BMBgwrBgEEAYGXRgEBBAKgPAw60LwuINCe0LTQtdGB0LAsINCy
0YPQuy4g0JrQsNC90LTRgNCw0YjQuNC90LAsIDE1LCDQutCyLiAxMqAfBgwrBgEE
AYGXRgEBBAGgDwwNMDYzLTE5NS0zNS0yMDBFBgNVHR8EPjA8MDqgOKA2hjRodHRw
Oi8vY3JsLm1hc3RlcmtleS51YS9jYS9jcmxzL0NBLUYzRTMxRDJFLUZ1bGwuY3Js
MEYGA1UdLgQ/MD0wO6A5oDeGNWh0dHA6Ly9jcmwubWFzdGVya2V5LnVhL2NhL2Ny
bHMvQ0EtRjNFMzFEMkUtRGVsdGEuY3JsMEMGCCsGAQUFBwEBBDcwNTAzBggrBgEF
BQcwAYYnaHR0cDovL29jc3AubWFzdGVya2V5LnVhL3NlcnZpY2VzL29jc3AvMEEG
CCsGAQUFBwELBDUwMzAxBggrBgEFBQcwA4YlaHR0cDovL3RzcC5tYXN0ZXJrZXku
dWEvc2VydmljZXMvdHNwLzBFBgNVHQkEPjA8MBwGDCqGJAIBAQELAQQCATEMEwoz
MjI1ODEzODczMBwGDCqGJAIBAQELAQQBATEMEwozMjI1ODEzODczMA0GCyqGJAIB
AQEBAwEBA0MABECKtrDwddPDo7mxULsZyorz7uW8kBw9DXQWLNbr9SUefjpOdbtJ
3bruYVnfNPAMPf0y1YdPS7JhJFWeZsutdOMx
-----END CERTIFICATE-----';

		$result = dstu_get_parsed('http://localhost:8013/api/0/check', $_REQUEST['dstu']);

		$salt = wp_salt('secure_auth');
		$login = 'dstu_' . md5($result['1.2.804.2.1.1.1.11.1.4.1.1'] . $salt);
		$name = explode(' ', $result['GN']);
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
		// and finally redirect to a specified destination
		$redirect = !empty($_REQUEST['redirect']) ? urldecode($_REQUEST['redirect']) : 'http://' . $_SERVER['SERVER_NAME'];
		wp_redirect($redirect);
		exit;
	}
}

add_action( 'parse_request', 'parse_request' );
