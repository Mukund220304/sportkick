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
    <div class="auth-split-card">
        <!-- Left Brand Visual Panel -->
        <div class="auth-split-card__visual">
            <img
                src="https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=1000&q=85"
                alt="Join SportKick Roster"
                class="auth-split-card__bg"
            >
            <div class="auth-split-card__overlay">
                <span class="section-eyebrow">Join The Roster</span>
                <h2 class="auth-split-card__headline">GEAR UP FOR GREATNESS.</h2>
                <p class="auth-split-card__sub">
                    Create your free SportKick athlete profile for priority access to limited footwear releases and instant 10% savings with code KICK10.
                </p>
                <div class="auth-perks">
                    <span>✓ Free Express Shipping Over ₹4,999</span>
                    <span>✓ 30-Day Performance Trial Guarantee</span>
                    <span>✓ Real-Time Order History &amp; Tracking</span>
                </div>
            </div>
        </div>

        <!-- Right Registration Form Panel -->
        <div class="auth-split-card__form">
            <div style="margin-bottom:22px;">
                <span class="section-eyebrow">New Athlete Membership</span>
                <h1 style="font-family:var(--font-display);font-size:2.4rem;font-weight:900;font-style:italic;text-transform:uppercase;line-height:1;">
                    Create Athlete Account
                </h1>
                <p style="font-size:0.9rem;color:var(--text-secondary);margin-top:6px;">
                    Fill in your details below to join the SportKick Locker Club.
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

                <button type="submit" id="regSubmitBtn" class="btn btn--primary btn--lg btn--full" style="margin-top:8px;">
                    Create My Account &rarr;
                </button>
            </form>

            <div style="text-align:center;margin-top:20px;padding-top:16px;border-top:1px solid var(--border-light);font-size:0.9rem;">
                Already have a SportKick account? <a href="login.php" style="color:var(--accent);font-weight:800;">Sign In Here &rarr;</a>
            </div>
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
