<?php
require_once __DIR__ . '/../../config/Config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: AdminPortal.php");
        exit();
    } else {
        header("Location: dashboard.php");
        exit();
    }
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Cookies
            setcookie('user_id', $user['id'], time() + (86400 * 30), "/");
            setcookie('username', $user['username'], time() + (86400 * 30), "/");
            setcookie('role', $user['role'], time() + (86400 * 30), "/");

            // REDIRECT based on role
            if ($user['role'] === 'admin') {
                header("Location: AdminPortal.php");
                exit();
            } else {
                header("Location: dashboard.php");
                exit();
            }
        } 
        else {
            $error = "Invalid username or password!";
        }

    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Dev Course Homepage</title>
    <!-- CSS Files -->
    <link rel="stylesheet" href="/src/common/css/main.css">
    <link rel="stylesheet" href="/src/common/css/background.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>

<body>
    <!-- Background Animation -->
    <div class="bg-animation">
        <div class="neural-network" id="neuralNetwork"></div>
        <div class="particles" id="particles"></div>
    </div>

    <!-- Header -->
    <header>
        <nav>
            <a href="#home" class="logo">
                <svg class="logo-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <!-- Hexagon background -->
                    <polygon points="50,5 85,25 85,65 50,85 15,65 15,25" fill="none" stroke="url(#gradientStroke)"
                        stroke-width="2" />

                    <!-- Neural network nodes -->
                    <circle cx="50" cy="30" r="4" fill="#00ffff" />
                    <circle cx="35" cy="45" r="4" fill="#ff00ff" />
                    <circle cx="65" cy="45" r="4" fill="#ff00ff" />
                    <circle cx="35" cy="65" r="4" fill="#00ffff" />
                    <circle cx="65" cy="65" r="4" fill="#00ffff" />
                    <circle cx="50" cy="55" r="5" fill="#7c3aed" />

                    <!-- Connection lines -->
                    <line x1="50" y1="30" x2="35" y2="45" stroke="#00ffff" stroke-width="1" opacity="0.5" />
                    <line x1="50" y1="30" x2="65" y2="45" stroke="#00ffff" stroke-width="1" opacity="0.5" />
                    <line x1="35" y1="45" x2="50" y2="55" stroke="#ff00ff" stroke-width="1" opacity="0.5" />
                    <line x1="65" y1="45" x2="50" y2="55" stroke="#ff00ff" stroke-width="1" opacity="0.5" />
                    <line x1="50" y1="55" x2="35" y2="65" stroke="#7c3aed" stroke-width="1" opacity="0.5" />
                    <line x1="50" y1="55" x2="65" y2="65" stroke="#7c3aed" stroke-width="1" opacity="0.5" />

                    <!-- Gradient definitions -->
                    <defs>
                        <linearGradient id="gradientStroke" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#00ffff;stop-opacity:1" />
                            <stop offset="50%" style="stop-color:#ff00ff;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#7c3aed;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                </svg>
                <span class="logo-text"> ITCS333 - INTERNET SOFTWARE DEVELOPMENT</span>
            </a>
            <ul class="nav-links">
                <li><a href="#features">FEATURES</a></li>
                <li><a href="#login">LOGIN</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">LOGOUT (<?php echo $_SESSION['username']; ?>)</a></li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li><a href="AdminPortal.php">ADMIN</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <div class="mobile-menu" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
        <!-- Mobile Navigation Menu -->
        <div class="mobile-nav" id="mobileNav">
            <a href="#features" onclick="closeMenu()">Features</a>
            <a href="#login" onclick="closeMenu()">Login</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" onclick="closeMenu()">Logout (<?php echo $_SESSION['username']; ?>)</a>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="AdminPortal.php" onclick="closeMenu()">Admin</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1> Welcome to the Web Development Course</h1>
            <p>Go from beginner to builder. Learn the skills to create modern, responsive websites.</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div style="margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; display: inline-block;">
                    <h3 style="margin: 0; color: #00ffff;">Hello, <?php echo $_SESSION['username']; ?>!</h3>
                    <p style="margin: 5px 0 0 0; color: #ff00ff;">Role: <?php echo $_SESSION['role']; ?></p>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <a href="AdminPortal.php" style="color: #7c3aed; text-decoration: none; font-weight: bold;">→ Admin Panel</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section">
        <h2 class="animate-on-scroll"> Website Features </h2>
        <div class="features-grid">
            <div class="feature-card animate-on-scroll scale-up stagger-animation" style="--stagger: 1;">
                <div class="feature-icon"><i class="fas fa-book"></i></div>
                <h3 class="feature-name">Course Resources</h3>
                <div class="feature-description">Lecture notes, book chapters, slides, helpful links</div>
                <p class="feature-details">All your study goodies in one place! Peek, read, and leave comments to share
                    thoughts with your classmates</p>
            </div>
            <div class="feature-card animate-on-scroll scale-up stagger-animation" style="--stagger: 2;">
                <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
                <h3 class="feature-name">Weekly Breakdown</h3>
                <div class="feature-description">Week-by-week topics, notes, exercises, links</div>
                <p class="feature-details">Your roadmap for the semester! Explore each week and never miss a beat</p>
            </div>
            <div class="feature-card animate-on-scroll scale-up stagger-animation" style="--stagger: 3;">
                <div class="feature-icon"><i class="fas fa-pencil-alt"></i></div>
                <h3 class="feature-name">Assignments</h3>
                <div class="feature-description">Assignment titles, instructions, due dates, downloadable files</div>
                <p class="feature-details">Stay on top of your tasks! Check details, submit work, and ask questions if
                    you get stuck</p>
            </div>
            <div class="feature-card animate-on-scroll scale-up stagger-animation" style="--stagger: 4;">
                <div class="feature-icon"><i class="fas fa-comments"></i></div>
                <h3 class="feature-name">Discussion Boards</h3>
                <div class="feature-description">Topics, replies, comments, teacher guidance</div>
                <p class="feature-details">Chat, brainstorm, and ask questions! Jump into the conversation and make your
                    voice heard</p>
            </div>
        </div>
    </section>

    <!-- Login Section -->
