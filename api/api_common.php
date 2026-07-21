<?php
// Common API settings: disable HTML error display and return JSON on errors
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

// Ensure JSON content-type (endpoints may override)
header('Content-Type: application/json; charset=utf-8');

// Standard exception handler that returns JSON
set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error', 'detail' => $e->getMessage()]);
    exit;
});

// Standard error handler that returns JSON
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error', 'detail' => $errstr]);
    exit;
});

// Helper for sending JSON and exiting
function api_json($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}
