<?php

/**
 * ============================================================
 * feedback.php — Feedback / Guestbook Page
 * ============================================================
 * CTF Platform | Educational Cyber Security Lab
 *
 * Allows logged-in users to post feedback/comments.
 *
 * ⚠ VULNERABILITY: Stored Cross-Site Scripting (XSS)
 * ------------------------------------------------------------
 * 1. When a user submits a comment, it is stored in the
 *    database EXACTLY as entered — no sanitization.
 * 2. When displaying comments, the raw text is output
 *    directly into the HTML using <?php echo $row['comment']; ?>
 *    WITHOUT htmlspecialchars() or any output encoding.
 *
 * This means any HTML or JavaScript entered in the comment
 * field will be executed in the browser of every user who
 * visits this page.
 *
 * EXPLOIT EXAMPLES (try these in the comment field):
 *   <script>alert('XSS')</script>
 *   <script>alert(document.cookie)</script>
 *   <img src=x onerror=alert(1)>
 *   <svg onload=alert('XSS')>
 *   <iframe src="javascript:alert('XSS')">
 *
 * LEARNING OBJECTIVE: Understand why output encoding
 * (htmlspecialchars in PHP) is critical for preventing XSS.
 * ============================================================
 */

session_start();
require_once 'db.php';

$is_logged_in = isset($_SESSION['user_id']);
$error        = '';
$success      = '';

// ---- Handle Comment Submission ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_logged_in) {
    $comment = $_POST['comment'] ?? '';

    if (empty(trim($comment))) {
        $error = 'Comment cannot be empty.';
    } else {
        /**
         * ⚠ VULNERABILITY: No input sanitization
         * The comment is stored exactly as entered.
         * In a safe application, you would use:
         *   $safe_comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
         * But here we store the RAW input for educational purposes.
         */
        $username = $_SESSION['username'];
        $user_id  = $_SESSION['user_id'];

        // Note: We DO use pg_query_params here for the INSERT to prevent
        // SQL injection on this page — the XSS is the intended vulnerability,
        // not SQLi on the feedback form itself.
        // However, the comment content is stored RAW (unsanitized).
        $query = "INSERT INTO feedback (user_id, username, comment) VALUES ($1, $2, $3)";

        $result = pg_query_params(
            $dbconn,
            $query,
            [
                $user_id,
                $username,
                $comment
            ]
        );

        if ($result) {

            $_SESSION['success'] = 'Your feedback has been posted!';

            header("Location: feedback.php");

            exit;
        }
        if (!$result) {

            echo "<pre>";
            echo "SQL Query:\n";
            echo $query . "\n\n";

            echo "PostgreSQL Error:\n";
            echo pg_last_error($dbconn);
            echo "</pre>";

            exit;
        }
    }
}

