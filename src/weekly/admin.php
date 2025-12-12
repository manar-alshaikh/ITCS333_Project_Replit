<?php
require_once __DIR__ . "/../../config/Config.php";

if (!isLoggedIn() || !isAdmin()) {
    header("Location: /login");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Weekly Breakdown</title>

    <!-- FONTS -->

    <!-- CSS FRAMEWORKS -->

    <!-- CUSTOM CSS FILES -->
    <link rel="stylesheet" href="/src/common/css/background.css">
    <link rel="stylesheet" href="/src/common/css/weekly-admin.css">

    <!-- JS FILES -->
    <script src="/src/common/js/background.js"></script>
    <script src="/src/weekly/admin.js" defer></script>
    <script>
    window.BACK_URL = <?= isAdmin() ? '"/weekly/admin"' : '"/weekly/list"' ?>;
    </script>

</head>

<body id="main-content">

    <div class="bg-animation">
        <div class="neural-network" id="neuralNetwork"></div>
        <div class="particles" id="particles"></div>
    </div>

    <header>
        <button id="go-back-btn">⮜</button>
        <h1>Manage Weekly Breakdown</h1>
    </header>

    <main>
        <!-- Section 1: Add/Edit Week Form -->
        <section>
            <h2>Add a New Week</h2>
            <form id="week-form" action="#">
                <fieldset>
                    <legend>Weekly Details</legend>

                    <label for="week-title">Week Title:</label>
                    <input id="week-title" type="text" required placeholder="Week 1: Introduction to HTML">

                    <label for="week-start-date">Start Date:</label>
                    <input id="week-start-date" type="date" required>

                    <label for="week-description">Description:</label>
                    <textarea id="week-description" rows="5"></textarea>

                    <label for="week-links">Links:</label>
                    <textarea id="week-links" rows="3"></textarea>

                    <div id="form-global-error"></div>
                    <button id="add-week" type="submit">Add Week</button>
                </fieldset>
            </form>
        </section>


        <!-- Section 2: Existing Weekly Entries -->
        <section>
            <h2>Current Weekly Breakdown</h2>
            <table id="weeks-table">
                <thead>
                    <tr>
                        <th>Week Title</th>
                        <th>Description</th>

                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="weeks-tbody">
                    <!-- Dummy data row for demonstration -->
                    <tr>
                        <td>Week 1: Intro to HTML</td>
                        <td>Learn the basics of HTML structure and elements</td>
                        <td>
                            <button>Edit</button>
                            <button>Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Modal -->
    <div class="modal-overlay" id="edit-modal-overlay">
        <div class="modal">
            <h2>Edit Week</h2>
            <form id="edit-week-form"> <label for="edit-week-title">Week Title:</label> <input id="edit-week-title"
                    type="text" required> <label for="edit-week-start-date">Start Date:</label> <input
                    id="edit-week-start-date" type="date" required> <label
                    for="edit-week-description">Description:</label> <textarea id="edit-week-description"
                    rows="4"></textarea> <label for="edit-week-links">Links:</label> <textarea id="edit-week-links"
                    rows="3"></textarea>
                <div style="margin-top: 1rem;"> <button type="submit">Save</button> <button type="button"
                        id="cancel-edit">Cancel</button> </div>
            </form>
        </div>
    </div>
</body>

</html>