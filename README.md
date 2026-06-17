# 🔓 CTF Platform — Educational Cyber Security Lab

An intentionally vulnerable Capture The Flag (CTF) platform built for learning web security concepts.

## ⚠️ WARNING

**This application contains INTENTIONAL security vulnerabilities.**
- **DO NOT** deploy on a public-facing server.
- **DO NOT** use in production environments.
- Use only in isolated lab environments for educational purposes.

---

## 📁 File Structure

```
ctf-platform/
├── schema.sql          — PostgreSQL database schema & sample data
├── db.php              — Database connection configuration
├── register.php        — User registration page
├── login.php           — User login page (SQLi vulnerable)
├── index.php           — Dashboard / challenge listing
├── challenge.php       — Individual challenge page (SQLi vulnerable)
├── feedback.php        — Feedback/guestbook (Stored XSS vulnerable)
├── check_flag.php      — Flag verification handler
├── logout.php          — Session destruction
├── js/
│   └── script.js       — Frontend JavaScript
└── README.md           — This file
```

## 🛠 Setup Instructions

### Prerequisites
- PHP 7.4+ with PostgreSQL extension (`php-pgsql`)
- PostgreSQL 12+
- Web server (Apache/Nginx) or PHP built-in server

### Step 1: Create the Database

Run one line command to create the database and tables:
```bash
sudo -u postgres psql -d postgres -f schema.sql
php -S localhost:8080
```

### Step 2: Configure Database Connection

Edit `db.php` and update the database credentials:
```php
$db_host     = 'localhost';
$db_port     = '5432';
$db_name     = 'ctf_platform';
$db_user     = 'ctf_user';
$db_password = 'ctf_password_123';
```

### Step 3: Deploy

**Option A: PHP Built-in Server (easiest for lab)**
```bash
cd /path/to/ctf-platform
php -S localhost:8080
```
Then open `http://localhost:8080` in your browser.

**Option B: Apache**
Copy the `ctf-platform` directory to your web root and ensure `mod_php` is enabled.

### Step 4: Test

1. Open `http://localhost:8080` in your browser
2. Register a new account
3. Log in
4. Explore the challenges and feedback section

---

## 🎯 Lab Tasks & Vulnerabilities

### Task 1: SQL Injection (Login Bypass)
**Location:** `login.php`
**Difficulty:** Easy

The login form directly concatenates user input into the SQL query.

**Exploit:**
- Username: `' OR '1'='1' --`
- Password: `anything`

**Flag:** `flag{sql_injection_bypass_001}`

---

### Task 2: Stored XSS (Feedback Section)
**Location:** `feedback.php`
**Difficulty:** Easy

Comments are stored and displayed without sanitization.

**Exploit:** Post this as a comment:
```html
<script>alert('XSS')</script>
```

**Flag:** `flag{stored_xss_master_002}`

---

### Task 3: SQL Injection (Union-Based Data Extraction)
**Location:** `challenge.php`
**Difficulty:** Medium

The `id` parameter is vulnerable to UNION-based SQL injection.

**Step 1 — Find column count:**
```
challenge.php?id=1 ORDER BY 1--
challenge.php?id=1 ORDER BY 2--
... (until error)
```

**Step 2 — Extract flags:**
```
challenge.php?id=1 UNION SELECT 1,flag,flag,flag,flag,flag FROM challenges
```

**Step 3 — Extract user credentials:**
```
challenge.php?id=1 UNION SELECT 1,username,password_hash,email,1,1 FROM users
```

**Flag:** `flag{union_select_admin_003}`

---

### Task 4: Cookie Analysis
**Difficulty:** Easy

Inspect the session cookie in browser DevTools (F12 → Application → Cookies).

**Flag:** `flag{cookie_manipulation_004}`

---

### Task 5: Directory Traversal (Bonus)
**Difficulty:** Medium

Explore URL parameters and file inclusion patterns.

**Flag:** `flag{path_traversal_king_005}`

---

### Task 6: Command Injection (Bonus)
**Difficulty:** Hard

Look for any input that might be passed to system commands.

**Flag:** `flag{command_injection_pro_006}`

---

## 🔒 How to Fix (For Students)

After completing the challenges, here's how to fix each vulnerability:

### Fix SQL Injection
```php
// VULNERABLE:
$query = "SELECT * FROM users WHERE username = '{$username}'";

// SAFE — Use prepared statements:
$result = pg_query_params($dbconn,
    "SELECT * FROM users WHERE username = $1 AND password_hash = $2",
    array($username, $password_hash)
);
```

### Fix Stored XSS
```php
// VULNERABLE:
echo $row['comment'];

// SAFE — Use output encoding:
echo htmlspecialchars($row['comment'], ENT_QUOTES, 'UTF-8');
```

### Fix Weak Hashing
```php
// VULNERABLE:
$hash = md5($password);

// SAFE — Use bcrypt:
$hash = password_hash($password, PASSWORD_BCRYPT);
```

---

## 📝 Default Test Accounts

| Username | Password      |
|----------|---------------|
| admin    | admin123secure! |
| player1  | password123   |
| hacker   | letmein       |

---

*Built for educational purposes. Learn, hack, and secure! 🔐*
