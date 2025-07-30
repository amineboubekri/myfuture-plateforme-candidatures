<?php

/**
 * MyFuture Platform - Entry Point
 * 
 * This file serves as the main entry point for the MyFuture application.
 * It redirects all requests to the proper Laravel public directory.
 * 
 * For production environments, it's recommended to configure your web server
 * to point directly to the public/ directory instead of using this file.
 */

// Check if we're accessing the application through the root directory
if (php_sapi_name() === 'cli-server') {
    // PHP built-in server
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    
    // If the request is for a static file in the public directory, serve it directly
    if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
        return false; // Let the built-in server handle static files
    }
}

// Define the public directory path
$publicPath = __DIR__ . '/public';

// Check if the public directory exists
if (!is_dir($publicPath)) {
    // If public directory doesn't exist, show an error
    http_response_code(500);
    echo "
    <!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Erreur - MyFuture</title>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white; 
                margin: 0; 
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .error-container {
                text-align: center;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 3rem;
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
                border: 1px solid rgba(255, 255, 255, 0.15);
            }
            h1 { font-size: 2.5rem; margin-bottom: 1rem; }
            p { font-size: 1.1rem; line-height: 1.6; margin-bottom: 1rem; }
            .icon { font-size: 4rem; margin-bottom: 1rem; opacity: 0.8; }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <div class='icon'>⚠️</div>
            <h1>Configuration Requise</h1>
            <p>Le répertoire public de l'application Laravel est introuvable.</p>
            <p>Veuillez vous assurer que l'application Laravel est correctement installée.</p>
            <p><strong>Répertoire attendu:</strong> $publicPath</p>
        </div>
    </body>
    </html>
    ";
    exit;
}

// Store the original script name and document root
$originalScriptName = $_SERVER['SCRIPT_NAME'];
$originalDocumentRoot = $_SERVER['DOCUMENT_ROOT'];

// Update server variables for Laravel
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $publicPath . '/index.php';
$_SERVER['DOCUMENT_ROOT'] = $publicPath;

// Change working directory to public
chdir($publicPath);

// Check if Laravel's index.php exists
$laravelIndex = $publicPath . '/index.php';
if (!file_exists($laravelIndex)) {
    http_response_code(500);
    echo "
    <!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Erreur - MyFuture</title>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white; 
                margin: 0; 
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .error-container {
                text-align: center;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 3rem;
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
                border: 1px solid rgba(255, 255, 255, 0.15);
            }
            h1 { font-size: 2.5rem; margin-bottom: 1rem; }
            p { font-size: 1.1rem; line-height: 1.6; margin-bottom: 1rem; }
            .icon { font-size: 4rem; margin-bottom: 1rem; opacity: 0.8; }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <div class='icon'>🚫</div>
            <h1>Application Introuvable</h1>
            <p>Le fichier d'entrée Laravel est introuvable.</p>
            <p>Veuillez vous assurer que l'application Laravel est correctement installée.</p>
            <p><strong>Fichier attendu:</strong> $laravelIndex</p>
        </div>
    </body>
    </html>
    ";
    exit;
}

// Include and run Laravel's index.php
require $laravelIndex;
