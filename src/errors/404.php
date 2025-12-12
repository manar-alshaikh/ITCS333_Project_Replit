<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>404 - Page Not Found</title>

    <!-- FIXED PATHS -->
    <link rel="stylesheet" href="/src/common/css/background.css">
    <script src="/src/common/js/background.js" defer></script>

    <style>
        :root {
            --dark: #0a0a0f;
            --darker: #050508;
            --primary: #00ffff;
            --secondary: #ff00ff;
            --accent: #7c3aed;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: #0a0a0f;
            color: #ffffff;
            font-family: Arial, sans-serif;
        }

        .content {
            position: relative;
            z-index: 10;
            text-align: center;
            padding-top: 100px;
        }

        h1 {
            font-size: 4rem;
            font-weight: bold;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: neonTitleGlow 1.6s ease-in-out infinite alternate;
        }

        @keyframes neonTitleGlow {
            from { filter: drop-shadow(0 0 12px rgba(0, 255, 255, 0.5)); }
            to   { filter: drop-shadow(0 0 18px rgba(255, 0, 255, 0.7)); }
        }

        p {
            font-size: 1.2rem;
            color: #ccc;
        }

        a {
            margin-top: 20px;
            display: inline-block;
            padding: 12px 22px;
            background: #00ffff;
            color: #000;
            font-weight: bold;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.2s;
        }

        a:hover {
            background: #7c3aed;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="bg-animation">
        <div class="neural-network" id="neuralNetwork"></div>
        <div class="particles" id="particles"></div>
    </div>

    <div class="content">
        <h1>404</h1>
        <p>Oops! The page you are looking for does not exist.</p>
        <a href="/login">Go Back Home</a>
    </div>

</body>

</html>
