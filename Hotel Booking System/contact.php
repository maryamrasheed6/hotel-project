<?php
/**
 * Royal Stay - Luxury Concierge Contact & Location Page
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

$pageTitle = 'Contact Concierge & Inquiries';
$activeNav = 'contact';

$currentUser = get_logged_in_user();
$prefillName = $currentUser ? $currentUser['name'] : '';
$prefillEmail = $currentUser ? $currentUser['email'] : '';

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1>Connect with Concierge</h1>
        <p>Our dedicated hospitality team is at your beck and call 24 hours a day, 7 days a week.</p>
        <div class="breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <span>Contact</span>
        </div>
    </div>
</div>

<div class="section">
    <div class="container">
        
        <!-- Contact Cards Row -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.75rem; margin-bottom: 3.5rem;">
            
            <div style="background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 2rem; box-shadow: var(--shadow-sm); text-align: center;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(6, 59, 62, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1.25rem auto;">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <h4 style="font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--primary); margin-bottom: 0.35rem;">Resort Address</h4>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin: 0;">
                    <?= e(get_setting('hotel_address', '100 Royal Palm Boulevard, Marina Bay, Suite 800')) ?>
                </p>
            </div>

            <div style="background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 2rem; box-shadow: var(--shadow-sm); text-align: center;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(217, 164, 65, 0.15); color: #B45309; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1.25rem auto;">
                    <i class="fa-solid fa-phone"></i>
                </div>
                <h4 style="font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--primary); margin-bottom: 0.35rem;">Direct Concierge</h4>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin: 0;">
                    <?= e(get_setting('contact_phone', '+1 (800) 789-7829')) ?><br>
                    <span style="font-size: 0.8rem; color: #10B981;"><i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i> 24/7 Butler Assistance</span>
                </p>
            </div>

            <div style="background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 2rem; box-shadow: var(--shadow-sm); text-align: center;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(6, 59, 62, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1.25rem auto;">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <h4 style="font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--primary); margin-bottom: 0.35rem;">VIP Inquiries</h4>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin: 0;">
                    <?= e(get_setting('contact_email', 'concierge@royalstay.com')) ?><br>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Responses within 2 hours</span>
                </p>
            </div>

            <div style="background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 2rem; box-shadow: var(--shadow-sm); text-align: center;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(217, 164, 65, 0.15); color: #B45309; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1.25rem auto;">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <h4 style="font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--primary); margin-bottom: 0.35rem;">Arrival Hours</h4>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin: 0;">
                    Check-in: <?= e(get_setting('default_checkin_time', '15:00')) ?><br>
                    Check-out: <?= e(get_setting('default_checkout_time', '11:00')) ?>
                </p>
            </div>

        </div>

        <!-- Main Form & Map Row -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3.5rem; align-items: start;" class="contact-grid">
            
            <!-- Message Form -->
            <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-md); padding: 2.5rem;">
                <span class="section-badge"><i class="fa-solid fa-paper-plane"></i> Send a Message</span>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.75rem; color: var(--primary); margin: 0.5rem 0 1rem 0;">
                    Bespoke Requests & Questions
                </h3>
                <p style="color: var(--text-muted); font-size: 0.92rem; margin-bottom: 2rem;">
                    Whether requesting a private yacht transfer, planning an intimate celebration, or reserving our presidential suites, our team will assist you.
                </p>

                <form action="<?= base_url('actions/submit-contact.php') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label" for="contactName">Your Full Name *</label>
                        <input type="text" name="name" id="contactName" class="form-control" value="<?= e($prefillName) ?>" placeholder="e.g. Eleanor Vance" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contactEmail">Email Address *</label>
                        <input type="email" name="email" id="contactEmail" class="form-control" value="<?= e($prefillEmail) ?>" placeholder="e.g. eleanor@example.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contactSubject">Inquiry Nature / Subject *</label>
                        <select name="subject" id="contactSubject" class="form-control" required>
                            <option value="">-- Please Select Topic --</option>
                            <option value="General Concierge Inquiry">General Concierge Inquiry</option>
                            <option value="Suite Booking & Special Requests">Suite Booking & Special Requests</option>
                            <option value="Private Dining & Michelin Tasting">Private Dining & Michelin Tasting</option>
                            <option value="Wedding & Gala Event Booking">Wedding & Gala Event Booking</option>
                            <option value="Helicopter / Airport Chauffeur Transfer">Helicopter / Airport Chauffeur Transfer</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contactMessage">Your Message / Requirements *</label>
                        <textarea name="message" id="contactMessage" class="form-control" rows="5" placeholder="Share your dates, guest count, and personalized requests..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-gold btn-block" style="padding: 0.9rem; font-size: 1rem;">
                        <i class="fa-solid fa-paper-plane"></i> Transmit Inquiry to Concierge
                    </button>
                </form>
            </div>

            <!-- Map Mockup & Resort Coordinates -->
            <div>
                <div style="background: linear-gradient(135deg, #063B3E 0%, #0F2D30 100%); color: #ffffff; border-radius: var(--radius-lg); padding: 2.5rem; box-shadow: var(--shadow-md); margin-bottom: 2rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                        <i class="fa-solid fa-compass" style="color: var(--accent); font-size: 1.5rem;"></i>
                        <h4 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; color: #ffffff; margin: 0;">Prime Oceanfront Enclave</h4>
                    </div>
                    <p style="color: rgba(255, 255, 255, 0.85); font-size: 0.95rem; line-height: 1.7; margin-bottom: 1.5rem;">
                        Royal Stay is nestled 20 minutes from the international airport along the secluded coastline. Valet parking, private dock mooring, and rooftop helipad coordinates are available upon booking.
                    </p>
                    
                    <div style="background: rgba(255, 255, 255, 0.08); border-radius: var(--radius-md); padding: 1.25rem; border: 1px solid rgba(255, 255, 255, 0.12);">
                        <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--accent); letter-spacing: 1px; font-weight: 600;">GPS Coordinates</div>
                        <div style="font-family: monospace; font-size: 1.1rem; color: #ffffff; margin-top: 0.25rem;">24° 42' 19.4" N &bull; 55° 18' 33.1" E</div>
                    </div>
                </div>

                <!-- FAQ Accordion -->
                <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 2rem;">
                    <h4 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; color: var(--primary); margin-bottom: 1.25rem;">
                        Frequently Asked Questions
                    </h4>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <details style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.85rem 1.25rem; cursor: pointer;">
                            <summary style="font-weight: 600; color: var(--primary); outline: none;">What is your cancellation policy?</summary>
                            <p style="color: var(--text-muted); font-size: 0.88rem; margin: 0.5rem 0 0 0; line-height: 1.6;">Reservations may be cancelled free of charge up to 48 hours prior to check-in date via your guest portal.</p>
                        </details>

                        <details style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.85rem 1.25rem; cursor: pointer;">
                            <summary style="font-weight: 600; color: var(--primary); outline: none;">Do you provide complimentary airport transfers?</summary>
                            <p style="color: var(--text-muted); font-size: 0.88rem; margin: 0.5rem 0 0 0; line-height: 1.6;">Guests residing in our Presidential Penthouse and Oceanfront Villas enjoy complimentary private limousine or helicopter transfer.</p>
                        </details>

                        <details style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.85rem 1.25rem; cursor: pointer;">
                            <summary style="font-weight: 600; color: var(--primary); outline: none;">Are pets permitted on the premises?</summary>
                            <p style="color: var(--text-muted); font-size: 0.88rem; margin: 0.5rem 0 0 0; line-height: 1.6;">We welcome small luxury pets in our private garden bungalows with prior arrangement with the concierge team.</p>
                        </details>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<style>
@media (max-width: 900px) {
    .contact-grid {
        grid-template-columns: 1fr !important;
        gap: 2.5rem !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
