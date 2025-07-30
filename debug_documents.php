<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DOCUMENT FILE PATH DIAGNOSTIC ===\n\n";

// Get first 10 documents from database
$documents = \App\Models\Document::take(10)->get();

if ($documents->isEmpty()) {
    echo "No documents found in database.\n";
    exit;
}

echo "Found " . $documents->count() . " documents to check:\n\n";

foreach ($documents as $doc) {
    echo "Document ID: {$doc->id}\n";
    echo "Document Type: {$doc->document_type}\n";
    echo "File Path (from DB): {$doc->file_path}\n";
    
    // Build the full path using Laravel's public_path helper
    $fullPath = public_path($doc->file_path);
    echo "Full System Path: {$fullPath}\n";
    
    // Check if file exists
    $exists = file_exists($fullPath);
    echo "File Exists: " . ($exists ? "YES" : "NO") . "\n";
    
    if ($exists) {
        $size = filesize($fullPath);
        echo "File Size: " . number_format($size) . " bytes\n";
        $readable = is_readable($fullPath);
        echo "File Readable: " . ($readable ? "YES" : "NO") . "\n";
    } else {
        // Check if directory exists
        $dir = dirname($fullPath);
        $dirExists = is_dir($dir);
        echo "Directory Exists: " . ($dirExists ? "YES" : "NO") . " ({$dir})\n";
        
        // List files in directory if it exists
        if ($dirExists) {
            $files = scandir($dir);
            $files = array_diff($files, ['.', '..']);
            echo "Files in directory: " . implode(', ', array_slice($files, 0, 5)) . "\n";
        }
    }
    
    echo "Status: {$doc->status}\n";
    echo "Uploaded At: {$doc->uploaded_at}\n";
    echo str_repeat('-', 50) . "\n\n";
}

// Show Laravel public path
echo "Laravel public_path(): " . public_path() . "\n";
echo "Current working directory: " . getcwd() . "\n";

// Check uploads directory
$uploadsDir = public_path('uploads');
echo "Uploads directory: {$uploadsDir}\n";
echo "Uploads directory exists: " . (is_dir($uploadsDir) ? "YES" : "NO") . "\n";

if (is_dir($uploadsDir)) {
    $uploadFiles = scandir($uploadsDir);
    $uploadFiles = array_diff($uploadFiles, ['.', '..']);
    echo "Files in uploads directory: " . count($uploadFiles) . " files\n";
    echo "First 10 files: " . implode(', ', array_slice($uploadFiles, 0, 10)) . "\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
