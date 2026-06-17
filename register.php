<?php
/**
 * ============================================================
 * register.php — User Registration
 * ============================================================
 * CTF Platform | Educational Cyber Security Lab
 *
 * Allows new users to create an account.
 * Passwords are hashed with MD5 (intentionally weak for CTF).
 * ============================================================
 */

session_start();
require_once 'db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ---- Basic Validation ----
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // ---- Hash password with MD5 (intentionally weak for CTF) ----
        $password_hash = md5($password);

        // ---- Insert new user ----
        $query  = "INSERT INTO users (username, email, password_hash) VALUES ('{$username}', '{$email}', '{$password_hash}')";
        $result = pg_query($dbconn, $query);

        if ($result) {
            $success = 'Registration successful! You can now log in.';
        } else {
            $error = 'Registration failed. Username may already exist.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — CTF Platform</title>
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
                <a href="login.php" class="hover:text-green-300 transition">
                    <i class="fa-solid fa-right-to-bracket mr-1"></i>Login
                </a>
                <a href="register.php" class="bg-green-500/20 border border-green-500/50 px-3 py-1 rounded hover:bg-green-500/30 transition">
                    <i class="fa-solid fa-user-plus mr-1"></i>Register
                </a>
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <div class="max-w-md mx-auto mt-16 px-4">
        <div class="border border-green-900/60 rounded-lg bg-gray-900/80 glow-border p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-500/10 border border-green-500/30 mb-4">
                    <i class="fa-solid fa-user-plus text-2xl text-green-400"></i>
                </div>
                <h1 class="text-2xl font-bold text-green-400 glow-green">Create Account</h1>
                <p class="text-green-600 text-sm mt-2">Join the CTF platform and start hacking</p>
            </div>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded mb-6 text-sm">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if (!empty($success)): ?>
                <div class="bg-green-500/10 border border-green-500/40 text-green-300 px-4 py-3 rounded mb-6 text-sm">
                    <i class="fa-solid fa-check-circle mr-2"></i><?php echo $success; ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="register.php" id="registerForm" class="space-y-5">
                <!-- Username -->
                <div>
                    <label class="block text-green-500 text-xs uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-user mr-1"></i>Username
                    </label>
                    <input type="text" name="username" required minlength="3"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        class="w-full bg-gray-950 border border-green-900/60 rounded px-4 py-3 text-green-300 placeholder-green-900 focus:outline-none focus:border-green-500 input-glow transition"
                        placeholder="Choose a username">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-green-500 text-xs uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-envelope mr-1"></i>Email
                    </label>
                    <input type="email" name="email" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        class="w-full bg-gray-950 border border-green-900/60 rounded px-4 py-3 text-green-300 placeholder-green-900 focus:outline-none focus:border-green-500 input-glow transition"
                        placeholder="your@email.com">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-green-500 text-xs uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-lock mr-1"></i>Password
                    </label>
                    <input type="password" name="password" required minlength="6"
                        class="w-full bg-gray-950 border border-green-900/60 rounded px-4 py-3 text-green-300 placeholder-green-900 focus:outline-none focus:border-green-500 input-glow transition"
                        placeholder="Minimum 6 characters">
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-green-500 text-xs uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-lock mr-1"></i>Confirm Password
                    </label>
                    <input type="password" name="confirm_password" required minlength="6"
                        class="w-full bg-gray-950 border border-green-900/60 rounded px-4 py-3 text-green-300 placeholder-green-900 focus:outline-none focus:border-green-500 input-glow transition"
                        placeholder="Re-enter your password">
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-green-500/20 border border-green-500/60 text-green-400 font-bold py-3 rounded hover:bg-green-500/30 hover:shadow-lg hover:shadow-green-500/20 transition-all duration-300 uppercase tracking-wider">
                    <i class="fa-solid fa-user-plus mr-2"></i>Create Account
                </button>
            </form>

            <!-- Login Link -->
            <p class="text-center text-green-700 text-sm mt-6">
                Already have an account?
                <a href="login.php" class="text-green-400 hover:text-green-300 underline">Log in here</a>
            </p>
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
