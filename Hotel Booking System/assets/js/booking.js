/**
 * Royal Stay - Luxury Hotel Booking Management System
 * Interactive Booking Logic & Gallery Functions
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Gallery Thumbnail Switcher
    const mainImg = document.getElementById('mainGalleryImage');
    const thumbs = document.querySelectorAll('.gallery-thumb');

    if (mainImg && thumbs.length > 0) {
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', () => {
                const src = thumb.getAttribute('data-full');
                if (src) {
                    mainImg.style.opacity = '0.4';
                    setTimeout(() => {
                        mainImg.src = src;
                        mainImg.style.opacity = '1';
                    }, 150);

                    thumbs.forEach(t => t.classList.remove('active'));
                    thumb.classList.add('active');
                }
            });
        });
    }

    // 2. Real-time Stay & Cost Calculation on Room Details & Booking Form
    const checkInField = document.getElementById('calcCheckIn') || document.querySelector('input[name="check_in"]');
    const checkOutField = document.getElementById('calcCheckOut') || document.querySelector('input[name="check_out"]');
    const basePriceAttr = document.getElementById('bookingCalculator')?.getAttribute('data-price') || 0;
    const basePrice = parseFloat(basePriceAttr);
    const taxRate = parseFloat(document.getElementById('bookingCalculator')?.getAttribute('data-tax') || 10);

    function updateSummary() {
        if (!checkInField || !checkOutField || isNaN(basePrice) || basePrice <= 0) return;

        const checkInVal = checkInField.value;
        const checkOutVal = checkOutField.value;

        if (!checkInVal || !checkOutVal) return;

        const inDate = new Date(checkInVal);
        const outDate = new Date(checkOutVal);

        if (outDate > inDate) {
            const diffTime = Math.abs(outDate - inDate);
            const nights = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));
            const subtotal = nights * basePrice;
            const tax = (subtotal * taxRate) / 100;
            const total = subtotal + tax;

            // Update DOM display elements if present
            const nightsEl = document.getElementById('calcNights');
            const subtotalEl = document.getElementById('calcSubtotal');
            const taxEl = document.getElementById('calcTax');
            const totalEl = document.getElementById('calcTotal');

            if (nightsEl) nightsEl.textContent = `${nights} night${nights > 1 ? 's' : ''}`;
            if (subtotalEl) subtotalEl.textContent = '$' + subtotal.toFixed(2);
            if (taxEl) taxEl.textContent = '$' + tax.toFixed(2);
            if (totalEl) totalEl.textContent = '$' + total.toFixed(2);
        }
    }

    if (checkInField && checkOutField) {
        checkInField.addEventListener('change', updateSummary);
        checkOutField.addEventListener('change', updateSummary);
        // Initial run
        updateSummary();
    }
});
