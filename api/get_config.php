<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Files needed to use objects
require(dirname(__FILE__) . '/objects/config.php');
require(dirname(__FILE__) . '/objects/log.php');
require(dirname(__FILE__) . '/objects/admin.php');

// Create variables
$config = new Config();
$admin = new Admin();
$log = new Log();
$data = json_decode(file_get_contents("php://input"));

// If POST data is empty
if(empty($data) || !isset($data->cookie)) {

	// Log use
	$log->log_activity('get_config.php', 'unknown', 'No admin login cookie provided.');

    echo json_encode(array("error" => true, "message" => "No cookie provided."));
    exit(0);
	
}

// Remove potential harmfull input
$cookie = htmlspecialchars($data->cookie);

// Check if confgiured
if(!$admin->is_configured()) {

	// Log use
	$log->log_activity('get_config.php', 'unknown', 'Wrapperr admin is not configured.');

    echo json_encode(array("error" => true, "message" => "Wrapperr admin is not configured."));
    exit(0);

} else if(!$config->is_configured()) {

    // Log use
	$log->log_activity('get_config.php', 'unknown', 'Wrapperr is not configured.');

    echo json_encode(array("error" => false, "message" => "Wrapperr is not configured.", "wrapperr_configured" => false, "data" => $config, "admin" => $admin->username));
    exit(0);

} 

// Decrypt cookie
$cookie_object = json_decode($admin->decrypt_cookie($cookie));

// Validate admin cookie
if(!$admin->validate_cookie($cookie_object)) {
    
	// Log use
	$log->log_activity('get_config.php', 'unknown', 'Admin cookie not valid.');

    echo json_encode(array("error" => true, "message" => "Admin cookie not accepted. Log in again."));
    exit(0);
	
}

// Log use
$log->log_activity('get_config.php', 'admin', 'Retrieved Wrapperr configuration.');

echo json_encode(array("error" => false, "message" => "Config retrieved.", "wrapperr_configured" => true, "data" => $config, "admin" => $admin->username));
exit(0);
?>