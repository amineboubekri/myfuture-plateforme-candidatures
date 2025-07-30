<?php
// Comprehensive Server/Hosting Environment Check for Laravel

header('Content-Type: text/html; charset=utf-8');

function check_status($condition, $success_msg, $fail_msg) {
    if ($condition) {
        echo "<li style='color: green;'>✓ $success_msg</li>";
        return true;
    } else {
        echo "<li style='color: red; font-weight: bold;'>✗ $fail_msg</li>";
        return false;
    }
}

function get_ini_value($key) {
    $val = ini_get($key);
    return $val ? $val : 'Not Set';
}

function get_perms($path) {
    if (!file_exists($path)) return 'N/A';
    return substr(sprintf('%o', fileperms($path)), -4);
}

echo "<!DOCTYPE html><html><head><title>Server Check for MyFuture Platform</title>";
echo "<style>body { font-family: sans-serif; background-color: #f5f5f5; padding: 20px; } .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); } h1, h2 { border-bottom: 1px solid #eee; padding-bottom: 10px; } ul { list-style-type: none; padding-left: 0; } li { padding: 5px 0; }</style>";
echo "</head><body><div class='container'>";

echo "<h1>Server Environment Check</h1>";
echo "<p>This script checks if your server environment is correctly configured for the MyFuture Laravel application, focusing on file upload issues.</p>";

// --- PHP Configuration ---
echo "<h2>1. PHP Configuration (php.ini)</h2><ul>";
$upload_max = get_ini_value('upload_max_filesize');
$post_max = get_ini_value('post_max_size');
check_status($upload_max && (int)$upload_max >= 5, "upload_max_filesize is $upload_max (Good, >= 5M)", "upload_max_filesize is $upload_max (should be at least 5M for your validation rules)");
check_status($post_max && (int)$post_max >= 5, "post_max_size is $post_max (Good, >= 5M)", "post_max_size is $post_max (should be at least 5M and >= upload_max_filesize)");
echo "</ul>";

// --- PHP Extensions ---
echo "<h2>2. Required PHP Extensions</h2><ul>";
check_status(extension_loaded('fileinfo'), "Fileinfo extension is enabled", "Fileinfo extension is DISABLED. This is required by Laravel for file validation and is a likely cause of the problem.");
check_status(extension_loaded('gd'), "GD extension is enabled", "GD extension is disabled (needed for image processing).");
check_status(extension_loaded('mbstring'), "Mbstring extension is enabled", "Mbstring extension is disabled.");
echo "</ul>";

// --- Directory Permissions ---
echo "<h2>3. Directory and Permissions Check</h2><ul>";
$storage_path = __DIR__ . '/storage';
$storage_app_public_path = $storage_path . '/app/public';
$documents_path = $storage_app_public_path . '/documents';
$public_storage_path = __DIR__ . '/public/storage';

echo "<h3>Storage Directories (MUST BE WRITABLE)</h3><ul>";
$storage_writable = check_status(is_dir($storage_path) && is_writable($storage_path), "storage directory exists and is writable. (Perms: " . get_perms($storage_path) . ")", "storage directory is missing or NOT WRITABLE. THIS IS A MAJOR PROBLEM.");
$app_public_writable = check_status(is_dir($storage_app_public_path) && is_writable($storage_app_public_path), "storage/app/public directory exists and is writable. (Perms: " . get_perms($storage_app_public_path) . ")", "storage/app/public directory is missing or NOT WRITABLE. THIS IS A MAJOR PROBLEM.");
$docs_writable = check_status(is_dir($documents_path) && is_writable($documents_path), "storage/app/public/documents directory exists and is writable. (Perms: " . get_perms($documents_path) . ")", "storage/app/public/documents directory is missing or NOT WRITABLE. THIS IS THE MOST LIKELY CAUSE OF THE UPLOAD ERROR.");

// Attempt to create the documents directory if it doesn't exist
if (!is_dir($documents_path)) {
    echo "<li style='color: blue;'>Attempting to create documents directory...</li>";
    if (@mkdir($documents_path, 0775, true)) {
        check_status(true, "Successfully created the storage/app/public/documents directory.", "");
    } else {
        check_status(false, "", "Failed to automatically create the documents directory. Please create it manually via FTP or your hosting file manager and ensure it has 775 permissions.");
    }
}
echo "</ul>";

echo "<h3>Public Symlink</h3><ul>";
check_status(file_exists($public_storage_path), "public/storage link/directory exists.", "public/storage link is MISSING. You must run `php artisan storage:link` on your server or create it manually.");
if (is_link($public_storage_path)) {
    echo "<li style='color: green;'>✓ public/storage is a symlink (Correct setup).</li>";
} else if(is_dir($public_storage_path)) {
    echo "<li style='color: orange;'>⚠ public/storage is a directory, not a symlink. This may work but is not standard. It means your hosting likely doesn't support symlinks.</li>";
}
echo "</ul>";

// --- Write Test ---
echo "<h2>4. Live Write Test</h2><ul>";
if ($docs_writable) {
    $test_file_path = $documents_path . '/test_write.txt';
    if (@file_put_contents($test_file_path, 'test')) {
        check_status(true, "Successfully wrote a test file to the documents directory.", "");
        @unlink($test_file_path);
    } else {
        check_status(false, "", "LIVE WRITE FAILED. Even though the directory appears writable, the server blocked the write operation. Check for other security policies like `open_basedir` or contact hosting support.");
    }
} else {
     check_status(false, "", "Skipping live write test because documents directory is not writable.");
}
echo "</ul>";

echo "<h2>Conclusion</h2>";
echo "<p>Review the checks above. Any items marked with a red '✗' are likely causing your file upload error. The most common issues are incorrect directory permissions or low PHP `upload_max_filesize` limits.</p>";
echo "<p style='font-weight: bold; color: red;'>IMPORTANT: For security, please delete this file (server_check.php) from your server after you have finished diagnosing the issue.</p>";

echo "</div></body></html>";

?>
