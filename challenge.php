<?php
/**
 * ============================================================
 * challenge.php — Individual Challenge Page
 * ============================================================
 * CTF Platform | Educational Cyber Security Lab
 *
 * Displays a specific challenge based on the 'id' URL parameter.
 *
 * ⚠ VULNERABILITY: GET-based SQL Injection (SQLi)
 * ------------------------------------------------------------
 * The 'id' parameter from the URL is directly concatenated
 * into the SQL query WITHOUT prepared statements.
 *
 * EXPLOIT EXAMPLES:
 *   1. Basic:      challenge.php?id=1 OR 1=1
 *   2. Union:      challenge.php?id=1 UNION SELECT 1,2,3,4,5,6
 *   3. Get flags:  challenge.php?id=1 UNION SELECT 1,flag,flag,flag,flag,flag FROM challenges
 *   4. Get users:  challenge.php?id=1 UNION SELECT 1,username,password_hash,email,1,1 FROM users
 *
 * LEARNING OBJECTIVE: Understand why parameterized queries
 * (prepared statements) are essential for security.
 * ============================================================
 */

session_start();
require_once 'db.php';

$is_logged_in = isset($_SESSION['user_id']);
$challenge    = null;
$error        = '';

/**
 * ⚠ VULNERABLE CODE
 * The $_GET['id'] value is directly concatenated into the SQL string.
 * This allows an attacker to inject arbitrary SQL commands.
 *
 * SAFE ALTERNATIVE (what you SHOULD do):
 *   $query = "SELECT * FROM challenges WHERE id = $1";
 *   $result = pg_query_params($dbconn, $query, array($_GET['id']));
 */
if (isset($_GET['id'])) {
    $id = $_GET['id'];  // RAW user input — no sanitization!

    $query  = "SELECT * FROM challenges WHERE id = " . $id;
    $result = pg_query($dbconn, $query);

    if ($result && pg_num_rows($result) > 0) {
        $challenge = pg_fetch_assoc($result);
    } else {
        $error = 'Challenge not found. (PostgreSQL says: ' . pg_last_error($dbconn) . ')';
    }
} else {
    $error = 'No challenge ID specified. Use ?id=1';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $challenge ? htmlspecialchars($challenge['title']) : 'Challenge'; ?> — CTF Platform</title>
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

        <?php if (!empty($error)): ?>
            <!-- Error State -->
            <div class="border border-red-500/40 rounded-lg bg-red-500/5 p-8 text-center">
                <i class="fa-solid fa-triangle-exclamation text-4xl text-red-400 mb-4"></i>
                <h1 class="text-xl font-bold text-red-400 mb-2">Error</h1>
                <p class="text-red-500 font-mono text-sm"><?php echo $error; ?></p>
                <a href="index.php" class="inline-block mt-6 text-green-400 hover:text-green-300 underline">
                    <i class="fa-solid fa-arrow-left mr-1"></i>Back to Dashboard
                </a>
            </div>

        <?php elseif ($challenge): ?>
            <!-- Challenge Details -->
            <div class="border border-green-900/50 rounded-lg bg-gray-900/60 overflow-hidden glow-border">
                <!-- Challenge Header -->
                <div class="border-b border-green-900/40 p-6 bg-green-500/5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs uppercase tracking-wider bg-green-500/10 border border-green-500/30 text-green-500 px-3 py-1 rounded">
                            <i class="fa-solid fa-folder mr-1"></i><?php echo htmlspecialchars($challenge['category']); ?>
                        </span>
                        <span class="text-green-400 font-bold">
                            <i class="fa-solid fa-star mr-1"></i><?php echo $challenge['points']; ?> points
                        </span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-green-300 glow-green">
                        <?php echo htmlspecialchars($challenge['title']); ?>
                    </h1>
                </div>

                <!-- Challenge Body -->
                <div class="p-6">
                    <div class="mb-6">
                        <h2 class="text-sm uppercase tracking-wider text-green-600 mb-3">
                            <i class="fa-solid fa-align-left mr-1"></i>Description
                        </h2>
                        <p class="text-green-400 leading-relaxed">
                            <?php echo htmlspecialchars($challenge['description']); ?>
                        </p>
                    </div>

                    <!-- Flag Submission -->
                    <div class="border border-green-900/40 rounded-lg p-6 bg-gray-950/50">
                        <h2 class="text-sm uppercase tracking-wider text-green-600 mb-4">
                            <i class="fa-solid fa-flag mr-1"></i>Submit Flag
                        </h2>
                        <?php if ($is_logged_in): ?>
                            <form method="POST" action="check_flag.php" class="flex gap-3">
                                <input type="hidden" name="challenge_id" value="<?php echo $challenge['id']; ?>">
                                <input type="text" name="flag" required
                                    class="flex-1 bg-gray-900 border border-green-900/60 rounded px-4 py-3 text-green-300 placeholder-green-800 focus:outline-none focus:border-green-500 input-glow transition font-mono"
                                    placeholder="flag{...}">
                                <button type="submit"
                                    class="bg-green-500/20 border border-green-500/60 text-green-400 font-bold px-6 py-3 rounded hover:bg-green-500/30 transition uppercase tracking-wider">
                                    <i class="fa-solid fa-paper-plane mr-2"></i>Submit
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="text-green-700 text-sm">
                                <i class="fa-solid fa-lock mr-1"></i>
                                You must be logged in to submit flags.
                                <a href="login.php" class="text-green-400 underline hover:text-green-300">Login here</a>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Lab Notes (Educational) -->
                    <div class="mt-6 code-block rounded p-4">
                        <h3 class="text-green-600 text-xs uppercase tracking-wider mb-2">
                            <i class="fa-solid fa-flask mr-1"></i>Lab Notes — SQL Injection
                        </h3>
                        <p class="text-green-700 text-xs leading-relaxed mb-2">
                            This page accepts a <code class="text-green-500">?id=</code> parameter in the URL.
                            The backend PHP code directly concatenates this value into the SQL query:
                        </p>
                        <pre class="text-green-500 text-xs bg-black/30 p-3 rounded overflow-x-auto mb-2">SELECT * FROM challenges WHERE id = <span class="text-red-400"><?php echo $_GET['id'] ?? 'YOUR_INPUT'; ?></span></pre>
                        <p class="text-green-700 text-xs leading-relaxed">
                            Try modifying the URL parameter to see how the database responds.
                            Can you extract data from other tables?
                        </p>
                    </div>
                </div>
            </div>

            <!-- Back Link -->
            <div class="mt-6 text-center">
                <a href="index.php" class="text-green-600 hover:text-green-400 text-sm transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i>Back to Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="text-center text-green-900 text-xs py-8 mt-12 border-t border-green-900/20">
        <p>CTF Platform v1.0 — Educational Cyber Security Lab</p>
        <p class="mt-1">⚠ For educational purposes only. Do not deploy in production.</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
