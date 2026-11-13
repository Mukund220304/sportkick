<?php
/**
 * SportKick - User Login Page (login.php)
 */

require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: profile.php');
    exit;
}

$pageTitle = 'Sign In To SportKick';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper">
        <div style="text-align:center;margin-bottom:26px;">
            <span class="section-eyebrow">Athlete Access</span>
            <h1 style="font-family:var(--font-display);font-size:2.4rem;font-weight:900;font-style:italic;text-transform:uppercase;">
                Welcome Back
            </h1>
            <p style="font-size:0.9rem;color:var(--text-secondary);">
                Sign in to access your saved wishlist, locker bag, and order tracking.
            </p>
        </div>

        <?php if (!empty($_GET['logged_out'])): ?>
            <div style="padding:10px 14px;background:rgba(16,185,129,0.12);border-left:4px solid var(--success);color:var(--success);font-weight:700;font-size:0.86rem;margin-bottom:18px;">
                You have been signed out safely.
            </div>
        <?php endif; ?>

        <div id="loginErrorBox" style="display:none;padding:12px 14px;background:rgba(239,68,68,0.1);border-left:4px solid var(--danger);color:var(--danger);font-weight:700;font-size:0.88rem;margin-bottom:18px;"></div>

        <form id="loginForm" novalidate>
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="redirect" value="<?= e($_GET['redirect'] ?? 'profile.php') ?>">

            <div class="form-group">
                <label for="loginEmail" class="form-label">Email Address *</label>
                <input
                    type="email"
                    id="loginEmail"
                    name="email"
                    class="form-control"
                    required
                    placeholder="athlete@sportkick.com"
                >
            </div>

            <div class="form-group">
                <label for="loginPassword" class="form-label">Password *</label>
                <input
                    type="password"
                    id="loginPassword"
                    name="password"
                    class="form-control"
                    required
                    placeholder="Enter your password"
                >
            </div>

            <button type="submit" id="loginSubmitBtn" class="btn btn--primary btn--lg btn--full" style="margin-top:8px;">
                Sign In &rarr;
            </button>
        </form>

        <!-- Quick Demo Evaluator Credential Helper -->
        <div style="margin-top:20px;padding:14px;background:var(--bg-light);border-radius:var(--radius-sm);border:1px dashed var(--border-light);font-size:0.82rem;">
            <div style="font-weight:800;text-transform:uppercase;color:var(--text-muted);margin-bottom:6px;">
                Demo Athlete Account:
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                <span><code>athlete@sportkick.com</code> / <code>SportKick123</code></span>
                <button type="button" id="fillDemoBtn" class="btn btn--dark btn--sm" style="padding:5px 12px;font-size:0.78rem;">
                    Auto-Fill Demo
                </button>
            </div>
        </div>

        <div style="text-align:center;margin-top:22px;padding-top:18px;border-top:1px solid var(--border-light);font-size:0.9rem;">
            New to SportKick? <a href="register.php" style="color:var(--accent);font-weight:800;">Create an Athlete Account &rarr;</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    const errBox = document.getElementById('loginErrorBox');
    const submitBtn = document.getElementById('loginSubmitBtn');
    const fillDemoBtn = document.getElementById('fillDemoBtn');

    if (fillDemoBtn) {
        fillDemoBtn.addEventListener('click', function () {
            document.getElementById('loginEmail').value = 'athlete@sportkick.com';
            document.getElementById('loginPassword').value = 'SportKick123';
        });
    }

    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            errBox.style.display = 'none';

            const payload = Object.fromEntries(new FormData(form).entries());
            submitBtn.disabled = true;
            submitBtn.textContent = 'Signing In...';

            try {
                const res = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    window.location.href = data.redirect || 'profile.php';
                } else {
                    errBox.textContent = data.message || 'Invalid credentials.';
                    errBox.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign In →';
                }
            } catch (err) {
                errBox.textContent = 'Network error while signing in.';
                errBox.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign In →';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

