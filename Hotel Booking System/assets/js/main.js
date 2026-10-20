/**
 * Royal Stay - Luxury Hotel Booking Management System
 * Main Frontend JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sticky Navigation Scroll Effect
    const header = document.querySelector('.site-header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 40) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // 2. Mobile Navigation Toggle
    const navToggle = document.querySelector('.mobile-nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            navLinks.classList.toggle('active');
            const icon = navToggle.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-xmark');
            }
        });

        // Close on clicking outside
        document.addEventListener('click', (e) => {
            if (!navLinks.contains(e.target) && !navToggle.contains(e.target)) {
                navLinks.classList.remove('active');
                const icon = navToggle.querySelector('i');
                if (icon) {
                    icon.classList.add('fa-bars');
                    icon.classList.remove('fa-xmark');
                }
            }
        });
    }

    // 3. User Dropdown Menu
    const userDropdown = document.querySelector('.nav-user-dropdown');
    if (userDropdown) {
        const userBtn = userDropdown.querySelector('.nav-user-btn');
        if (userBtn) {
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });
        }
        document.addEventListener('click', () => {
            userDropdown.classList.remove('active');
        });
    }

    // 4. Automatic Flash Alert Dismissal
    const flashAlerts = document.querySelectorAll('.flash-alert');
    flashAlerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.closest('.flash-container')?.remove(), 500);
        }, 5500);
    });

    // 5. Datepicker Minimum Constraints
    const today = new Date().toISOString().split('T')[0];
    const checkInInputs = document.querySelectorAll('input[type="date"][name="check_in"]');
    const checkOutInputs = document.querySelectorAll('input[type="date"][name="check_out"]');

    checkInInputs.forEach(inInput => {
        inInput.min = today;
        inInput.addEventListener('change', () => {
            const form = inInput.closest('form');
            if (form) {
                const outInput = form.querySelector('input[type="date"][name="check_out"]');
                if (outInput) {
                    // Out must be at least next day
                    const nextDay = new Date(inInput.value);
                    nextDay.setDate(nextDay.getDate() + 1);
                    const minOut = nextDay.toISOString().split('T')[0];
                    outInput.min = minOut;
                    if (outInput.value && outInput.value <= inInput.value) {
                        outInput.value = minOut;
                    }
                }
            }
        });
    });

    checkOutInputs.forEach(outInput => {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        outInput.min = tomorrow.toISOString().split('T')[0];
    });

    // 6. Auth Tabs Switcher (Login / Register)
    const authTabs = document.querySelectorAll('.auth-tab');
    authTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            authTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const targetId = tab.getAttribute('data-target');
            document.querySelectorAll('.auth-pane').forEach(pane => {
                pane.style.display = 'none';
            });
            const targetPane = document.getElementById(targetId);
            if (targetPane) {
                targetPane.style.display = 'block';
            }
        });
    });

    // 7. Password Visibility Toggle
    document.querySelectorAll('.password-toggle-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const input = btn.previousElementSibling;
            if (input && input.type) {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                }
            }
        });
    });
});
