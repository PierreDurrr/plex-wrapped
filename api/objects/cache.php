<?php
class Cache{

    // Object properties
    // Cache path
    private $path;

    // Constructor
    public function __construct(){

        // Delcare cache path
        $this->path = $_SERVER['DOCUMENT_ROOT'] . '/config/cache.json';

        // Check if cache file exists, if not, create it
        if(!file_exists($this->path)) {
            @$create_cache = fopen($this->path, "w");
            if(!$create_cache) {
                echo json_encode(array("message" => "Failed to create cache.json. Is the 'config' directory writable?", "error" => true));
                exit();
            }
            fclose($create_cache);
        }

    }

    public function clear_cache() {
        
        // Try to open cache
        @$cache = fopen($this->path, "w");

        if(!$cache) {
            return false;
        } else {
            fwrite($cache, "");
            fclose($cache);
            return true;
        }
    }

}