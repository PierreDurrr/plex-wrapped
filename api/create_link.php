<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Files needed to use objects
require(dirname(__FILE__) . '/objects/auth.php');
require(dirname(__FILE__) . '/objects/config.php');
require(dirname(__FILE__) . '/objects/log.php');
require(dirname(__FILE__) . '/objects/link.php');

// Create variables
$auth = new Auth();
$config = new Config();
$link = new Link();
$log = new Log();
$data = json_decode(file_get_contents("php://input"));

// If POST data is empty
if(empty($data)) {

	// Log use
	$log->log_activity('create_link.php', 'unknown', 'No input provided.');

    echo json_encode(array("error" => true, "message" => "No input provided."));
    exit(0);
	
}

// Check if confgiured
if(!$config->is_configured()) {

	// Log use
	$log->log_activity('create_link.php', 'unknown', 'Wrapperr is not configured.');

    echo json_encode(array("error" => true, "message" => "Wrapperr is not configured.", "password" => false, "data" => array()));
    exit(0);

}

// Check if link creation is allowed
if(!$config->create_share_links) {

	// Log use
	$log->log_activity('create_link.php', 'unknown', 'Wrapperr does not allow link creation in config.');

    echo json_encode(array("error" => true, "message" => "Wrapperr option for link creation not enabled."));
    exit(0);

}

// Remove potential harmfull input
$cookie = htmlspecialchars($data->cookie);
$wrapped_data = $data->data;
$wrapped_functions = $data->functions;

// Get Plex Token
$token_object = json_decode($auth->validate_token($cookie));

// Validate Plex ID
if(empty($token_object) || !isset($token_object->data->id)) {
    
	// Log use
	$log->log_activity('create_link.php', 'unknown', 'Plex Token from cookie not valid. Could not create link.');

    echo json_encode(array("error" => true, "message" => "Login not accepted. Try again."));
    exit(0);
	
}

// Assign values from Plex Token
$id = $token_object->data->id;

// Get the current date
$now = new DateTime('NOW');

// Create random URL value
$random = md5(rand(0,1000));

$url_hash = $id . '-' . $random;

//Create link content
$link_content = array("url_hash" => $url_hash, "id" => $id, "date" => $now->format('Y-m-d'), "wrapperr_version" => $config->wrapperr_version, "data" => $wrapped_data, "functions" => $wrapped_functions);

// Save the content to file
$link->save_link($link_content, $id);

// Log use
$log->log_activity('create_link.php', $id, 'Created Wrapperr link.');

// Return URL generated
echo json_encode(array("error" => false, "message" => "Link created.", "url" => "?hash=" . $url_hash));
exit(0);
?>