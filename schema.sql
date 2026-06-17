-- ============================================================
-- CTF Platform — PostgreSQL Schema
-- Educational Cyber Security Lab
-- ============================================================
-- WARNING: This application contains INTENTIONAL vulnerabilities
-- for educational purposes. DO NOT deploy in production.
-- ============================================================

-- Drop database if exists
DROP DATABASE IF EXISTS ctf_platform;

-- Drop user if exists
DROP ROLE IF EXISTS ctf_user;

-- Create user
CREATE ROLE ctf_user LOGIN PASSWORD 'ctf_password';

-- Create database with owner
CREATE DATABASE ctf_platform OWNER ctf_user;

-- Connect to database
\c ctf_platform

-- Drop tables if they exist (clean slate)
DROP TABLE IF EXISTS feedback CASCADE;
DROP TABLE IF EXISTS challenges CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- ============================================================
-- Table: users
-- Stores registered user accounts
-- Password stored with MD5 hash (intentionally weak for CTF)
-- ============================================================
CREATE TABLE users (
    id              SERIAL PRIMARY KEY,
    username        VARCHAR(50) UNIQUE NOT NULL,
    email           VARCHAR(100) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Table: challenges
-- Stores CTF challenges for the platform
-- ============================================================
CREATE TABLE challenges (
    id              SERIAL PRIMARY KEY,
    title           VARCHAR(200) NOT NULL,
    category        VARCHAR(50) NOT NULL,
    description     TEXT NOT NULL,
    points          INTEGER DEFAULT 100,
    flag            VARCHAR(255),          -- the hidden flag for each challenge
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Table: feedback
-- Stores user feedback/comments
-- NOTE: This table is intentionally vulnerable to Stored XSS.
--       Comments are stored and displayed without sanitization.
-- ============================================================
CREATE TABLE feedback (
    id              SERIAL PRIMARY KEY,
    user_id         INTEGER REFERENCES users(id) ON DELETE CASCADE,
    username        VARCHAR(50) NOT NULL,  -- denormalized for display
    comment         TEXT NOT NULL,         -- RAW input stored here
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Sample Data: Challenges
-- ============================================================
INSERT INTO challenges (title, category, description, points, flag) VALUES
('Login Bypass 101', 'Web Security', 'Can you log in without a password? The login form on this site might have a flaw. Find a way in!', 100, 'flag{sql_injection_bypass_001}'),
('Hidden in Plain Sight', 'Web Security', 'The feedback section seems to accept anything you throw at it. Can you make it dance?', 150, 'flag{stored_xss_master_002}'),
('The Admin Portal', 'Web Security', 'There is a secret admin page somewhere. The flag is hidden in the database. Can you extract it?', 200, 'flag{union_select_admin_003}'),
('Cookie Monster', 'Web Security', 'The session cookies look suspicious. Can you decode or manipulate them to gain access?', 175, 'flag{cookie_manipulation_004}'),
('Directory Traversal', 'Web Security', 'The file viewer parameter might be tricked into revealing files it should not.', 250, 'flag{path_traversal_king_005}'),
('Command Injection', 'Web Security', 'The ping utility on the network tools page takes user input. Can you chain commands?', 300, 'flag{command_injection_pro_006}');

-- ============================================================
-- Sample Data: Users (password: 'password123' MD5 hashed)
-- ============================================================
INSERT INTO users (username, email, password_hash) VALUES
('admin', 'admin@ctf.local', MD5('admin123secure!')),
('player1', 'player1@ctf.local', MD5('password123')),
('hacker', 'hacker@ctf.local', MD5('letmein'));

-- ============================================================
-- Sample Data: Feedback (includes a benign example)
-- ============================================================
INSERT INTO feedback (user_id, username, comment) VALUES
(1, 'admin', 'Welcome to the CTF platform! Find all the flags. Good luck!'),
(2, 'player1', 'This platform is awesome! I love the challenges.'),
(3, 'hacker', 'I think I found something interesting in the feedback section...');

-- Grant sequence usage for SERIAL columns (needed for INSERT)
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO ctf_user;
-- Grant table permissions
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO ctf_user;

-- Grant sequence permissions
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO ctf_user;

-- ============================================================
-- Verification queries (run these to confirm setup)
-- ============================================================
-- SELECT * FROM users;
-- SELECT * FROM challenges;
-- SELECT * FROM feedback;
