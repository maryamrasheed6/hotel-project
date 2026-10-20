<?php
/**
 * Royal Stay - About Us & Heritage Page
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

$pageTitle = 'Our Heritage & Philosophy';
$activeNav = 'about';

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1>Timeless Elegance & Heritage</h1>
        <p>A sanctuary crafted for refined indulgence, timeless architecture, and unforgettable moments.</p>
        <div class="breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <span>About Us</span>
        </div>
    </div>
</div>

<!-- Story Section -->
<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;" class="about-grid">
            <div>
                <span class="section-badge"><i class="fa-solid fa-crown"></i> The Royal Stay Story</span>
                <h2 style="font-family: 'Playfair Display', serif; font-size: 2.4rem; color: var(--primary); margin: 0.75rem 0 1.5rem 0; line-height: 1.2;">
                    Redefining Luxury Living on the Horizon
                </h2>
                <p style="color: var(--text-muted); font-size: 1.05rem; line-height: 1.8; margin-bottom: 1.5rem;">
                    Founded with a bold vision to marry architectural splendor with intuitive hospitality, Royal Stay has grown to become an international beacon of elegance. Perched atop the dramatic ocean coastline, our sanctuary celebrates panoramic natural beauty alongside world-class refinement.
                </p>
                <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.8; margin-bottom: 2rem;">
                    Every corridor, suite, and garden is thoughtfully curated to inspire peaceful serenity. From bespoke Italian marble finishes and custom Emperor king beds to artisanal dining experiences, we leave no detail untouched.
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div style="background: var(--bg-surface); padding: 1.25rem; border-radius: var(--radius-md); border-left: 3px solid var(--accent);">
                        <h4 style="color: var(--primary); font-size: 1.1rem; margin-bottom: 0.35rem;">Bespoke Butler Care</h4>
                        <p style="color: var(--text-muted); font-size: 0.88rem; margin: 0;">Dedicated 24/7 personal service for every suite and penthouse.</p>
                    </div>
                    <div style="background: var(--bg-surface); padding: 1.25rem; border-radius: var(--radius-md); border-left: 3px solid var(--accent);">
                        <h4 style="color: var(--primary); font-size: 1.1rem; margin-bottom: 0.35rem;">Awarded Gastronomy</h4>
                        <p style="color: var(--text-muted); font-size: 0.88rem; margin: 0;">Four Michelin stars across two signature oceanfront pavilions.</p>
                    </div>
                </div>
            </div>

            <div>
                <img src="<?= base_url('assets/images/room_ocean_villa.jpg') ?>" alt="Royal Stay Architecture" style="width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); object-fit: cover; height: 480px;">
            </div>
        </div>
    </div>
</section>

<!-- Luxury Pillars & Statistics -->
<section style="background: var(--primary); color: #ffffff; padding: 5rem 0; position: relative;">
    <div class="container">
        <div style="text-align: center; max-width: 700px; margin: 0 auto 3.5rem auto;">
            <span class="section-badge" style="background: rgba(217, 164, 65, 0.2); color: var(--accent);"><i class="fa-solid fa-award"></i> Distinction</span>
            <h2 style="font-family: 'Playfair Display', serif; font-size: 2.2rem; color: #ffffff; margin-top: 0.5rem;">
                Crafted for Discerning Travelers
            </h2>
            <p style="color: rgba(255, 255, 255, 0.8); font-size: 1rem;">
                Our commitment to flawless service has earned global recognition as an elite destination.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; text-align: center;">
            <div style="padding: 1.5rem; background: rgba(255, 255, 255, 0.04); border-radius: var(--radius-md); border: 1px solid rgba(255, 255, 255, 0.08); transition: var(--transition);">
                <div style="font-size: 2.8rem; font-weight: 700; color: var(--accent); margin-bottom: 0.35rem;">25+</div>
                <h4 style="font-size: 1.1rem; color: #ffffff; margin-bottom: 0.35rem;">Years of Experience</h4>
                <p style="color: rgba(255, 255, 255, 0.65); font-size: 0.85rem; margin: 0;">Over two decades of luxury hospitality and timeless heritage.</p>
            </div>

            <div style="padding: 1.5rem; background: rgba(255, 255, 255, 0.04); border-radius: var(--radius-md); border: 1px solid rgba(255, 255, 255, 0.08); transition: var(--transition);">
                <div style="font-size: 2.8rem; font-weight: 700; color: var(--accent); margin-bottom: 0.35rem;">120+</div>
                <h4 style="font-size: 1.1rem; color: #ffffff; margin-bottom: 0.35rem;">Curated Suites</h4>
                <p style="color: rgba(255, 255, 255, 0.65); font-size: 0.85rem; margin: 0;">Each boasting panoramic coastline views and private terraces.</p>
            </div>

            <div style="padding: 1.5rem; background: rgba(255, 255, 255, 0.04); border-radius: var(--radius-md); border: 1px solid rgba(255, 255, 255, 0.08); transition: var(--transition);">
                <div style="font-size: 2.8rem; font-weight: 700; color: var(--accent); margin-bottom: 0.35rem;">99.8%</div>
                <h4 style="font-size: 1.1rem; color: #ffffff; margin-bottom: 0.35rem;">Guest Satisfaction</h4>
                <p style="color: rgba(255, 255, 255, 0.65); font-size: 0.85rem; margin: 0;">Rated 5 stars by thousands of global travelers and dignitaries.</p>
            </div>

            <div style="padding: 1.5rem; background: rgba(255, 255, 255, 0.04); border-radius: var(--radius-md); border: 1px solid rgba(255, 255, 255, 0.08); transition: var(--transition);">
                <div style="font-size: 2.8rem; font-weight: 700; color: var(--accent); margin-bottom: 0.35rem;">18+</div>
                <h4 style="font-size: 1.1rem; color: #ffffff; margin-bottom: 0.35rem;">World Awards</h4>
                <p style="color: rgba(255, 255, 255, 0.65); font-size: 0.85rem; margin: 0;">Recognized by Forbes Travel Guide and Condé Nast Traveler.</p>
            </div>

            <div style="padding: 1.5rem; background: rgba(255, 255, 255, 0.04); border-radius: var(--radius-md); border: 1px solid rgba(255, 255, 255, 0.08); transition: var(--transition);">
                <div style="font-size: 2.8rem; font-weight: 700; color: var(--accent); margin-bottom: 0.35rem;">24/7</div>
                <h4 style="font-size: 1.1rem; color: #ffffff; margin-bottom: 0.35rem;">Private Concierge</h4>
                <p style="color: rgba(255, 255, 255, 0.65); font-size: 0.85rem; margin: 0;">Seamless helicopter transfers, yacht bookings, and private dining.</p>
            </div>
        </div>
    </div>
</section>

<!-- Amenities Gallery Grid -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge"><i class="fa-solid fa-gem"></i> Resort Amenities</span>
            <h2 class="section-title">An Unsurpassed Living Experience</h2>
            <p class="section-subtitle">
                Immerse yourself in elevated amenities designed to revitalize your mind, body, and soul.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
            <div style="background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 2rem; box-shadow: var(--shadow-sm); transition: var(--transition);">
                <div style="width: 54px; height: 54px; border-radius: 12px; background: rgba(6, 59, 62, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-water-ladder"></i>
                </div>
                <h4 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--primary); margin-bottom: 0.5rem;">Infinity Horizon Pool</h4>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin: 0;">Temperature-controlled heated pool with private sunken cabanas, daybeds, and poolside cocktail service.</p>
            </div>

            <div style="background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 2rem; box-shadow: var(--shadow-sm); transition: var(--transition);">
                <div style="width: 54px; height: 54px; border-radius: 12px; background: rgba(217, 164, 65, 0.15); color: #B45309; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-spa"></i>
                </div>
                <h4 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--primary); margin-bottom: 0.5rem;">The Lotus Spa & Wellness</h4>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin: 0;">Ancient Ayurvedic therapies, Himalayan salt caves, hydrotherapy circuits, and customized organic facials.</p>
            </div>

            <div style="background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 2rem; box-shadow: var(--shadow-sm); transition: var(--transition);">
                <div style="width: 54px; height: 54px; border-radius: 12px; background: rgba(6, 59, 62, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <h4 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--primary); margin-bottom: 0.5rem;">Michelin Gastronomy</h4>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin: 0;">Farm-to-table culinary masterpieces curated by world-renowned chefs with rare vintage cellar pairings.</p>
            </div>

            <div style="background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--border); padding: 2rem; box-shadow: var(--shadow-sm); transition: var(--transition);">
                <div style="width: 54px; height: 54px; border-radius: 12px; background: rgba(217, 164, 65, 0.15); color: #B45309; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-ship"></i>
                </div>
                <h4 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--primary); margin-bottom: 0.5rem;">Private Yacht Charters</h4>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin: 0;">Explore secluded coves and sunset bays aboard our private 80-foot luxury catamaran fleet.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section style="background: linear-gradient(135deg, #063B3E 0%, #0F2D30 100%); color: #ffffff; padding: 4.5rem 0; text-align: center;">
    <div class="container" style="max-width: 750px;">
        <h2 style="font-family: 'Playfair Display', serif; font-size: 2.2rem; color: #ffffff; margin-bottom: 1rem;">
            Your Royal Sanctuary Awaits
        </h2>
        <p style="color: rgba(255, 255, 255, 0.85); font-size: 1.05rem; margin-bottom: 2rem;">
            Whether celebrating an intimate milestone or seeking an unforgettable escape, discover our collection of private villas and penthouses.
        </p>
        <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
            <a href="<?= base_url('rooms.php') ?>" class="btn btn-gold">
                <i class="fa-solid fa-calendar-check"></i> Reserve a Suite
            </a>
            <a href="<?= base_url('contact.php') ?>" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #ffffff;">
                <i class="fa-solid fa-envelope"></i> Contact Concierge
            </a>
        </div>
    </div>
</section>

<style>
@media (max-width: 900px) {
    .about-grid {
        grid-template-columns: 1fr !important;
        gap: 2.5rem !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