// ---- Fetch All Feedback ----
$query   = "SELECT * FROM feedback ORDER BY created_at DESC";
$result  = pg_query($dbconn, $query);
$feedback_list = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $feedback_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback — CTF Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Fira Code', monospace;
        }

        .glow-green {
            text-shadow: 0 0 10px #00ff41, 0 0 20px #00ff41;
        }

        .glow-border {
            box-shadow: 0 0 10px rgba(0, 255, 65, 0.3), inset 0 0 10px rgba(0, 255, 65, 0.1);
        }

        .input-glow:focus {
            box-shadow: 0 0 15px rgba(0, 255, 65, 0.5);
        }

        .scan-line {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: rgba(0, 255, 65, 0.1);
            animation: scan 4s linear infinite;
            pointer-events: none;
            z-index: 9999;
        }

        @keyframes scan {
            0% {
                top: 0;
            }

            100% {
                top: 100%;
            }
        }

        .matrix-bg {
            background-image: radial-gradient(rgba(0, 255, 65, 0.03) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .code-block {
            background: rgba(0, 0, 0, 0.4);
            border-left: 3px solid #00ff41;
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
                <a href="feedback.php" class="text-green-300">
                    <i class="fa-solid fa-comments mr-1"></i>Feedback
                </a>
                <?php if ($is_logged_in): ?>
                    <span class="text-green-600">
                        <i class="fa-solid fa-user mr-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span>
                    <a href="logout.php" class="bg-red-500/10 border border-red-500/40 text-red-400 px-3 py-1 rounded hover:bg-red-500/20 transition">
                        <i class="fa-solid fa-right-from-bracket mr-1"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="hover:text-green-300 transition">
                        <i class="fa-solid fa-right-to-bracket mr-1"></i>Login
                    </a>
                    <a href="register.php" class="bg-green-500/20 border border-green-500/50 px-3 py-1 rounded hover:bg-green-500/30 transition">
                        <i class="fa-solid fa-user-plus mr-1"></i>Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-10">

        <!-- Page Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-500/10 border border-green-500/30 mb-4">
                <i class="fa-solid fa-comments text-2xl text-green-400"></i>
            </div>
            <h1 class="text-3xl font-bold text-green-400 glow-green mb-2">Community Feedback</h1>
            <p class="text-green-600 text-sm">Share your thoughts, report bugs, or leave hints for other players.</p>
        </div>

        <!-- Comment Form (Logged-in users only) -->
        <?php if ($is_logged_in): ?>
            <div class="border border-green-900/50 rounded-lg bg-gray-900/60 glow-border p-6 mb-8">
                <h2 class="text-sm uppercase tracking-wider text-green-500 mb-4">
                    <i class="fa-solid fa-pen mr-1"></i>Post a Comment
                </h2>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded mb-4 text-sm">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="bg-green-500/10 border border-green-500/40 text-green-300 px-4 py-3 rounded mb-4 text-sm">
                        <i class="fa-solid fa-check-circle mr-2"></i><?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="feedback.php"
                    onsubmit="this.querySelector('button[type=submit]').disabled=true">
                    <textarea name="comment" required rows="4" maxlength="1000"
                        class="w-full bg-gray-950 border border-green-900/60 rounded px-4 py-3 text-green-300 placeholder-green-900 focus:outline-none focus:border-green-500 input-glow transition resize-none"
                        placeholder="Write your feedback here..."></textarea>
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-green-900 text-xs">
                            <i class="fa-solid fa-info-circle mr-1"></i>Max 1000 characters
                        </span>
                        <button type="submit"
                            class="bg-green-500/20 border border-green-500/60 text-green-400 font-bold px-6 py-2 rounded hover:bg-green-500/30 hover:shadow-lg hover:shadow-green-500/20 transition-all duration-300 uppercase tracking-wider text-sm">
                            <i class="fa-solid fa-paper-plane mr-2"></i>Post Comment
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="border border-green-900/30 rounded-lg bg-gray-900/30 p-6 mb-8 text-center">
                <i class="fa-solid fa-lock text-green-700 text-2xl mb-3"></i>
                <p class="text-green-600 text-sm">
                    You must be logged in to post feedback.
                    <a href="login.php" class="text-green-400 underline hover:text-green-300">Login</a> or
                    <a href="register.php" class="text-green-400 underline hover:text-green-300">Register</a>
                </p>
            </div>
        <?php endif; ?>

        <!-- Feedback List -->
        <div class="space-y-4">
            <h2 class="text-sm uppercase tracking-wider text-green-600 mb-4">
                <i class="fa-solid fa-list mr-1"></i>All Comments (<?php echo count($feedback_list); ?>)
            </h2>

            <?php if (empty($feedback_list)): ?>
                <div class="text-center text-green-800 py-12">
                    <i class="fa-solid fa-inbox text-4xl mb-3"></i>
                    <p>No feedback yet. Be the first to comment!</p>
                </div>
            <?php else: ?>
                <?php foreach ($feedback_list as $fb): ?>
                    <div class="border border-green-900/40 rounded-lg bg-gray-900/40 p-5 hover:border-green-900/60 transition">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-green-500/10 border border-green-500/30 flex items-center justify-center">
                                    <i class="fa-solid fa-user text-green-500 text-xs"></i>
                                </div>
                                <span class="text-green-300 font-bold text-sm">
                                    <?php echo htmlspecialchars($fb['username']); ?>
                                </span>
                            </div>
                            <span class="text-green-900 text-xs">
                                <i class="fa-regular fa-clock mr-1"></i>
                                <?php echo $fb['created_at']; ?>
                            </span>
                        </div>

                        <?php
                        /**
                         * ⚠ VULNERABILITY: Stored XSS
                         * The comment is output directly into the HTML WITHOUT
                         * htmlspecialchars() or any output encoding.
                         *
                         * This means if someone posts:
                         *   <script>alert('XSS')</script>
                         * It will be rendered as executable HTML/JavaScript.
                         *
                         * SAFE ALTERNATIVE (what you SHOULD do):
                         *   <?php echo htmlspecialchars($fb['comment'], ENT_QUOTES, 'UTF-8'); ?>
                         */
                        ?>
                        <div class="text-green-400 text-sm leading-relaxed comment-body">
                            <?php echo $fb['comment']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Lab Notes (Educational) -->
        <div class="mt-10 code-block rounded-lg p-5">
            <h3 class="text-green-600 text-xs uppercase tracking-wider mb-3">
                <i class="fa-solid fa-flask mr-1"></i>Lab Notes — Stored XSS
            </h3>
            <p class="text-green-700 text-xs leading-relaxed mb-2">
                This feedback form accepts user input and displays it to all visitors.
                The comment is stored in the database and rendered in the browser
                <strong class="text-red-400">without any sanitization or output encoding</strong>.
            </p>
            <p class="text-green-700 text-xs leading-relaxed mb-2">
                Try posting a comment with HTML or JavaScript code:
            </p>
            <pre class="text-green-500 text-xs bg-black/30 p-3 rounded overflow-x-auto mb-2">&lt;script&gt;alert('XSS')&lt;/script&gt;
&lt;img src=x onerror=alert(document.cookie)&gt;
&lt;svg onload=alert('Stored XSS!')&gt;</pre>
            <p class="text-green-700 text-xs leading-relaxed">
                If the script executes when the page reloads, the vulnerability is confirmed.
                In production, always use <code class="text-green-500">htmlspecialchars()</code> when outputting user data.
            </p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center text-green-900 text-xs py-8 mt-12 border-t border-green-900/20">
        <p>CTF Platform v1.0 — Educational Cyber Security Lab</p>
        <p class="mt-1">⚠ For educational purposes only. Do not deploy in production.</p>
    </footer>

    <script src="js/script.js"></script>
</body>

</html>