<?php
/**
 * ============================================================
 * change_email.php — Change Account Email
 * ============================================================
 * CTF Platform | Educational Cyber Security Lab
 *
 * ⚠ VULNERABILITY: CSRF (Cross-Site Request Forgery)
 * ------------------------------------------------------------
 * This form changes the logged-in user's email address WITHOUT
 * any CSRF token check. Because the request relies solely on
 * the session cookie for authentication, an attacker can host
 * a page (on any other domain) with an auto-submitting form
 * that POSTs to this endpoint. If a logged-in victim visits
 * that page, their browser will send the request WITH their
 * session cookie attached — silently changing their email
 * without their knowledge or consent.
 *
 * EXPLOIT EXAMPLE (see csrf_email_attack.html):
 *   <form action="change_email.php" method="POST">
 *       <input type="hidden" name="email" value="attacker@evil.com">
 *   </form>
 *   <script>document.forms[0].submit()</script>
 *
 * LEARNING OBJECTIVE: Understand why state-changing requests
 * need CSRF tokens (or SameSite cookies / re-authentication)
 * in addition to session cookies.
 *
 * SAFE ALTERNATIVE (what you SHOULD do):
 *   1. Generate a random token, store it in $_SESSION.
 *   2. Embed it as a hidden field in the form.
 *   3. On submit, verify hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
 *      before processing the request.
 * ============================================================
 */

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    // For lab debugging
    die('Not logged in during CSRF request. Session missing.');
}

$is_logged_in = true;
$message      = '';
$flag_earned  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = trim($_POST['email'] ?? '');

    // ⚠ NOTE: No CSRF token is checked here at all — that's the vulnerability.

    if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
    } else {
        $user_id = $_SESSION['user_id'];

        $query  = "UPDATE users SET email = $1 WHERE id = $2";
        $result = pg_query_params($dbconn, $query, [$new_email, $user_id]);

        if ($result) {
            $_SESSION['email'] = $new_email;
            $message = 'Email updated successfully to: ' . htmlspecialchars($new_email);

            // Lab check: if the email matches the attacker's target address,
            // the CSRF exploit succeeded — award the flag.
            if (strtolower($new_email) === 'attacker@evil.com') {
                $flag_earned = true;
                $_SESSION['csrf_flag_earned'] = true;
            }
        } else {
            $message = 'Failed to update email.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings — CTF Platform</title>
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
                <a href="feedback.php" class="hover:text-green-300 transition">
                    <i class="fa-solid fa-comments mr-1"></i>Feedback
                </a>
                <span class="text-green-600">
                    <i class="fa-solid fa-user mr-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <a href="change_email.php" class="text-green-300">
                    <i class="fa-solid fa-gear mr-1"></i>Settings
                </a>
                <a href="logout.php" class="bg-red-500/10 border border-red-500/40 text-red-400 px-3 py-1 rounded hover:bg-red-500/20 transition">
                    <i class="fa-solid fa-right-from-bracket mr-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-md mx-auto px-4 py-10">
        <div class="border border-green-900/60 rounded-lg bg-gray-900/80 glow-border p-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-500/10 border border-green-500/30 mb-4">
                    <i class="fa-solid fa-gear text-2xl text-green-400"></i>
                </div>
                <h1 class="text-2xl font-bold text-green-400 glow-green">Account Settings</h1>
                <p class="text-green-600 text-sm mt-2">
                    Current email:
                    <span class="text-green-300"><?php echo htmlspecialchars($_SESSION['email'] ?? 'N/A'); ?></span>
                </p>
            </div>

            <?php if ($flag_earned): ?>
                <div class="bg-green-500/10 border border-green-500/40 text-green-300 px-4 py-3 rounded mb-4 text-sm">
                    <i class="fa-solid fa-flag mr-2"></i>
                    CSRF exploit successful! Flag: <code class="text-green-400">flag{csrf_email_takeover_007}</code>
                </div>
            <?php elseif (!empty($message)): ?>
                <div class="bg-green-500/10 border border-green-500/40 text-green-300 px-4 py-3 rounded mb-4 text-sm">
                    <i class="fa-solid fa-check-circle mr-2"></i><?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- ⚠ VULNERABLE FORM: no CSRF token field -->
            <form method="POST" action="change_email.php" class="space-y-4">
                <div>
                    <label class="block text-green-500 text-xs uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-envelope mr-1"></i>New Email
                    </label>
                    <input type="email" name="email" required
                        class="w-full bg-gray-950 border border-green-900/60 rounded px-4 py-3 text-green-300 placeholder-green-900 focus:outline-none focus:border-green-500 input-glow transition"
                        placeholder="new@email.com">
                </div>
                <button type="submit"
                    class="w-full bg-green-500/20 border border-green-500/60 text-green-400 font-bold py-3 rounded hover:bg-green-500/30 transition uppercase tracking-wider">
                    <i class="fa-solid fa-floppy-disk mr-2"></i>Update Email
                </button>
            </form>

            <!-- Lab Notes -->
            <div class="mt-6 code-block rounded p-4">
                <h3 class="text-green-600 text-xs uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-flask mr-1"></i>Lab Notes — CSRF
                </h3>
                <p class="text-green-700 text-xs leading-relaxed mb-2">
                    This form updates your email using only your session cookie for
                    authentication — there is no CSRF token. That means any external
                    page can trigger this request on your behalf while you are logged in.
                </p>
                <p class="text-green-700 text-xs leading-relaxed">
                    Try building an HTML page with an auto-submitting form that targets
                    this endpoint, and open it while logged in here to see the effect.
                </p>
            </div>
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
