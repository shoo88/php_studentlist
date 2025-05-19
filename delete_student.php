<?php
// delete_student.php
session_start();

// ✅ Check if user is logged in
if (!isset($_SESSION['logged_in_user'])) {
    header("Location: student_login.php");
    exit();
}

// ✅ Check if ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request. Student ID is missing.");
}

$studentId = intval($_GET['id']); // Sanitize ID

// ✅ Database connection
$host = 'localhost';
$user = 'root';
$password = ''; // Set your DB password if any
$database = 'student_management';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Step 1: Get student's image file path
$sql = "SELECT image FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// ✅ Step 2: Delete image file if it exists
if ($student && !empty($student['image'])) {
    $imagePath = $student['image'];
    if (file_exists($imagePath)) {
        unlink($imagePath); // Delete the image file from the server
    }
}

// ✅ Step 3: Delete student record from the database
$deleteSql = "DELETE FROM students WHERE id = ?";
$deleteStmt = $conn->prepare($deleteSql);
$deleteStmt->bind_param("i", $studentId);

if ($deleteStmt->execute()) {
    // Redirect with success message
    header("Location: student_list.php?msg=Student+deleted+successfully");
    exit();
} else {
    echo "Error deleting student: " . $conn->error;
}

$conn->close();
