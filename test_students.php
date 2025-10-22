<?php
require_once 'config/config.php';

try {
    $pdo = getDBConnection();
    
    // Count all students
    $stmt = $pdo->query('SELECT COUNT(*) FROM students');
    $total = $stmt->fetchColumn();
    echo 'Total students: ' . $total . PHP_EOL;
    
    // Count students with certificates
    $stmt = $pdo->query('SELECT COUNT(DISTINCT s.id) FROM students s JOIN certificates c ON s.id = c.student_id');
    $with_certs = $stmt->fetchColumn();
    echo 'Students with certificates: ' . $with_certs . PHP_EOL;
    
    // Count students without certificates
    $stmt = $pdo->query('SELECT COUNT(s.id) FROM students s LEFT JOIN certificates c ON s.id = c.student_id WHERE c.id IS NULL');
    $without_certs = $stmt->fetchColumn();
    echo 'Students without certificates: ' . $without_certs . PHP_EOL;
    
    // Show first few students
    $stmt = $pdo->query('SELECT s.id, s.surname, s.first_name, COUNT(c.id) as cert_count FROM students s LEFT JOIN certificates c ON s.id = c.student_id GROUP BY s.id ORDER BY s.id LIMIT 5');
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo PHP_EOL . 'First 5 students:' . PHP_EOL;
    foreach($students as $student) {
        echo 'ID: ' . $student['id'] . ', Name: ' . $student['first_name'] . ' ' . $student['surname'] . ', Certs: ' . $student['cert_count'] . PHP_EOL;
    }
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>