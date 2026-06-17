<?php
/**
 * ============================================================
 * db.php — PostgreSQL Database Connection
 * ============================================================
 * CTF Platform | Educational Cyber Security Lab
 * ============================================================
 */

// ---- Database Configuration ----
$db_host     = 'localhost';
$db_port     = '5432';
$db_name     = 'ctf_platform';
$db_user     = 'ctf_user';
$db_password = 'ctf_password';

// ---- Build Connection String ----
$conn_string = sprintf(
    "host=%s port=%s dbname=%s user=%s password=%s",
    $db_host,
    $db_port,
    $db_name,
    $db_user,
    $db_password
);

// ---- Establish Connection ----
$dbconn = pg_connect($conn_string);

// ---- Check Connection ----
if (!$dbconn) {
    die(
        '<!DOCTYPE html>
        <html>
        <head>
            <title>Database Error</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-900 text-white font-mono">
            <div class="max-w-2xl mx-auto mt-24 p-8 border border-red-500 rounded-xl bg-gray-800">
                <h1 class="text-3xl font-bold text-red-500 mb-4">
                    ⚠ Database Connection Failed
                </h1>

                <p class="mb-4 text-gray-300">
                    Could not connect to PostgreSQL database.
                </p>

                <div class="bg-black text-red-400 p-4 rounded overflow-auto">
                    Please check:
                    <ul class="list-disc ml-6 mt-2">
                        <li>PostgreSQL service is running</li>
                        <li>Database name: ctf_platform</li>
                        <li>User: ctf_user</li>
                        <li>Password: ctf_password</li>
                    </ul>
                </div>
            </div>
        </body>
        </html>'
    );
}

// ---- Set Client Encoding ----
pg_set_client_encoding($dbconn, 'UTF8');

// ---- Optional Helper Function ----
function db()
{
    global $dbconn;
    return $dbconn;
}