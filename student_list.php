<?php
session_start();

// ✅ Ensure user is logged in
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
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Fetch students
$students = [];
$result = $conn->query("SELECT * FROM students ORDER BY id");
if ($result && $result->num_rows > 0) {
    $students = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('https://images.unsplash.com/photo-1577017040065-650ee4d43339?fm=jpg&q=60&w=3000');
            background-size: cover;
            background-attachment: fixed;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start; /* Align content to top */
            min-height: 100vh;
        }

        h2 {
            text-align: center;
            color: #0B2E33;
            margin-bottom: 10px; /* Reduced gap */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: rgba(255,255,255,0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-top: 10px; /* Reduced gap */
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #0B2E33;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        img.profile {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }

        .action-links a {
            margin: 0 5px;
            text-decoration: none;
            color: #007BFF;
        }

        .action-links a:hover {
            text-decoration: underline;
        }

        .add-link, .export-link {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 16px;
            background-color: white;
            color: #007BFF;
            border: 1px solid #007BFF;
            border-radius: 5px;
            text-decoration: none;
            transition: 0.3s;
        }

        .add-link:hover, .export-link:hover {
            background-color: #007BFF;
            color: white;
        }

        .no-data {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            border-radius: 5px;
        }

        /* Add Student Link at Bottom-Right */
        .add-link {
            position: fixed;
            bottom: 20px;
            right: 20px;
        }

        /* Export Excel Link at Bottom-Right */
        .export-link {
            position: fixed;
            bottom: 70px; /* Positioned above the Add Student link */
            right: 20px;
        }

        .actions-bar {
            text-align: center;
            margin-top: 20px;
        }

    </style>
</head>
<body>

<h2>Student List</h2>

<?php if (!empty($students)): ?>
    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>ID</th>
                <th>Name</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $student): ?>
            <tr>
                <td>
                    <?php if (!empty($student['image']) && file_exists($student['image'])): ?>
                        <img class="profile" src="<?= htmlspecialchars($student['image']) ?>" alt="Profile">
                    <?php else: ?>
                        <span>No Image</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($student['id']) ?></td>
                <td><?= htmlspecialchars($student['name']) ?></td>
                <td><?= htmlspecialchars($student['address']) ?></td>
                <td><?= htmlspecialchars($student['phone']) ?></td>
                <td><?= htmlspecialchars($student['email']) ?></td>
                <td class="action-links">
                    <a href="edit_student.php?id=<?= urlencode($student['id']) ?>" onclick="return confirmEdit();">✏️</a>
                    <a href="delete_student.php?id=<?= urlencode($student['id']) ?>" onclick="return confirmDelete();">🗑️</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="no-data">No students found.</div>
<?php endif; ?>

<!-- Add Student Link at the Bottom-Right -->
<a href="student_form.php" class="add-link">➕ Add Student</a>

<!-- Export to Excel Link at the Bottom-Right -->
<a href="export_students.php" class="export-link">📊 Export to Excel</a>

<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this student?");
}
function confirmEdit() {
    return confirm("Are you sure you want to edit this student?");
}
</script>

</body>
</html>
