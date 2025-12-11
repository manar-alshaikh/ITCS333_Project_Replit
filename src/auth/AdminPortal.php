<?php
session_start();
include '../../config/Config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

$message = '';
$message_type = '';
$action = $_GET['action'] ?? 'dashboard';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    /* Change Password */
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);

                $message = "Password changed successfully!";
                $message_type = "success";
            } else {
                $message = "New passwords do not match!";
                $message_type = "error";
            }
        } else {
            $message = "Current password is incorrect!";
            $message_type = "error";
        }
    }

    /* Add Student */
    elseif (isset($_POST['add_student'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash('student123', PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'student')");
        $stmt->execute([$username, $password, $email]);

        $message = "Student added successfully! Default password: student123";
        $message_type = "success";
    }

    /* Update Student */
    elseif (isset($_POST['update_student'])) {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $email = $_POST['email'];

        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$username, $email, $id]);

        $message = "Student updated!";
        $message_type = "success";
    }

    /* Delete User */
    elseif (isset($_POST['delete_user'])) {
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        $message = "User deleted!";
        $message_type = "success";
    }
}

/* Fetch student list only for student view */
$students = [];
if ($action === 'students') {
    $stmt = $conn->query("SELECT * FROM users WHERE role = 'student'");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal</title>

    <!-- NEON THEME -->
    <link rel="stylesheet" href="../common/css/background.css">
    <link rel="stylesheet" href="../common/css/adminPortal.css">

    <!-- JS Background -->
    <script src="../common/js/background.js" defer></script>
</head>

<body>
<div class="bg-animation">
    <div class="neural-network" id="neuralNetwork"></div>
    <div class="particles" id="particles"></div>
</div>

<!-- HEADER -->
<div class="header">
    <h1>Admin Control Panel</h1>
</div>

<!-- NAVIGATION -->
<div class="nav">
    <div class="nav-left">
        <a href="AdminPortal.php?action=dashboard">Dashboard</a>
        <a href="AdminPortal.php?action=students">Students</a>
        <a href="AdminPortal.php?action=password">Password</a>
    </div>

    <div class="nav-right">
        <a href="logout.php">Logout</a>
    </div>
</div>

<!-- SUCCESS / ERROR MESSAGE -->
<?php if ($message): ?>
<div class="welcome-box" style="border-left: 4px solid <?php echo $message_type === 'success' ? '#00ff88' : '#ff4444'; ?>;">
    <p style="color: <?php echo $message_type === 'success' ? '#00ff88' : '#ff4444'; ?>;">
        <?php echo $message; ?>
    </p>
</div>
<?php endif; ?>

<!-- MAIN CONTENT -->
<div class="main-content">

<?php if ($action === 'dashboard'): ?>

    <!-- ===========================
         ADMIN DASHBOARD (NEW)
    ============================ -->

    <div class="welcome-box">
        <h2>Welcome back, <?php echo $_SESSION['username']; ?>!</h2>
        <p>Last login: <?php echo date("Y-m-d H:i:s"); ?></p>
    </div>

    <div class="dashboard-grid">

    <?php if (isAdmin() || isInstructor()): ?>
 <div class="card">
        <h3>Resources</h3>
        <p>Manage and organize all learning resources.</p>
        <a href="../resources/admin.php"><button>Manage Resources</button></a>
    </div>

    <div class="card">
        <h3>Weekly Breakdown</h3>
        <p>Create and manage weekly schedules and content.</p>
        <a href="../weekly/admin.php"><button>Manage Weekly</button></a>
    </div>

    <div class="card">
        <h3>Assignments</h3>
        <p>Create and manage assignments and grading.</p>
        <a href="../assignments/admin.php"><button>Manage Assignments</button></a>
    </div>

    <div class="card">
        <h3>General Discussion Boards</h3>
        <p>Moderate and manage all discussion boards.</p>
        <p>extra task (Not implemented)</p>
        <a href="#"><button>Manage Discussions</button></a>
    </div>
    <?php endif; ?>

    </div>

<?php elseif ($action === 'students'): ?>

    <!-- ===========================
         STUDENT MANAGEMENT
    ============================ -->

    <h2>Manage Students</h2>

    <div class="card" style="margin-bottom: 2rem;">
        <h3>Add New Student</h3>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br><br>
            <input type="email" name="email" placeholder="Email" required><br><br>
            <button type="submit" name="add_student">Add Student</button>
        </form>
        <small>Default password: <b>student123</b></small>
    </div>

    <?php if (count($students) > 0): ?>
        <table id="weeks-table">
            <tr>
                <th>ID</th><th>Username</th><th>Email</th><th>Actions</th>
            </tr>

            <?php foreach ($students as $student): ?>
            <tr>
                <td><?php echo $student['id']; ?></td>
                <td><?php echo $student['username']; ?></td>
                <td><?php echo $student['email']; ?></td>
                <td>
                    <button onclick="showEditForm(<?php echo $student['id']; ?>, '<?php echo $student['username']; ?>', '<?php echo $student['email']; ?>')">Edit</button>

                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                        <button type="submit" name="delete_user" class="danger" onclick="return confirm('Delete student?')">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>

        </table>
    <?php else: ?>
        <p>No students found.</p>
    <?php endif; ?>

    <div id="editForm" class="card" style="display:none; margin-top: 2rem;">
        <h3>Edit Student</h3>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <input type="text" name="username" id="edit_username" required><br><br>
            <input type="email" name="email" id="edit_email" required><br><br>
            <button type="submit" name="update_student">Update Student</button>
            <button type="button" onclick="hideEditForm()">Cancel</button>
        </form>
    </div>

<?php elseif ($action === 'password'): ?>

    <!-- ===========================
         PASSWORD PAGE
    ============================ -->

    <h2>Change Password</h2>

    <div class="card" style="max-width: 400px;">
        <form method="POST">

            <p>Current Password:<br>
            <input type="password" name="current_password" required></p>

            <p>New Password:<br>
            <input type="password" name="new_password" required></p>

            <p>Confirm New Password:<br>
            <input type="password" name="confirm_password" required></p>

            <button type="submit" name="change_password">Change Password</button>
        </form>
    </div>

<?php endif; ?>

</div>

<script>
function showEditForm(id, username, email) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_email').value = email;
    document.getElementById('editForm').style.display = 'block';
}

function hideEditForm() {
    document.getElementById('editForm').style.display = 'none';
}
</script>

</body>
</html>
