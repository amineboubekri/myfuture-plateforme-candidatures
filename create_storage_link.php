<?php
// This script creates the storage symlink on the web server
// Access this file via your web browser after uploading

echo "<h2>Laravel Storage Link Creator</h2>";

// Check if we're in the right directory
if (!file_exists('artisan')) {
    echo "<p style='color: red;'>Error: This script must be placed in the Laravel root directory (same level as artisan file).</p>";
    exit;
}

// Paths
$target = __DIR__ . '/storage/app/public';
$link = __DIR__ . '/public/storage';

echo "<p><strong>Target:</strong> " . $target . "</p>";
echo "<p><strong>Link:</strong> " . $link . "</p>";

// Check if target exists
if (!is_dir($target)) {
    echo "<p style='color: red;'>Error: Target directory does not exist: " . $target . "</p>";
    exit;
}

// Remove existing link/directory if it exists
if (file_exists($link)) {
    if (is_link($link)) {
        unlink($link);
        echo "<p style='color: orange;'>Removed existing symlink.</p>";
    } elseif (is_dir($link)) {
        rmdir($link);
        echo "<p style='color: orange;'>Removed existing directory.</p>";
    } else {
        unlink($link);
        echo "<p style='color: orange;'>Removed existing file.</p>";
    }
}

// Create the symlink
if (symlink($target, $link)) {
    echo "<p style='color: green;'>✓ Storage symlink created successfully!</p>";
} else {
    // If symlink fails, try to create a regular directory and copy files
    echo "<p style='color: orange;'>Symlink failed. Trying alternative approach...</p>";
    
    if (!mkdir($link, 0755, true)) {
        echo "<p style='color: red;'>Failed to create storage directory.</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ Storage directory created. Files will be copied instead of symlinked.</p>";
    echo "<p style='color: orange;'>Note: You may need to manually copy files from storage/app/public to public/storage when needed.</p>";
}

// Test the setup
echo "<h3>Testing Setup:</h3>";

// Check if documents directory exists
$documentsDir = $target . '/documents';
if (!is_dir($documentsDir)) {
    if (mkdir($documentsDir, 0755, true)) {
        echo "<p style='color: green;'>✓ Documents directory created.</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create documents directory.</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Documents directory exists.</p>";
}

// Check permissions
if (is_writable($target)) {
    echo "<p style='color: green;'>✓ Storage directory is writable.</p>";
} else {
    echo "<p style='color: red;'>✗ Storage directory is not writable. Check permissions.</p>";
}

if (is_writable($documentsDir)) {
    echo "<p style='color: green;'>✓ Documents directory is writable.</p>";
} else {
    echo "<p style='color: red;'>✗ Documents directory is not writable. Check permissions.</p>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Delete this file from your server for security</li>";
echo "<li>Test document upload and download functionality</li>";
echo "<li>If symlink didn't work, ask your hosting provider about symlink support</li>";
echo "</ul>";

echo "<p><em>Generated at: " . date('Y-m-d H:i:s') . "</em></p>";
?>
