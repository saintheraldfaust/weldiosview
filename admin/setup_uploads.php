<?php
/**
 * Upload Directory Setup Script
 * Run this once on your cPanel server to create required directories
 */

// Required directories
$directories = [
    '../uploads/',
    '../uploads/student_photos/',
    '../uploads/certificates/',
    '../uploads/certificate_images/'
];

echo "<h2>Setting up upload directories...</h2>";

foreach ($directories as $dir) {
    echo "<p>Checking directory: <strong>$dir</strong></p>";
    
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<span style='color: green;'>✓ Directory created successfully</span><br>";
        } else {
            echo "<span style='color: red;'>✗ Failed to create directory</span><br>";
        }
    } else {
        echo "<span style='color: blue;'>ℹ Directory already exists</span><br>";
    }
    
    // Check permissions
    if (is_writable($dir)) {
        echo "<span style='color: green;'>✓ Directory is writable</span><br>";
    } else {
        echo "<span style='color: orange;'>⚠ Directory may not be writable - check permissions</span><br>";
        
        // Try to set permissions
        if (chmod($dir, 0755)) {
            echo "<span style='color: green;'>✓ Permissions set to 755</span><br>";
        } else {
            echo "<span style='color: red;'>✗ Failed to set permissions</span><br>";
        }
    }
    
    echo "<hr>";
}

echo "<h3>Manual Steps for cPanel:</h3>";
echo "<ol>";
echo "<li>Login to your cPanel File Manager</li>";
echo "<li>Navigate to your website root directory</li>";
echo "<li>Create an 'uploads' folder if it doesn't exist</li>";
echo "<li>Inside 'uploads', create these folders:";
echo "<ul>";
echo "<li>student_photos</li>";
echo "<li>certificates</li>";
echo "<li>certificate_images</li>";
echo "</ul></li>";
echo "<li>Right-click each folder → Change Permissions → Set to 755 or 777</li>";
echo "<li>Make sure your PHP has write permissions to these directories</li>";
echo "</ol>";

echo "<h3>Directory Structure Should Look Like:</h3>";
echo "<pre>";
echo "your-website-root/
├── uploads/
│   ├── student_photos/
│   ├── certificates/
│   └── certificate_images/
├── admin/
│   ├── students.php
│   └── certificates.php
└── other files...
</pre>";

// Display current PHP upload settings
echo "<h3>Current PHP Upload Settings:</h3>";
echo "<ul>";
echo "<li>upload_max_filesize: " . ini_get('upload_max_filesize') . "</li>";
echo "<li>post_max_size: " . ini_get('post_max_size') . "</li>";
echo "<li>max_file_uploads: " . ini_get('max_file_uploads') . "</li>";
echo "<li>file_uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "</li>";
echo "</ul>";

echo "<p><strong>Note:</strong> Delete this file after setup for security reasons.</p>";
?>