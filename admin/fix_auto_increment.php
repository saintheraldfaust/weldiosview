<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirectToLogin();
}

try {
    $pdo = getDBConnection();
    
    // Get the current maximum ID from students table
    $stmt = $pdo->query("SELECT MAX(id) as max_id FROM students");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $max_id = $result['max_id'] ?? 0;
    
    // Set AUTO_INCREMENT to be one more than the current maximum ID
    $next_auto_increment = $max_id + 1;
    
    // Fix the AUTO_INCREMENT value
    $stmt = $pdo->prepare("ALTER TABLE students AUTO_INCREMENT = ?");
    $stmt->execute([$next_auto_increment]);
    
    echo "✅ Fixed students table AUTO_INCREMENT. Next ID will be: " . $next_auto_increment . "<br>";
    
    // Also fix certificates table if needed
    $stmt = $pdo->query("SELECT MAX(id) as max_id FROM certificates");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $max_id = $result['max_id'] ?? 0;
    $next_auto_increment = $max_id + 1;
    
    $stmt = $pdo->prepare("ALTER TABLE certificates AUTO_INCREMENT = ?");
    $stmt->execute([$next_auto_increment]);
    
    echo "✅ Fixed certificates table AUTO_INCREMENT. Next ID will be: " . $next_auto_increment . "<br>";
    
    echo "<br><a href='students.php'>← Back to Students</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>