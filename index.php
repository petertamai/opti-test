<?php
/**
 * Index file for root directory
 * This file simply forwards all requests to the public/index.php file
 */

// Define the root path
define('APP_ROOT', __DIR__);

// Forward to the actual index.php in the public directory
require_once __DIR__ . '/public/index.php';