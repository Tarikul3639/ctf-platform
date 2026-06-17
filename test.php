<?php

$conn = pg_connect(
    "host=localhost port=5432 dbname=ctf_platform user=ctf_user password=ctf_password"
);

if (!$conn) {
    die("Connection failed");
}

echo "Connected successfully!<br>";

$result = pg_query(
    $conn,
    "SELECT * FROM users WHERE username='admin' AND password_hash=MD5('admin123secure!')"
);

echo "Rows found: " . pg_num_rows($result);