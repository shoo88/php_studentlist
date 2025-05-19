<?php
session_start();

if (!isset($_SESSION['logged_in_user'])) {
    header("Location: student_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Student ID is missing.");
}

$studentId = (int) $_GET['id'];

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'student_management';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT * FROM students WHERE id = $studentId";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    die("Student not found.");
}

$student = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $address = $conn->real_escape_string(trim($_POST['address']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $imagePath = $student['image']; // default to existing image

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileSize = $_FILES['image']['size'];
        $fileType = mime_content_type($fileTmpPath);
        $allowedTypes = ['image/jpeg', 'image/png'];

        if (in_array($fileType, $allowedTypes) && $fileSize <= 2 * 1024 * 1024) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = uniqid('img_', true) . '.' . $ext;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Remove old image if exists
                if (!empty($student['image']) && file_exists($student['image'])) {
                    unlink($student['image']);
                }
                $imagePath = $destPath;
            }
        } else {
            $error = "Invalid image. Only JPG/PNG under 2MB allowed.";
        }
    }

    if (empty($error)) {
        $updateQuery = "UPDATE students SET 
            name = '$name',
            address = '$address',
            phone = '$phone',
            email = '$email',
            image = '$imagePath'
            WHERE id = $studentId";

        if ($conn->query($updateQuery)) {
            $_SESSION['message'] = "Student updated successfully.";
            header("Location: student_list.php");
            exit();
        } else {
            $error = "Error updating student: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .form-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            width: 420px;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 12px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        img.preview {
    display: block;
    margin-top: 10px;
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #ccc;
}


        input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #0B2E33;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        a.back {
            display: block;
            margin-top: 15px;
            text-align: center;
            text-decoration: none;
            color: #007BFF;
        }

        a.back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Student</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>

        <label>Address:</label>
        <input type="text" name="address" value="<?= htmlspecialchars($student['address']) ?>" required>

        <label>Phone:</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($student['phone']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>

        <label>Photo:</label>
        <input type="file" name="image" accept="image/*">
        <?php if (!empty($student['image']) && file_exists($student['image'])): ?>
            <img class="preview" src="<?= htmlspecialchars($student['image']) ?>" alt="Current Photo">
        <?php endif; ?>

        <input type="submit" value="Update Student">
    </form>

    <a class="back" href="student_list.php">← Back to Student List</a>
</div>

</body>
</html>
