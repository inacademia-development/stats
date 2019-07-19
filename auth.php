<?php

require_once('/var/simplesamlphp/lib/_autoload.php');
$as = new \SimpleSAML\Auth\Simple('default-sp');
$as->requireAuth();

$required_group = "GN4Phase3:WPs:WP5 T2";

$attributes = $as->getAttributes();   
$displayname = get_displayname($attributes);
$isuser_in_group = user_in_group($required_group, $attributes);

if (! $isuser_in_group) {
	print "You are not authorized to use this application. Please contact support@inacademia.org for more information.";
	exit();
}

function get_displayname($attributes) {
    return $attributes["urn:oid:2.16.840.1.113730.3.1.241"][0];
}

function user_in_group($group, $attributes) {
	if (in_array($group, $attributes["urn:oid:1.3.6.1.4.1.5923.1.5.1.1"])) {
		return true;
	} 
    return false;
}

