<?php
/**
 * SportKick - User Registration Page (register.php)
 */

require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: profile.php');
    exit;
}

$pageTitle = 'Create Athlete Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper" style="max-width:540px;">
        <div style="text-align:center;margin-bottom:26px;">
            <span class="section-eyebrow">Join The Roster</span>
            <h1 style="font-family:var(--font-display);font-size:2.4rem;font-weight:900;font-style:italic;text-transform:uppercase;">
                Create Athlete Account
            </h1>
            <p style="font-size:0.9rem;color:var(--text-secondary);">
                Get instant access to member-only colorway drops, wishlist sync, and express checkout.
            </p>
        </div>

        <div id="registerErrorBox" style="display:none;padding:12px 14px;background:rgba(239,68,68,0.1);border-left:4px solid var(--danger);color:var(--danger);font-weight:700;font-size:0.88rem;margin-bottom:18px;"></div>

        <form id="registerForm" novalidate>
            <input type="hidden" name="action" value="register">

            <div class="form-group">
                <label for="regName" class="form-label">Full Name *</label>
                <input
                    type="text"
                    id="regName"
                    name="full_name"
                    class="form-control"
                    required
                    placeholder="e.g. Rohan Verma"
                >
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="regEmail" class="form-label">Email Address *</label>
                    <input
                        type="email"
                        id="regEmail"
                        name="email"
                        class="form-control"
                        required
                        placeholder="rohan@example.com"
                    >
                </div>
                <div class="form-group">
                    <label for="regPhone" class="form-label">Phone Number</label>
                    <input
                        type="tel"
                        id="regPhone"
                        name="phone"
                        class="form-control"
                        placeholder="+91 98765 00000"
                    >
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="regPassword" class="form-label">Password (Min 6 chars) *</label>
                    <input
                        type="password"
                        id="regPassword"
                        name="password"
                        class="form-control"
                        required
                        minlength="6"
                        placeholder="At least 6 characters"
                    >
                </div>
                <div class="form-group">
                    <label for="regConfirm" class="form-label">Confirm Password *</label>
                    <input
                        type="password"
                        id="regConfirm"
                        name="confirm_password"
                        class="form-control"
                        required
                        minlength="6"
                        placeholder="Repeat password"
                    >
                </div>
            </div>

            <button type="submit" id="regSubmitBtn" class="btn btn--primary btn--lg btn--full" style="margin-top:10px;">
                Create My Account &rarr;
            </button>
        </form>

        <div style="text-align:center;margin-top:22px;padding-top:18px;border-top:1px solid var(--border-light);font-size:0.9rem;">
            Already have a SportKick account? <a href="login.php" style="color:var(--accent);font-weight:800;">Sign In Here &rarr;</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');
    const errBox = document.getElementById('registerErrorBox');
    const submitBtn = document.getElementById('regSubmitBtn');

    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            errBox.style.display = 'none';

            const payload = Object.fromEntries(new FormData(form).entries());
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating Profile...';

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
                    errBox.textContent = data.message || 'Please check the form fields.';
                    errBox.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create My Account →';
                }
            } catch (err) {
                errBox.textContent = 'Network error while creating account.';
                errBox.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create My Account →';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