<section id="login" class="login-section">
    <div id="login-form-section" class="login-form-section animate-on-scroll">
        <div class="login-container">
            <div class="login-form-column">
                <h3 class="login-form-title">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        Welcome back, <?php echo $_SESSION['username']; ?>!
                    <?php else: ?>
                        Welcome back!
                    <?php endif; ?>
                </h3>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <form class="login-form" method="POST" novalidate>
                        <div class="form-field">
                            <label for="username">Username</label>
                            <div class="input-wrapper">
                                <input type="text" id="username" name="username" required placeholder="Enter your username" autocomplete="username"
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            </div>
                            <span class="error-message" id="usernameError"></span>
                        </div>

                        <div class="form-field">
                            <label for="password">Password</label>
                            <div class="input-wrapper password-wrapper">
                                <input type="password" id="password" name="password" required placeholder="Enter your password" autocomplete="current-password">
                                <button type="button" class="password-toggle" id="passwordToggle"
                                    aria-label="Toggle password visibility">
                                    <span class="toggle-text">SHOW</span>
                                </button>
                            </div>
                            <span class="error-message" id="passwordError"></span>
                        </div>

                        <button type="submit" name="login" class="login-submit-btn">
                            <span class="btn-text">Login</span>
                            <div class="btn-loader">
                                <div class="loader-bar"></div>
                                <div class="loader-bar"></div>
                                <div class="loader-bar"></div>
                            </div>
                        </button>
                    </form>
                <?php else: ?>
                    <div style="text-align: center; padding: 20px;">
                        <div style="font-size: 48px; color: #00ffff; margin-bottom: 15px;">✓</div>
                        <h3 style="color: #00ffff; margin-bottom: 10px;">You are logged in!</h3>
                        <p style="color: #ff00ff; margin-bottom: 20px;">Role: <?php echo $_SESSION['role']; ?></p>
                        <div style="display: flex; gap: 10px; justify-content: center;">
                            <a href="dashboard.php" style="background: #7c3aed; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Go to Dashboard</a>
                            <a href="logout.php" style="background: transparent; color: #ff00ff; padding: 10px 20px; border-radius: 5px; text-decoration: none; border: 1px solid #ff00ff;">Logout</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

    <!-- JavaScript Files -->
    <script src="/src/common/js/background.js"></script>
    <script src="/src/common/js/main.js"></script>
    
</body>

</html>