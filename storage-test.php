<?php
// Storage Test for InfinityFree Hosting
// Place this file in your root directory and access it via browser

echo "<h2>Laravel Storage Diagnostic Test</h2>";

// Test 1: Check if storage directories exist
echo "<h3>1. Directory Structure Check</h3>";
$storageApp = __DIR__ . '/storage/app';
$storagePublic = __DIR__ . '/storage/app/public';
$publicStorage = __DIR__ . '/public/storage';

echo "storage/app exists: " . (is_dir($storageApp) ? "✅ YES" : "❌ NO") . "<br>";
echo "storage/app/public exists: " . (is_dir($storagePublic) ? "✅ YES" : "❌ NO") . "<br>";
echo "public/storage exists: " . (is_dir($publicStorage) ? "✅ YES" : "❌ NO") . "<br>";

if (is_dir($publicStorage)) {
    echo "public/storage is: " . (is_link($publicStorage) ? "Symbolic Link" : "Regular Directory") . "<br>";
}

// Test 2: Check write permissions
echo "<h3>2. Write Permission Check</h3>";
if (is_dir($storageApp)) {
    echo "storage/app writable: " . (is_writable($storageApp) ? "✅ YES" : "❌ NO") . "<br>";
}
if (is_dir($storagePublic)) {
    echo "storage/app/public writable: " . (is_writable($storagePublic) ? "✅ YES" : "❌ NO") . "<br>";
}

// Test 3: Try to create a test file
echo "<h3>3. File Creation Test</h3>";
$testFile = $storagePublic . '/test-file.txt';
$testContent = "Test file created at " . date('Y-m-d H:i:s');

try {
    if (!is_dir($storagePublic)) {
        mkdir($storagePublic, 0755, true);
        echo "Created storage/app/public directory<br>";
    }
    
    $result = file_put_contents($testFile, $testContent);
    if ($result !== false) {
        echo "✅ Test file created successfully in storage/app/public<br>";
        echo "File size: " . $result . " bytes<br>";
        
        // Check if accessible via web
        $webUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . 
                  '://' . $_SERVER['HTTP_HOST'] . '/storage/test-file.txt';
        echo "Test URL: <a href='$webUrl' target='_blank'>$webUrl</a><br>";
        
        // Clean up
        if (file_exists($testFile)) {
            unlink($testFile);
            echo "Test file cleaned up<br>";
        }
    } else {
        echo "❌ Failed to create test file<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 4: PHP Info (relevant parts)
echo "<h3>4. PHP Environment</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
echo "File Uploads Enabled: " . (ini_get('file_uploads') ? 'YES' : 'NO') . "<br>";

echo "<h3>5. Recommendations</h3>";
if (!is_dir($publicStorage)) {
    echo "❌ Missing public/storage link. You need to create it manually or use alternative storage.<br>";
}
if (!is_writable($storagePublic)) {
    echo "❌ Storage directory not writable. Contact hosting provider or use alternative storage.<br>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>If storage/app/public is not writable, you may need to use public folder directly</li>";
echo "<li>If public/storage link is missing and can't be created, use public folder storage</li>";
echo "<li>Consider using a custom disk configuration for InfinityFree</li>";
echo "</ul>";
?>
