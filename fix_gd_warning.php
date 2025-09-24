<?php
// PHP Configuration Checker for GD Extension Issue

echo "=== PHP Configuration Analysis ===\n\n";

// Show PHP version
echo "PHP Version: " . PHP_VERSION . "\n";

// Show configuration file paths
echo "Configuration File (php.ini) Path: " . php_ini_loaded_file() . "\n";
echo "Additional .ini files parsed: " . (function_exists('php_ini_scanned_dir') ? php_ini_scanned_dir() : 'N/A') . "\n\n";

// Check if GD extension is loaded
if (extension_loaded('gd')) {
    echo "✅ GD Extension is loaded\n";
    $gd_info = gd_info();
    echo "GD Version: " . $gd_info['GD Version'] . "\n\n";
} else {
    echo "❌ GD Extension is NOT loaded\n\n";
}

// Check for duplicate extension loading
echo "=== Checking for duplicate GD loading ===\n";
$ini_file = php_ini_loaded_file();
if ($ini_file && file_exists($ini_file)) {
    $content = file_get_contents($ini_file);
    $gd_lines = [];
    $lines = explode("\n", $content);
    
    foreach ($lines as $line_num => $line) {
        $line = trim($line);
        if (strpos($line, 'extension=gd') !== false || 
            strpos($line, 'extension=php_gd') !== false) {
            $gd_lines[] = "Line " . ($line_num + 1) . ": " . $line;
        }
    }
    
    if (count($gd_lines) > 1) {
        echo "⚠️  Found multiple GD extension declarations:\n";
        foreach ($gd_lines as $line) {
            echo "   " . $line . "\n";
        }
        echo "\n💡 Solution: Comment out duplicate lines by adding ; at the beginning\n";
    } else if (count($gd_lines) == 1) {
        echo "✅ Found single GD extension declaration:\n";
        echo "   " . $gd_lines[0] . "\n";
    } else {
        echo "ℹ️  No explicit GD extension declaration found (might be built-in)\n";
    }
} else {
    echo "❌ Could not read php.ini file\n";
}

echo "\n=== Quick Fix Instructions ===\n";
echo "1. Open php.ini file in a text editor\n";
echo "2. Search for 'extension=gd' or 'extension=php_gd'\n";
echo "3. If you find multiple lines, comment out duplicates with ;\n";
echo "4. Save the file and restart Apache\n";
echo "\nExample:\n";
echo "   extension=gd      ; Keep this one\n";
echo "   ;extension=gd     ; Comment out duplicates like this\n";

echo "\n=== Alternative: Suppress Warning ===\n";
echo "Add this to the top of your PHP files:\n";
echo "error_reporting(E_ALL & ~E_WARNING);\n";
echo "Or add to php.ini: error_reporting = E_ALL & ~E_WARNING\n";
?>