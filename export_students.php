<?php
session_start();

if (!isset($_SESSION['logged_in_user'])) {
    header("Location: student_login.php");
    exit();
}

// DB connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'student_management';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Force UTF-8 for Excel compatibility
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="student_list.csv"');

// Output UTF-8 BOM for Excel (optional but improves compatibility)
echo "\xEF\xBB\xBF";

// Open output stream
$output = fopen('php://output', 'w');

// Column headers
fputcsv($output, ['ID', 'Name', 'Address', 'Phone', 'Email']);

// Fetch data and write rows
$query = "SELECT id, name, address, phone, email FROM students";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Clean data: remove line breaks and trim
        $cleaned = array_map(function($val) {
            return trim(preg_replace("/[\r\n]+/", " ", $val));
        }, $row);

        fputcsv($output, $cleaned);
    }
}

fclose($output);
$conn->close();
exit();
