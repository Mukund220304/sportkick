<?php
/**
 * SportKick - Global Footer, Quick View Modal & Toast Notification Container
 */
?>
    </main>

    <!-- Global Quick View Modal -->
    <div class="modal-backdrop" id="quickViewModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="qvTitle">
        <div class="modal-dialog">
            <button type="button" class="modal-close" id="quickViewClose" aria-label="Close Quick View">&times;</button>
            <div class="modal-content" id="quickViewBody">
                <div class="qv-loading">Loading pro gear specifications...</div>
            </div>
        </div>
    </div>

    <!-- Global Toast Notification Stack -->
    <div class="toast-container" id="toastContainer" aria-live="polite"></div>

    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Col 1: Brand Identity -->
                <div class="footer-col footer-col--brand">
                    <a href="index.php" class="brand-logo">
                        <span class="brand-logo__mark">
                            <svg viewBox="0 0 36 36" width="28" height="28" fill="none">
                                <polygon points="4,28 18,6 23,6 13,22 32,16 14,30" fill="#FF5500"/>
                                <polygon points="8,18 28,8 22,19" fill="#FFFFFF"/>
                            </svg>
                        </span>
                        <span class="brand-logo__text">SPORT<span class="text-accent">KICK</span></span>
                    </a>
                    <p class="footer-tagline">
                        Engineered for athletes who refuse to settle. Tour-grade sports footwear and technical sportswear across 8 competitive disciplines.
                    </p>
                    <div class="footer-badges">
                        <span class="footer-pill">100% Authentic Pro Gear</span>
                        <span class="footer-pill">30-Day Trial Guarantee</span>
                    </div>
                </div>

                <!-- Col 2: Shop by Sport -->
                <div class="footer-col">
                    <h4 class="footer-heading">Shop By Sport</h4>
                    <ul class="footer-links">
                        <li><a href="shop.php?category=running">Running</a></li>
                        <li><a href="shop.php?category=basketball">Basketball</a></li>
                        <li><a href="shop.php?category=football">Football</a></li>
                        <li><a href="shop.php?category=cricket">Cricket</a></li>
                        <li><a href="shop.php?category=tennis">Tennis</a></li>
                        <li><a href="shop.php?category=badminton">Badminton</a></li>
                        <li><a href="shop.php?category=gym-training">Gym &amp; Training</a></li>
                        <li><a href="shop.php?category=hiking">Hiking &amp; Trail</a></li>
                    </ul>
                </div>

                <!-- Col 3: Collections & Account -->
                <div class="footer-col">
                    <h4 class="footer-heading">Athlete Hub</h4>
                    <ul class="footer-links">
                        <li><a href="shop.php?badge=trending">Trending Now</a></li>
                        <li><a href="shop.php?badge=new">New Season Arrivals</a></li>
                        <li><a href="shop.php?badge=deal">Exclusive Locker Deals</a></li>
                        <li><a href="cart.php">Shopping Bag</a></li>
                        <li><a href="wishlist.php">Saved Wishlist</a></li>
                        <li><a href="profile.php">Order History &amp; Profile</a></li>
                    </ul>
                </div>

                <!-- Col 4: Locker Club Newsletter -->
                <div class="footer-col footer-col--newsletter">
                    <h4 class="footer-heading">Join The Locker Club</h4>
                    <p class="footer-text">Get early access to limited colorway drops and an instant <strong>10% OFF</strong> with code <code>KICK10</code>.</p>
                    <form class="newsletter-form" id="newsletterForm">
                        <input type="email" placeholder="Enter your athlete email..." required aria-label="Email for newsletter">
                        <button type="submit" class="btn btn--primary">Join</button>
                    </form>
                    <div class="footer-support">
                        <span>Need sizing help? <strong>support@sportkick.com</strong></span>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> SportKick Sportswear Inc. All rights reserved. Built with PHP, MySQL, HTML5, CSS3 &amp; ES6.</p>
                <div class="footer-payments">
                    <span>VISA</span>
                    <span>MASTERCARD</span>
                    <span>UPI INSTANT</span>
                    <span>NET BANKING</span>
                    <span>COD</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Main Interactive Application Script -->
    <script src="assets/js/main.js"></script>
</body>
</html>

