    </main>

    <!-- Luxury Footer -->
    <footer class="site-footer">
        <div class="container footer-top">
            <div class="footer-grid">
                
                <!-- Brand Summary -->
                <div class="footer-brand">
                    <a href="<?= base_url() ?>" class="brand-logo">
                        <div class="logo-icon">
                            <i class="fa-solid fa-crown"></i>
                        </div>
                        <div class="logo-text">
                            <span class="logo-title" style="color: #ffffff;">Royal Stay</span>
                            <span class="logo-tagline">Luxury Resort & Suites</span>
                        </div>
                    </a>
                    <p class="footer-desc">
                        A sanctuary of unrivaled sophistication, timeless elegance, and heartfelt hospitality. Experience the pinnacle of luxury living on the pristine coastline.
                    </p>
                    <div class="social-links">
                        <a href="<?= e(get_setting('facebook_url', '#')) ?>" class="social-btn" aria-label="Facebook" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="<?= e(get_setting('instagram_url', '#')) ?>" class="social-btn" aria-label="Instagram" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i></a>
                        <a href="<?= e(get_setting('twitter_url', '#')) ?>" class="social-btn" aria-label="Twitter / X" target="_blank" rel="noopener"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="<?= e(get_setting('linkedin_url', '#')) ?>" class="social-btn" aria-label="LinkedIn" target="_blank" rel="noopener"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="footer-title">Explore</h4>
                    <div class="footer-links">
                        <a href="<?= base_url() ?>" class="footer-link"><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i> Home</a>
                        <a href="<?= base_url('rooms.php') ?>" class="footer-link"><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i> Rooms & Suites</a>
                        <a href="<?= base_url('about.php') ?>" class="footer-link"><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i> About Royal Stay</a>
                        <a href="<?= base_url('reviews.php') ?>" class="footer-link"><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i> Guest Reviews</a>
                        <a href="<?= base_url('contact.php') ?>" class="footer-link"><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i> Contact Concierge</a>
                    </div>
                </div>

                <!-- Guest Services -->
                <div>
                    <h4 class="footer-title">Guest Portal</h4>
                    <div class="footer-links">
                        <a href="<?= base_url('auth.php') ?>" class="footer-link"><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i> Customer Login</a>
                        <a href="<?= base_url('my-bookings.php') ?>" class="footer-link"><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i> My Bookings</a>
                        <a href="<?= base_url('admin/login.php') ?>" class="footer-link"><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i> Staff Administration</a>
                    </div>
                </div>

                <!-- Contact & Location -->
                <div>
                    <h4 class="footer-title">Concierge</h4>
                    <div class="footer-contact-list">
                        <div class="footer-contact-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <span><?= e(get_setting('hotel_address', '100 Royal Palm Boulevard, Marina Bay')) ?></span>
                        </div>
                        <div class="footer-contact-item">
                            <i class="fa-solid fa-phone"></i>
                            <span><?= e(get_setting('contact_phone', '+1 (800) 789-7829')) ?></span>
                        </div>
                        <div class="footer-contact-item">
                            <i class="fa-solid fa-envelope"></i>
                            <span><?= e(get_setting('contact_email', 'concierge@royalstay.com')) ?></span>
                        </div>
                        <div class="footer-contact-item">
                            <i class="fa-solid fa-clock"></i>
                            <span>Check-in: <?= e(get_setting('default_checkin_time', '15:00')) ?> | Check-out: <?= e(get_setting('default_checkout_time', '11:00')) ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="container footer-bottom">
            <div>
                &copy; <?= date('Y') ?> <?= e(get_setting('hotel_name', 'Royal Stay')) ?>. All Rights Reserved. Luxury Hospitality Redefined.
            </div>
            <div style="display: flex; gap: 1.5rem;">
                <a href="<?= base_url('about.php') ?>" style="color: #94a3b8;">Privacy Policy</a>
                <a href="<?= base_url('about.php') ?>" style="color: #94a3b8;">Terms of Service</a>
            </div>
        </div>
    </footer>

    <!-- Core Scripts -->
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <script src="<?= base_url('assets/js/booking.js') ?>"></script>
</body>
</html>
