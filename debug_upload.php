<?php
// Advanced Upload Error Debugging

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Upload Error Debug</h1>";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['test_file'])) {
    echo "<h2>Upload Attempt Results:</h2>";
    
    $file = $_FILES['test_file'];
    
    echo "<p><strong>File Info:</strong><br>";
    echo "Name: " . htmlspecialchars($file['name']) . "<br>";
    echo "Type: " . htmlspecialchars($file['type']) . "<br>";
    echo "Size: " . $file['size'] . " bytes<br>";
    echo "Error Code: " . $file['error'] . "<br>";
    echo "Temp Name: " . htmlspecialchars($file['tmp_name']) . "</p>";
    
    // Check for upload errors
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            echo "<p style='color: green;'>No upload errors detected.</p>";
            break;
        case UPLOAD_ERR_INI_SIZE:
            echo "<p style='color: red;'>ERROR: File exceeds upload_max_filesize (" . ini_get('upload_max_filesize') . ")</p>";
            break;
        case UPLOAD_ERR_FORM_SIZE:
            echo "<p style='color: red;'>ERROR: File exceeds MAX_FILE_SIZE directive</p>";
            break;
        case UPLOAD_ERR_PARTIAL:
            echo "<p style='color: red;'>ERROR: File was only partially uploaded</p>";
            break;
        case UPLOAD_ERR_NO_FILE:
            echo "<p style='color: red;'>ERROR: No file was uploaded</p>";
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            echo "<p style='color: red;'>ERROR: Missing temporary folder</p>";
            break;
        case UPLOAD_ERR_CANT_WRITE:
            echo "<p style='color: red;'>ERROR: Failed to write file to disk</p>";
            break;
        case UPLOAD_ERR_EXTENSION:
            echo "<p style='color: red;'>ERROR: A PHP extension stopped the upload</p>";
            break;
        default:
            echo "<p style='color: red;'>ERROR: Unknown upload error</p>";
            break;
    }
    
    if ($file['error'] == UPLOAD_ERR_OK) {
        // Try to move the file
        $target_dir = __DIR__ . '/storage/app/public/documents/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0775, true);
        }
        
        $target_file = $target_dir . time() . '_' . basename($file['name']);
        
        echo "<p>Attempting to move file to: " . htmlspecialchars($target_file) . "</p>";
        
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            echo "<p style='color: green;'>SUCCESS: File uploaded successfully!</p>";
            // Clean up
            unlink($target_file);
        } else {
            echo "<p style='color: red;'>FAILED: Could not move uploaded file. Check directory permissions.</p>";
        }
    }
    
} else {
    echo "<h2>Upload Test Form</h2>";
    echo "<p>Use this form to test file uploads and see the exact error:</p>";
    echo "<form method='POST' enctype='multipart/form-data'>";
    echo "<input type='file' name='test_file' accept='.pdf,.jpg,.jpeg,.png' required><br><br>";
    echo "<input type='submit' value='Test Upload'>";
    echo "</form>";
    
    echo "<p><strong>Current PHP Settings:</strong><br>";
    echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
    echo "post_max_size: " . ini_get('post_max_size') . "<br>";
    echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";
    echo "memory_limit: " . ini_get('memory_limit') . "</p>";
}

echo "<p style='color: red; font-weight: bold;'>IMPORTANT: Delete this file after debugging!</p>";
?>
