<?php
require_once __DIR__ . '/../../config/Config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: /login");
    exit();
}

// Safely read session values
$loggedInUserId   = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null; // Changed from 'id' to 'user_id'
$loggedInUsername = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$isAdmin          = function_exists('isAdmin') ? isAdmin() : false;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Week Details</title>

    <!-- FONTS -->

    <!-- CSS FRAMEWORKS -->

    <!-- CUSTOM CSS FILES -->
    <link rel="stylesheet" href="/src/common/css/background.css">
    <link rel="stylesheet" href="/src/common/css/weekly-details.css">

    <!-- JS GLOBALS FROM PHP -->
    <script>
    window.IS_ADMIN = <?= $isAdmin ? 'true' : 'false'; ?>;
    window.LOGGED_IN_USER_ID = <?= $loggedInUserId !== null ? $loggedInUserId : 'null' ?>;
    window.LOGGED_IN_USER_NAME = <?= json_encode($loggedInUsername) ?>;

    window.BACK_URL = <?= $isAdmin ? '"/weekly/admin"' : '"/weekly/list"' ?>;
    </script>

    <!-- JS FILES -->
    <script src="/src/common/js/background.js"></script>
    <script src="/src/weekly/details.js" defer></script>
</head>

<body>
    <div class="bg-animation">
        <div class="neural-network" id="neuralNetwork"></div>
        <div class="particles" id="particles"></div>
    </div>

    <header>
        <button id="go-back-btn">⮜</button>
        <h1 id="week-title">Week details</h1>
    </header>

    <main>
        <!-- Section 1: Weekly Information -->
        <article>
            <p id="week-start-date">Starts on: -</p>

            <h2>Description &amp; Notes</h2>
            <p id="week-description">Loading...</p>

            <h2>Exercises &amp; Resources</h2>
            <ul id="week-links-list"></ul>
        </article>

        <!-- Section 2: Discussion Forum -->
        <section id="discussion-forum">
            <h2>Discussion</h2>

            <!-- Existing Comments -->
            <div id="comment-list"></div>

            <!-- Add a Comment Form -->
            <form action="#" id="comment-form">
                <fieldset>
                    <legend>Ask a Question</legend>

                    <label for="new-comment-text">Comment:</label>
                    <textarea id="new-comment-text" required></textarea>

                    <button type="submit">Post Comment</button>
                </fieldset>
            </form>
        </section>
    </main>
</body>

</html>