<?php
require_once __DIR__ . "/../../config/Config.php";

if (!isLoggedIn()) {
    header("Location: ../auth/login.php");
    exit();
}

$loggedInUserId   = $_SESSION['user_id'] ?? null;
$loggedInUsername = $_SESSION['username'] ?? null;
$isAdmin          = function_exists('isAdmin') ? isAdmin() : false;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Details</title>

    <!-- CSS FILES -->
    <link rel="stylesheet" href="/src/common/css/background.css">
    <link rel="stylesheet" href="/src/common/css/weekly-details.css">

    <!-- SESSION VARIABLES PASSED TO JS -->
    <script>
        window.IS_ADMIN = <?= $isAdmin ? 'true' : 'false'; ?>;
        window.LOGGED_IN_USER_ID = <?= $loggedInUserId ?? 'null'; ?>;
        window.LOGGED_IN_USER_NAME = <?= json_encode($loggedInUsername); ?>;
    </script>

    <!-- JS FILES -->
    <script src="/src/common/js/background.js"></script>
    <script src="/src/common/js/goBackButton.js" defer></script>
    <script src="/src/resources/details.js" defer></script>
</head>

<body>

    <!-- BACKGROUND ANIMATION -->
    <div class="bg-animation">
        <div class="neural-network" id="neuralNetwork"></div>
        <div class="particles" id="particles"></div>
    </div>

    <!-- PAGE HEADER -->
    <header>
        <button id="go-back-btn">⮜</button>
        <h1 id="resource-title">Loading...</h1>
    </header>

    <!-- MAIN CONTENT -->
    <main>

        <!-- Resource Information -->
        <article>
            <p id="resource-description"></p>

            <a id="resource-link" href="#" target="_blank">
                Access Resource Material
            </a>
        </article>

        <!-- Discussion Section -->
        <section id="discussion-forum">
            <h2>Discussion</h2>

            <!-- Existing comments -->
            <div id="comment-list"></div>

            <!-- Add a new comment -->
            <form id="comment-form">
                <fieldset>
                    <legend>Leave a Comment</legend>

                    <label for="new-comment-text">Comment:</label>
                    <textarea id="new-comment-text" required></textarea>

                    <button type="submit">Post Comment</button>
                </fieldset>
            </form>
        </section>

    </main>

</body>

</html>

