<?php
/**
 * ============================================================
 * check_flag.php — Flag Verification
 * ============================================================
 * CTF Platform | Educational Cyber Security Lab
 * ============================================================
 */

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $challenge_id  = intval($_POST['challenge_id'] ?? 0);
    $submitted_flag = trim($_POST['flag'] ?? '');

    // Fetch challenge flag
    $query = "SELECT flag, title
              FROM challenges
              WHERE id = {$challenge_id}";

    $result = pg_query($dbconn, $query);

    if (!$result) {

        $_SESSION['flag_message'] = 'error';

    } elseif (pg_num_rows($result) > 0) {

        $challenge = pg_fetch_assoc($result);

        if ($submitted_flag === $challenge['flag']) {

            $_SESSION['flag_message'] = 'correct';
            $_SESSION['flag_title']   = $challenge['title'];

        } else {

            $_SESSION['flag_message'] = 'incorrect';
        }

    } else {

        $_SESSION['flag_message'] = 'error';
    }

    header("Location: challenge.php?id={$challenge_id}");
    exit;
}

// GET request - redirect to homepage
header('Location: index.php');
exit;