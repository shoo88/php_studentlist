<?php
session_start();

// ✅ Enable error reporting (for development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Check if user is logged in
if (!isset($_SESSION['logged_in_user'])) {
    header("Location: student_login.php");
    exit();
}

// ✅ Database connection
$host = 'localhost';
$user = 'root';
$password = ''; // Change if needed
$database = 'student_management';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ Handle image upload
    $imagePath = "";
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $filename = uniqid() . "_" . basename($_FILES["profile_picture"]["name"]);
        $targetFile = $targetDir . $filename;
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile);
        $imagePath = $targetFile;
    }

    // ✅ Sanitize form data
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $image = $conn->real_escape_string($imagePath);

    // ✅ Insert student into DB (ID auto-incremented)
    $sql = "INSERT INTO students (name, address, phone, email, image)
            VALUES ('$name', '$address', '$phone', '$email', '$image')";

    if ($conn->query($sql) === TRUE) {
        header("Location: student_list.php?msg=Student+added+successfully");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('https://t4.ftcdn.net/jpg/06/51/44/67/360_F_651446718_ZIHGEFxdN1ZSP2hVmtembHIpShrOUo3e.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0;
            min-height: 100vh;
            padding-top: 50px;
            padding-bottom: 50px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .container {
            width: 400px;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #0B2E33;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"],
        input[type="email"],
        input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        input[type="submit"] {
            background-color: #0B2E33;
            color: white;
            width: 100%;
            padding: 10px;
            border: none;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #09424a;
        }
        .link {
            text-align: center;
            margin-top: 15px;
        }
        .link a {
            color: #0B2E33;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Add Student</h2>
    <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('Are you sure you want to submit this student?');">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Address:</label>
        <input type="text" name="address" required>

        <label>Phone:</label>
        <input type="text" name="phone" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Profile Picture:</label>
        <input type="file" name="profile_picture" accept="image/*">

        <input type="submit" value="Submit">
    </form>
    <div class="link">
        <a href="student_list.php">📋 View All Students</a>
    </div>
    <div class="link">
        <a href="logout.php">🚪 Logout</a>
    </div>
</div>
</body>
</html>
