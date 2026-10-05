<?php

/**
 * Router for PHP's built-in server
 * 
 * Usage: php -S localhost:8000 router.php
 */

// Rewrite to index.php if the file doesn't exist
if (preg_match('/\.(?:png|jpg|jpeg|gif|ico|css|js|svg|webp|woff|woff2|ttf|eot)$/', $_SERVER['REQUEST_URI'])) {
    // Serve static files directly
    return false;
}

// Block access to sensitive directories
if (preg_match('#^/(content|site|kirby)/.*#', $_SERVER['REQUEST_URI'])) {
    if (!preg_match('#^/content/.*\.(jpg|jpeg|png|gif|svg|webp|pdf)$#', $_SERVER['REQUEST_URI'])) {
        http_response_code(403);
        echo 'Forbidden';
        return true;
    }
}

// Route everything else through index.php
require_once 'index.php';
