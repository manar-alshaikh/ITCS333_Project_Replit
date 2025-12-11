<?php
session_start();
include '../../config/Config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
        <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Course System</title>
     <link rel="stylesheet" href="../common/css/background.css">
        <link rel="stylesheet" href="../common/css/dashboard.css">
         <script src="../common/js/background.js"></script>
</head>
<body>
    <div class="bg-animation">
    <div class="neural-network" id="neuralNetwork"></div>
    <div class="particles" id="particles"></div>
</div>

    <div class="header">
        <h1>Course Management System</h1>
    </div>

<div class="nav">
    <div class="nav-left">
        <a href="dashboard.php">Dashboard</a>

        <?php if (isAdmin()): ?>
            <a href="AdminPortal.php">Admin Portal</a>
        <?php endif; ?>

        <?php if (isInstructor()): ?>
            <a href="InstructorPortal.php">Instructor Portal</a>
        <?php endif; ?>
    </div>

    <div class="nav-right">
        <a href="logout.php">Logout</a>
    </div>
</div>



    <div class="welcome-box">
        <h2>Welcome back, <?php echo $_SESSION['username']; ?>!</h2>
        <p>Last login: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>

  <div class="dashboard-grid">
    <!-- Student Cards - Only visible to students -->
    <?php if (isStudent()): ?>
    <div class="card">
        <h3>Resources</h3>
        <p>Access learning materials, documents, and study resources.</p>
        <a href="../resources/list.php"><button>View Resources</button></a>
    </div>

    <div class="card">
        <h3>Weekly Breakdown</h3>
        <p>Check out this week's learning schedule and topics.</p>
        <a href="../weekly/list.php"><button>Go to Weekly</button></a>
    </div>

    <div class="card">
        <h3>Assignments</h3>
        <p>View and submit your assignments and projects.</p>
        <a href="../assignments/list.php"><button>View Assignments</button></a>
    </div>

    <div class="card">
        <h3>General Discussion Boards</h3>
        <p>Participate in course discussions and ask questions.</p>
        <p>extra task (Not implemented)</p>
        <a href="#"><button>Join Discussion</button></a>
    </div>
    <?php endif; ?>

</div>

</body>
</html>