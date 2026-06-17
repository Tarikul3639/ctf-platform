/**
 * ============================================================
 * script.js — Frontend JavaScript
 * ============================================================
 * CTF Platform | Educational Cyber Security Lab
 *
 * Handles interactive UI actions, form validation,
 * and visual effects for the CTF platform.
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', function () {

    // ========================================
    // Terminal Typing Effect (Hero Section)
    // ========================================
    const typingElements = document.querySelectorAll('.typing-effect');
    typingElements.forEach(function (el) {
        const text = el.textContent;
        el.textContent = '';
        let i = 0;
        const speed = 50;

        function typeChar() {
            if (i < text.length) {
                el.textContent += text.charAt(i);
                i++;
                setTimeout(typeChar, speed);
            }
        }
        typeChar();
    });

    // ========================================
    // Form Validation — Registration
    // ========================================
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            const password  = this.querySelector('input[name="password"]').value;
            const confirm   = this.querySelector('input[name="confirm_password"]').value;
            const username  = this.querySelector('input[name="username"]').value;

            if (username.length < 3) {
                e.preventDefault();
                showNotification('Username must be at least 3 characters.', 'error');
                return;
            }

            if (password.length < 6) {
                e.preventDefault();
                showNotification('Password must be at least 6 characters.', 'error');
                return;
            }

            if (password !== confirm) {
                e.preventDefault();
                showNotification('Passwords do not match.', 'error');
                return;
            }
        });
    }

    // ========================================
    // Form Validation — Login
    // ========================================
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            const username = this.querySelector('input[name="username"]').value.trim();
            const password = this.querySelector('input[name="password"]').value;

            if (!username || !password) {
                e.preventDefault();
                showNotification('Please fill in all fields.', 'error');
                return;
            }
        });
    }

    // ========================================
    // Form Validation — Feedback
    // ========================================
    const feedbackForm = document.getElementById('feedbackForm');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', function (e) {
            const comment = this.querySelector('textarea[name="comment"]').value.trim();

            if (!comment) {
                e.preventDefault();
                showNotification('Comment cannot be empty.', 'error');
                return;
            }

            if (comment.length > 1000) {
                e.preventDefault();
                showNotification('Comment must be 1000 characters or less.', 'error');
                return;
            }
        });
    }

    // ========================================
    // Character Counter (Feedback Textarea)
    // ========================================
    const commentTextarea = document.querySelector('textarea[name="comment"]');
    if (commentTextarea) {
        // Create counter element
        const counter = document.createElement('div');
        counter.className = 'text-green-900 text-xs mt-1 text-right';
        counter.innerHTML = '<span id="charCount">0</span> / 1000';
        commentTextarea.parentNode.appendChild(counter);

        commentTextarea.addEventListener('input', function () {
            const count = this.value.length;
            const charCount = document.getElementById('charCount');
            if (charCount) {
                charCount.textContent = count;
                if (count > 900) {
                    charCount.className = 'text-red-400';
                } else if (count > 700) {
                    charCount.className = 'text-yellow-400';
                } else {
                    charCount.className = '';
                }
            }
        });
    }

    // ========================================
    // Notification System
    // ========================================
    function showNotification(message, type) {
        // Remove existing notifications
        const existing = document.querySelectorAll('.notification-toast');
        existing.forEach(function (el) { el.remove(); });

        const notification = document.createElement('div');
        notification.className = 'notification-toast fixed top-20 right-4 z-50 px-6 py-3 rounded-lg border text-sm font-mono shadow-lg transition-all duration-300 transform translate-x-full';

        if (type === 'error') {
            notification.classList.add('bg-red-500/10', 'border-red-500/40', 'text-red-400');
            notification.innerHTML = '<i class="fa-solid fa-triangle-exclamation mr-2"></i>' + message;
        } else if (type === 'success') {
            notification.classList.add('bg-green-500/10', 'border-green-500/40', 'text-green-400');
            notification.innerHTML = '<i class="fa-solid fa-check-circle mr-2"></i>' + message;
        } else {
            notification.classList.add('bg-blue-500/10', 'border-blue-500/40', 'text-blue-400');
            notification.innerHTML = '<i class="fa-solid fa-info-circle mr-2"></i>' + message;
        }

        document.body.appendChild(notification);

        // Animate in
        setTimeout(function () {
            notification.classList.remove('translate-x-full');
        }, 10);

        // Auto-remove after 4 seconds
        setTimeout(function () {
            notification.classList.add('translate-x-full', 'opacity-0');
            setTimeout(function () { notification.remove(); }, 300);
        }, 4000);
    }

    // ========================================
    // Challenge Card Hover Effect
    // ========================================
    const challengeCards = document.querySelectorAll('.card-hover');
    challengeCards.forEach(function (card) {
        card.addEventListener('mouseenter', function () {
            this.style.transition = 'all 0.3s ease';
        });
    });

    // ========================================
    // Smooth Scroll for Anchor Links
    // ========================================
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                e.preventDefault();
                const target = document.querySelector(targetId);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });

    // ========================================
    // Console Easter Egg
    // ========================================
    console.log(
        '%c🔓 CTF Platform — Educational Cyber Security Lab',
        'color: #00ff41; font-size: 16px; font-weight: bold;'
    );
    console.log(
        '%cThis application contains intentional vulnerabilities for learning purposes.',
        'color: #00ff41; font-size: 12px;'
    );
    console.log(
        '%cFind the flags! 🚩',
        'color: #ff6b6b; font-size: 14px; font-weight: bold;'
    );

});
