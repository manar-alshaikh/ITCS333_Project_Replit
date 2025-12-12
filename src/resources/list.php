<!--
  Student View - Course Resources (Read Only)
-->
<?php
require_once __DIR__ . "/../../config/Config.php";

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
     <title>Course Resources</title>

     <!-- CSS FRAMEWORK (Optional) -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pico-css@1.5.10/dist/pico.min.css">

     <!-- CUSTOM CSS (Optional) -->
     <style>
        main {
            width: 90%;
            max-width: 900px;
            margin: auto;
            padding-top: 20px;
        }

        article {
            margin-bottom: 20px;
        }
     </style>

     <!-- JS -->
     <!-- <script src="../resources/list.js" defer></script> -->
     <link rel="stylesheet" href="../common/css/background.css">
<link rel="stylesheet" href="../common/css/weekly-list.css">

<script src="../common/js/background.js"></script>
<script src="../common/js/goBackButton.js" defer></script>
<script src="list.js" defer></script>

</head>

<body>
<div class="bg-animation">
    <div class="neural-network" id="neuralNetwork"></div>
    <div class="particles" id="particles"></div>
</div>

 <header>
        <button id="go-back-btn">⮜</button>
     <h1>Course Resources</h1>
 </header>

 <main>
    
     <!-- REQUIRED SECTION ID -->
     <section id="resource-list-section">
         <!-- JavaScript (list.js) will insert resources here -->
     </section>
 </main>

</body>
</html>

