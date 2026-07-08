<?php
/**
 * ============================================================
 * index.php — Dashboard / Home Page
 * ============================================================
 * CTF Platform | Educational Cyber Security Lab
 *
 * Displays available CTF challenges from the database.
 * Shows different content for logged-in vs guest users.
 * ============================================================
 */

session_start();
require_once 'db.php';

// ---- Fetch all challenges ----
$query   = "SELECT id, title, category, description, points FROM challenges ORDER BY points ASC";
$result  = pg_query($dbconn, $query);
$challenges = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $challenges[] = $row;
    }
}

$is_logged_in = isset($_SESSION['user_id']);
$username     = $is_logged_in ? $_SESSION['username'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — CTF Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&display=swap');
        body { font-family: 'Fira Code', monospace; }
        .glow-green { text-shadow: 0 0 10px #00ff41, 0 0 20px #00ff41; }
        .glow-border { box-shadow: 0 0 10px rgba(0, 255, 65, 0.3), inset 0 0 10px rgba(0, 255, 65, 0.1); }
        .card-hover:hover {
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.2);
            transform: translateY(-2px);
        }
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
        .typing {
            overflow: hidden; white-space: nowrap;
            animation: typing 3s steps(30, end);
        }
        @keyframes typing { from { width: 0; } to { width: 100%; } }
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
                <a href="index.php" class="text-green-300">
                    <i class="fa-solid fa-house mr-1"></i>Home
                </a>
                <a href="feedback.php" class="hover:text-green-300 transition">
                    <i class="fa-solid fa-comments mr-1"></i>Feedback
                </a>
                <?php if ($is_logged_in): ?>
                    <span class="text-green-600">
                        <i class="fa-solid fa-user mr-1"></i><?php echo htmlspecialchars($username); ?>
                    </span>
                    <a href="change_email.php" class="hover:text-green-300 transition">
                        <i class="fa-solid fa-gear mr-1"></i>Settings
                    </a>
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

    <!-- Hero Section -->
    <div class="max-w-6xl mx-auto px-4 pt-12 pb-8">
        <div class="text-center mb-12">
            <div class="inline-block border border-green-500/30 rounded-lg px-6 py-2 mb-4 bg-green-500/5">
                <span class="text-green-500 text-xs uppercase tracking-widest">
                    <i class="fa-solid fa-shield-halved mr-1"></i>Cyber Security Lab
                </span>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-green-400 glow-green mb-4">
                Capture The Flag
            </h1>
            <p class="text-green-600 max-w-2xl mx-auto">
                Test your hacking skills on intentionally vulnerable challenges.
                Find flags, exploit weaknesses, and learn web security the hands-on way.
            </p>

            <!-- Stats Bar -->
            <div class="flex justify-center gap-8 mt-8 text-sm">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-400"><?php echo count($challenges); ?></div>
                    <div class="text-green-700 text-xs uppercase">Challenges</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-400">
                        <?php
                        $total_points = 0;
                        foreach ($challenges as $c) { $total_points += $c['points']; }
                        echo $total_points;
                        ?>
                    </div>
                    <div class="text-green-700 text-xs uppercase">Total Points</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-400">
                        <?php echo $is_logged_in ? '1' : '0'; ?>
                    </div>
                    <div class="text-green-700 text-xs uppercase">Session</div>
                </div>
            </div>
        </div>

        <!-- Challenge Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($challenges as $challenge): ?>
                <a href="challenge.php?id=<?php echo $challenge['id']; ?>"
                   class="block border border-green-900/50 rounded-lg bg-gray-900/60 p-6 card-hover transition-all duration-300 hover:border-green-500/50 group">
                    <!-- Category Badge -->
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs uppercase tracking-wider bg-green-500/10 border border-green-500/30 text-green-500 px-2 py-1 rounded">
                            <?php echo htmlspecialchars($challenge['category']); ?>
                        </span>
                        <span class="text-green-600 text-sm">
                            <i class="fa-solid fa-star mr-1"></i><?php echo $challenge['points']; ?> pts
                        </span>
                    </div>

                    <!-- Title -->
                    <h3 class="text-lg font-bold text-green-300 group-hover:text-green-200 mb-3 transition">
                        <?php echo htmlspecialchars($challenge['title']); ?>
                    </h3>

                    <!-- Description -->
                    <p class="text-green-700 text-sm leading-relaxed mb-4">
                        <?php echo htmlspecialchars($challenge['description']); ?>
                    </p>

                    <!-- Footer -->
                    <div class="flex items-center justify-between border-t border-green-900/30 pt-3">
                        <span class="text-green-900 text-xs">
                            <i class="fa-solid fa-flag mr-1"></i>ID: <?php echo $challenge['id']; ?>
                        </span>
                        <span class="text-green-500 text-xs group-hover:text-green-400 transition">
                            Attempt <i class="fa-solid fa-arrow-right ml-1"></i>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Quick Links -->
        <div class="mt-12 border border-green-900/40 rounded-lg bg-gray-900/40 p-6">
            <h2 class="text-lg font-bold text-green-400 mb-4">
                <i class="fa-solid fa-compass mr-2"></i>Explore
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="feedback.php" class="flex items-center gap-3 border border-green-900/40 rounded p-4 hover:border-green-500/40 hover:bg-green-500/5 transition">
                    <i class="fa-solid fa-comments text-green-500 text-xl"></i>
                    <div>
                        <div class="text-green-300 text-sm font-bold">Feedback Board</div>
                        <div class="text-green-700 text-xs">Leave a message for the community</div>
                    </div>
                </a>
                <a href="challenge.php?id=1" class="flex items-center gap-3 border border-green-900/40 rounded p-4 hover:border-green-500/40 hover:bg-green-500/5 transition">
                    <i class="fa-solid fa-bug text-green-500 text-xl"></i>
                    <div>
                        <div class="text-green-300 text-sm font-bold">Start Hacking</div>
                        <div class="text-green-700 text-xs">Begin with the first challenge</div>
                    </div>
                </a>
                <?php if (!$is_logged_in): ?>
                    <a href="register.php" class="flex items-center gap-3 border border-green-900/40 rounded p-4 hover:border-green-500/40 hover:bg-green-500/5 transition">
                        <i class="fa-solid fa-user-plus text-green-500 text-xl"></i>
                        <div>
                            <div class="text-green-300 text-sm font-bold">Join Now</div>
                            <div class="text-green-700 text-xs">Register to track your progress</div>
                        </div>
                    </a>
                <?php else: ?>
                    <a href="feedback.php" class="flex items-center gap-3 border border-green-900/40 rounded p-4 hover:border-green-500/40 hover:bg-green-500/5 transition">
                        <i class="fa-solid fa-pen text-green-500 text-xl"></i>
                        <div>
                            <div class="text-green-300 text-sm font-bold">Write Feedback</div>
                            <div class="text-green-700 text-xs">Share your thoughts</div>
                        </div>
                    </a>
                <?php endif; ?>
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
