<?php
// Path: /home/adam/webapps/scripts/public/opti2/test.php

// Basic test script to confirm PHP processing
echo "<h1>LiteSpeed Test Script</h1>";
echo "<p>The server is properly processing PHP files in this directory.</p>";
echo "<hr>";
echo "<h2>Server Information:</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "</pre>";