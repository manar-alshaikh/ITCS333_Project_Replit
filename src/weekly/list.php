<?php
include '../../config/Config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isStudent()) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Course Breakdown</title>

    <!-- FONTS -->

    <!-- CSS FRAMEWORKS -->

    <!-- CUSTOM CSS FILES -->
    <link rel="stylesheet" href="../common/css/background.css">
    <link rel="stylesheet" href="../common/css/weekly-list.css">

    <!-- JS FILES -->
    <script src="../common/js/background.js"></script>
    <script src="../weekly/list.js" defer></script>
    <script src="../common/js/goBackButton.js"></script>


</head>

<body>
    <div class="bg-animation">
        <div class="neural-network" id="neuralNetwork"></div>
        <div class="particles" id="particles"></div>
    </div>

    <header>
        <button id="go-back-btn">⮜</button>
        <h1>Weekly Course Breakdown</h1>
    </header>

    <main>
        <section id="week-list-section">
            <!-- Dummy week 1 -->
            <article>
                <h2>Week 1: Introduction to HTML</h2>
                <!-- Dummy date format: YYYY-MM-DD -->
                <p>Starts on: 2024-01-01</p>
                <p>Learn the fundamentals of HTML structure and basic web page creation.</p>
                <!-- Dummy href - will link to actual week detail page later -->
                <a href="week-detail.html">View Details & Discussion</a>
            </article>

            <!-- Dummy week 2 -->
            <article>
                <h2>Week 2: CSS Fundamentals</h2>
                <!-- Dummy date format: YYYY-MM-DD -->
                <p>Starts on: 2024-01-08</p>
                <p>Introduction to styling with CSS, selectors, and basic layout techniques.</p>
                <!-- Dummy href - will link to actual week detail page later -->
                <a href="week-detail.html">View Details & Discussion</a>
            </article>

            <!-- Dummy week 3 -->
            <article>
                <h2>Week 3: JavaScript Basics</h2>
                <!-- Dummy date format: YYYY-MM-DD -->
                <p>Starts on: 2024-01-15</p>
                <p>Learn JavaScript fundamentals, variables, functions, and DOM manipulation.</p>
                <!-- Dummy href - will link to actual week detail page later -->
                <a href="week-detail.html">View Details & Discussion</a>
            </article>
        </section>
    </main>
</body>

</html>