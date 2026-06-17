<?php
/**
 * ============================================================
 * login.php — User Login
 * ============================================================
 * CTF Platform | Educational Cyber Security Lab
 *
 * Authenticates users against the PostgreSQL database.
 * Uses MD5 password hashing (intentionally weak for CTF).
 *
 * LAB NOTE: This login form is intentionally vulnerable to
 * SQL injection. Students should try:
 *   Username: ' OR '1'='1' --
 *   Password: anything
 * ============================================================
 */

session_start();
require_once 'db.php';

$error   = '';
$success = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // ---- Hash password with MD5 (intentionally weak) ----
        $password_hash = md5($password);

        /**
         * ⚠ VULNERABILITY: SQL Injection
         * The username is directly concatenated into the SQL query
         * without sanitization or prepared statements.
         *
         * EXPLOIT EXAMPLE:
         *   Username: ' OR '1'='1' --
         *   Password: anything
         *
         * This makes the query:
         *   SELECT * FROM users WHERE username = '' OR '1'='1' --' AND password_hash = '...'
         *   The -- comments out the password check, and '1'='1' is always true.
         */
        $query  = "SELECT * FROM users WHERE username = '{$username}' AND password_hash = '{$password_hash}'";
        $result = pg_query($dbconn, $query);

        if ($result && pg_num_rows($result) > 0) {
            $user = pg_fetch_assoc($result);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['email']     = $user['email'];

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — CTF Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&display=swap');
        body { font-family: 'Fira Code', monospace; }
        .glow-green { text-shadow: 0 0 10px #00ff41, 0 0 20px #00ff41; }
        .glow-border { box-shadow: 0 0 10px rgba(0, 255, 65, 0.3), inset 0 0 10px rgba(0, 255, 65, 0.1); }
        .input-glow:focus { box-shadow: 0 0 15px rgba(0, 255, 65, 0.5); }
        .scan-line {
            position: fixed; top: 0; left: 0; width: 100%; height: 2px;
            background: rgba(0, 255, 65, 0.1); animation: scan 4s linear infinite;
            pointer-events: none; z-index: 9999;
        }
        @keyframes scan { 0% { top: 0; } 100% { top: 100%; } }
        .matrix-bg {
            background-image: radial-gradient(rgba(0, 255, 65, 0.03) 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .glitch {
            animation: glitch 2s infinite;
        }
        @keyframes glitch {
            0%, 90%, 100% { transform: translate(0); }
            92% { transform: translate(-2px, 1px); }
            94% { transform: translate(2px, -1px); }
            96% { transform: translate(-1px, -1px); }
            98% { transform: translate(1px, 1px); }
        }
    </style>
</head>
<body class="bg-gray-950 text-green-400 min-h-screen matrix-bg">
    <div class="scan-line"></div>

    <!-- Navigation -->
    <nav class="border-b border-green-900/50 bg-gray-950/90 backdrop-blur-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2 text-green-400 hover:text-green-300 transition">
                <i class="fa-solid fa-terminal text-xl"></i>
                <span class="text-lg font-bold glow-green">CTF_PLATFORM</span>
            </a>
            <div class="flex items-center gap-4 text-sm">
                <a href="index.php" class="hover:text-green-300 transition">
                    <i class="fa-solid fa-house mr-1"></i>Home
                </a>
                <a href="login.php" class="bg-green-500/20 border border-green-500/50 px-3 py-1 rounded hover:bg-green-500/30 transition">
                    <i class="fa-solid fa-right-to-bracket mr-1"></i>Login
                </a>
                <a href="register.php" class="hover:text-green-300 transition">
                    <i class="fa-solid fa-user-plus mr-1"></i>Register
                </a>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="max-w-md mx-auto mt-16 px-4">
        <div class="border border-green-900/60 rounded-lg bg-gray-900/80 glow-border p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-500/10 border border-green-500/30 mb-4">
                    <i class="fa-solid fa-key text-2xl text-green-400 glitch"></i>
                </div>
                <h1 class="text-2xl font-bold text-green-400 glow-green">System Access</h1>
                <p class="text-green-600 text-sm mt-2">Authenticate to access CTF challenges</p>
            </div>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded mb-6 text-sm">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="login.php" id="loginForm" class="space-y-5">
                <!-- Username -->
                <div>
                    <label class="block text-green-500 text-xs uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-user mr-1"></i>Username
                    </label>
                    <input type="text" name="username" required
                        class="w-full bg-gray-950 border border-green-900/60 rounded px-4 py-3 text-green-300 placeholder-green-900 focus:outline-none focus:border-green-500 input-glow transition"
                        placeholder="Enter your username">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-green-500 text-xs uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-lock mr-1"></i>Password
                    </label>
                    <input type="password" name="password" required
                        class="w-full bg-gray-950 border border-green-900/60 rounded px-4 py-3 text-green-300 placeholder-green-900 focus:outline-none focus:border-green-500 input-glow transition"
                        placeholder="Enter your password">
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-green-500/20 border border-green-500/60 text-green-400 font-bold py-3 rounded hover:bg-green-500/30 hover:shadow-lg hover:shadow-green-500/20 transition-all duration-300 uppercase tracking-wider">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i>Login
                </button>
            </form>

            <!-- Register Link -->
            <p class="text-center text-green-700 text-sm mt-6">
                Don't have an account?
                <a href="register.php" class="text-green-400 hover:text-green-300 underline">Register here</a>
            </p>

            <!-- Hint (subtle, for students who are stuck) -->
            <div class="mt-6 border-t border-green-900/30 pt-4">
                <p class="text-green-900 text-xs text-center italic">
                    <i class="fa-solid fa-lightbulb mr-1"></i>
                    Hint: Sometimes the simplest approach works. What if the query could be... modified?
                </p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center text-green-900 text-xs py-8 mt-12">
        <p>CTF Platform v1.0 — Educational Cyber Security Lab</p>
        <p class="mt-1">⚠ For educational purposes only. Do not deploy in production.</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
