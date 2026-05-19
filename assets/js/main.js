/**
 * Main JavaScript - 125 Gor Kadva Patel Samaj Matrimony
 */

// Sticky Navbar
window.addEventListener('scroll', () => {
    const nav = document.querySelector('.navbar');
    if (nav) nav.classList.toggle('scrolled', window.scrollY > 50);
});

// Floating Hearts Animation
function createHearts() {
    const container = document.querySelector('.hearts-container');
    if (!container) return;
    const hearts = ['❤', '💕', '💗', '💖', '💝'];
    setInterval(() => {
        const heart = document.createElement('span');
        heart.className = 'heart';
        heart.textContent = hearts[Math.floor(Math.random() * hearts.length)];
        heart.style.left = Math.random() * 100 + '%';
        heart.style.fontSize = (Math.random() * 1.2 + 0.6) + 'rem';
        heart.style.animationDuration = (Math.random() * 4 + 4) + 's';
        container.appendChild(heart);
        setTimeout(() => heart.remove(), 8000);
    }, 800);
}

// Scroll Reveal
function initReveal() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } });
    }, { threshold: 0.15 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
}

// Animated Counter
function animateCounters() {
    document.querySelectorAll('.counter').forEach(el => {
        const target = parseInt(el.dataset.target);
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        const timer = setInterval(() => {
            current += step;
            if (current >= target) { el.textContent = target.toLocaleString(); clearInterval(timer); }
            else el.textContent = Math.floor(current).toLocaleString();
        }, 16);
    });
}

// Counter observer
function initCounters() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { animateCounters(); observer.unobserve(e.target); } });
    }, { threshold: 0.3 });
    const section = document.querySelector('.stats-section');
    if (section) observer.observe(section);
}

// Toast Notification
function showToast(message, type = 'success') {
    const container = document.querySelector('.toast-container') || (() => {
        const d = document.createElement('div'); d.className = 'toast-container'; document.body.appendChild(d); return d;
    })();
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-circle', info: 'fa-info-circle' };
    const colors = { success: '#4caf50', error: '#e75480', warning: '#ff9800', info: '#2196f3' };
    const toast = document.createElement('div');
    toast.className = 'custom-toast';
    toast.innerHTML = `<i class="fas ${icons[type]}" style="color:${colors[type]};font-size:1.3rem"></i><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(100%)'; setTimeout(() => toast.remove(), 300); }, 4000);
}

// Notification Bell
function initNotifBell() {
    const bell = document.querySelector('.notif-bell');
    if (!bell) return;
    bell.addEventListener('click', (e) => {
        e.stopPropagation();
        document.querySelector('.notif-dropdown')?.classList.toggle('show');
    });
    document.addEventListener('click', () => document.querySelector('.notif-dropdown')?.classList.remove('show'));
}

// Poll notifications
function pollNotifications() {
    if (!document.querySelector('.notif-bell')) return;
    setInterval(() => {
        fetch('api/get-notifications.php').then(r => r.json()).then(data => {
            const badge = document.querySelector('.notif-badge');
            if (badge) badge.textContent = data.count || '';
            if (data.count == 0 && badge) badge.style.display = 'none';
            else if (badge) badge.style.display = 'flex';
        }).catch(() => {});
    }, 30000);
}

// Image lazy loading
function lazyLoad() {
    const images = document.querySelectorAll('img[data-src]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.src = e.target.dataset.src;
                e.target.removeAttribute('data-src');
                observer.unobserve(e.target);
            }
        });
    });
    images.forEach(img => observer.observe(img));
}

// Image preview on upload
function previewImage(input, previewEl) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const el = document.querySelector(previewEl);
            if (el) { el.src = e.target.result; el.style.display = 'block'; }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Password strength meter
function checkPasswordStrength(password) {
    let score = 0;
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^a-zA-Z\d]/.test(password)) score++;
    return score;
}

function updatePasswordMeter(password) {
    const meter = document.querySelector('.password-strength');
    if (!meter) return;
    const score = checkPasswordStrength(password);
    const colors = ['#e75480', '#ff9800', '#ffc107', '#8bc34a', '#4caf50'];
    const widths = ['20%', '40%', '60%', '80%', '100%'];
    meter.style.width = widths[Math.min(score, 4)];
    meter.style.background = colors[Math.min(score, 4)];
}

// Auto-calculate age
function calcAge(dobInput, ageDisplay) {
    const dob = new Date(dobInput.value);
    if (isNaN(dob)) return;
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
    if (ageDisplay) ageDisplay.textContent = age + ' years';
}

// Smooth scroll (only for real section anchors, not dropdowns/modals)
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', (e) => {
        const href = a.getAttribute('href');
        if (href === '#' || a.dataset.bsToggle) return; // skip Bootstrap triggers
        const t = document.querySelector(href);
        if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
});

// Init
document.addEventListener('DOMContentLoaded', () => {
    createHearts();
    initReveal();
    initCounters();
    initNotifBell();
    pollNotifications();
    lazyLoad();
});
