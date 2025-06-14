<?php
// index.php for NarcosNET

// Set a higher memory limit if needed, though ideally, the refactored code is efficient.
// ini_set('memory_limit', '256M'); // Example, adjust if necessary

// Basic error reporting (consider more robust logging in a production environment)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Off for production, on for debugging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log'); // Ensure this path is writable

// Autoloading - Adjust if you have a central autoloader (e.g., Composer)
// For now, a simple require should work if Server.php and SlotSettings.php are in the same directory
// and GameReel.php is also handled or included within SlotSettings.php.
// If GameReel is in the same dir and not namespaced, it might be found.
// If it's namespaced or in a different path, this will need adjustment.

// Assuming SlotSettings.php and GameReel.php are correctly included/loaded by Server.php or handled by namespace
require_once __DIR__ . '/Server.php';
require_once __DIR__ . '/SlotSettings.php'; // Required because Server news it up
require_once __DIR__ . '/GameReel.php'; // Required because SlotSettings news it up

// The namespace for Server class is VanguardLTE\Games\NarcosNET
use VanguardLTE\Games\NarcosNET\Server;

// Create an instance of the Server
\$server = new Server();

// Call the handle method to process the request
\$server->handle();

```
