````md
# 🔓 CTF Platform — Educational Cyber Security Lab

An intentionally vulnerable Capture The Flag (CTF) platform built for learning web security concepts in a safe, isolated lab environment.

---

## ⚠️ WARNING

**This application contains INTENTIONAL security vulnerabilities.**

- **DO NOT** deploy on a public-facing server.
- **DO NOT** use in production environments.
- Use only in isolated lab environments for educational purposes.

---

## 📁 File Structure

```text
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
````

---

## 🛠 Setup Instructions

### Prerequisites

* PHP 7.4+ with PostgreSQL extension (`php-pgsql`)
* PostgreSQL 12+
* Web server such as Apache/Nginx, or PHP built-in server for local testing

### Step 1: Create the Database

Run the schema file from the `postgres` database so it can safely drop and recreate `ctf_platform`:

```bash
sudo -u postgres psql -d postgres -f schema.sql
```

This will:

* drop the existing `ctf_platform` database if it exists,
* recreate the database,
* create the demo user,
* create all tables,
* insert sample challenges, users, and feedback.

### Step 2: Configure Database Connection

Edit `db.php` and make sure the credentials match your PostgreSQL setup:

```php
$db_host     = 'localhost';
$db_port     = '5432';
$db_name     = 'ctf_platform';
$db_user     = 'ctf_user';
$db_password = 'ctf_password';
```

### Step 3: Start the Application

#### Option A: PHP Built-in Server

```bash
cd /path/to/ctf-platform
php -S localhost:8080
```

Then open:

```text
http://localhost:8080
```

#### Option B: Apache / Nginx

Copy the `ctf-platform` directory to your web root and ensure PHP support is enabled.

---

## 🎯 Lab Tasks & Vulnerabilities

### Task 1: SQL Injection (Login Bypass)

**Location:** `login.php`
**Difficulty:** Easy

The login form directly concatenates user input into the SQL query.

**Goal:** Bypass authentication and access the dashboard.

**Flag:** `flag{sql_injection_bypass_001}`

---

### Task 2: Stored XSS (Feedback Section)

**Location:** `feedback.php`
**Difficulty:** Easy

Comments are stored and displayed without sanitization.

**Goal:** Post a payload that executes in the browser when the feedback page loads.

**Flag:** `flag{stored_xss_master_002}`

---

### Task 3: SQL Injection (Union-Based Data Extraction)

**Location:** `challenge.php`
**Difficulty:** Medium

The `id` parameter is vulnerable to SQL injection.

**Goal:** Use SQL injection to inspect challenge data and extract hidden values.

**Flag:** `flag{union_select_admin_003}`

---

### Task 4: Cookie Analysis

**Difficulty:** Easy

Inspect the session cookie in browser DevTools.

**Goal:** Understand how session handling works in the lab environment.

**Flag:** `flag{cookie_manipulation_004}`

---

### Task 5: Directory Traversal (Bonus)

**Difficulty:** Medium

Explore URL parameters and file inclusion patterns.

**Goal:** Identify and abuse insecure file access behavior.

**Flag:** `flag{path_traversal_king_005}`

---

### Task 6: Command Injection (Bonus)

**Difficulty:** Hard

Look for any input that may be passed to system commands.

**Goal:** Abuse unsafe command execution paths.

**Flag:** `flag{command_injection_pro_006}`

---

## 🔑 Default Demo Accounts

These accounts are created automatically by `schema.sql`.

| Username  | Password          |
| --------- | ----------------- |
| `admin`   | `admin123secure!` |
| `player1` | `password123`     |
| `hacker`  | `letmein`         |

You can also register a new account from the application.

---

## 🔧 Notes on the Database

The schema uses:

* `ctf_platform` as the database name
* `ctf_user` as the application user
* `ctf_password` as the application password

If you need to reset everything, rerun:

```bash
sudo -u postgres psql -d postgres -f schema.sql
```

---

## 🔒 How to Fix the Vulnerabilities

This section is for students after completing the lab.

### Fix SQL Injection

**Vulnerable:**

```php
$query = "SELECT * FROM users WHERE username = '{$username}'";
```

**Safe:**

```php
$result = pg_query_params(
    $dbconn,
    "SELECT * FROM users WHERE username = $1 AND password_hash = $2",
    array($username, $password_hash)
);
```

---

### Fix Stored XSS

**Vulnerable:**

```php
echo $row['comment'];
```

**Safe:**

```php
echo htmlspecialchars($row['comment'], ENT_QUOTES, 'UTF-8');
```

---

### Fix Weak Password Hashing

**Vulnerable:**

```php
$hash = md5($password);
```

**Safe:**

```php
$hash = password_hash($password, PASSWORD_BCRYPT);
```

---

## 🧪 Quick Test Checklist

1. Start PostgreSQL.
2. Run `schema.sql`.
3. Start the PHP server.
4. Open the site in your browser.
5. Log in with a demo account.
6. Visit the challenge and feedback pages.
7. Test the lab tasks one by one.

---

## 📜 Disclaimer

This project is intended solely for educational and ethical cybersecurity training purposes.

* The vulnerabilities included here are intentional.
* Use this application only in isolated lab environments.
* Never expose it to the public internet.

---

*Built for learning, testing, and secure development practice.*

```

If you want, I can also turn this into a polished `README.md` file layout with badges, screenshots section, and a nicer GitHub style format.
```
