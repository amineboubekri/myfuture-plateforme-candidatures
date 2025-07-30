<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Document;

echo "Checking document file paths in database:\n";
echo "=========================================\n\n";

$documents = Document::select('id', 'file_path', 'name', 'created_at')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

if ($documents->isEmpty()) {
    echo "No documents found in the database.\n";
} else {
    foreach ($documents as $doc) {
        echo "ID: {$doc->id}\n";
        echo "Name: {$doc->name}\n";
        echo "File Path: {$doc->file_path}\n";
        echo "Created: {$doc->created_at}\n";
        
        // Check if file actually exists
        $fullPath = public_path($doc->file_path);
        $exists = file_exists($fullPath) ? 'YES' : 'NO';
        echo "Full Path: {$fullPath}\n";
        echo "File Exists: {$exists}\n";
        echo "---\n\n";
    }
}
