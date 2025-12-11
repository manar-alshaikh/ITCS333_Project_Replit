<!--
  Admin View - Course Resources
  This page allows the teacher/admin to manage all course resources (CRUD).
-->
<?php
require_once __DIR__ . "/../../config/Config.php";

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Manage Resources</title>

    <!-- CSS Framework (optional) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pico-css@1.5.10/dist/pico.min.css">

    <!-- Custom CSS (optional) -->
    <style>
        main {
            width: 90%;
            max-width: 900px;
            margin: auto;
            padding-top: 20px;
        }
        table {
            width: 100%;
        }
        #add-resource {
            margin-top: 15px;
        }
    </style>

    <!-- Link JS -->
    <script src="admin.js" defer></script>
    <link rel="stylesheet" href="../common/css/background.css">
<link rel="stylesheet" href="../common/css/weekly-admin.css">
<script src="../common/js/background.js"></script>
<script src="../common/js/goBackButton.js" defer></script>
<script src="admin.js" defer></script>

</head>

<body>
<div class="bg-animation">
    <div class="neural-network" id="neuralNetwork"></div>
    <div class="particles" id="particles"></div>
</div>


    <header>
        <button id="go-back-btn">⮜</button>
        <h1>Manage Course Resources</h1>
    </header>

    <main>

        <!-- Section 1: Add New Resource Form -->
        <section>
            <h2>Add a New Resource</h2>

            <form id="resource-form">
                <fieldset>
                    <legend>Resource Details</legend>

                    <label for="resource-title">Title:</label>
                    <input id="resource-title" type="text" required>

                    <label for="resource-description">Description:</label>
                    <textarea id="resource-description"></textarea>

                    <label for="resource-link">Link:</label>
                    <input id="resource-link" type="url" required>

                    <button id="add-resource" type="submit">Add Resource</button>
                </fieldset>
            </form>
        </section>

        <!-- Section 2: Existing Resources List -->
        <section>
            <h2>Existing Resources</h2>

            <table id="resources-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <!-- REQUIRED: tbody must have id="resources-tbody" -->
                <tbody id="resources-tbody">
                    <!-- JavaScript will populate the rows here -->
                </tbody>
            </table>
        </section>

    </main>

</body>
</html>
