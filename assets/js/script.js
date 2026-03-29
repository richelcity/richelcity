/* ════════════════════════════════════════════
   RichelCity Enterprise — script.js
   ════════════════════════════════════════════ */

/* ─── NAVBAR: shrink on scroll ─── */
const navbar = document.getElementById('navbar');

if (navbar) {
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 60);
    }, { passive: true });
}


/* ─── MOBILE MENU ─── */
const menuEl     = document.getElementById('menu');
const toggleBtn  = document.getElementById('menuToggle');

function toggleMenu() {
    if (!menuEl) return;
    const isOpen = menuEl.classList.toggle('open');
    menuEl.classList.toggle('active', isOpen); // keep legacy class in sync
    if (toggleBtn) toggleBtn.textContent = isOpen ? '✕' : '☰';
    document.body.style.overflow = isOpen ? 'hidden' : '';
}

// Close on nav link click
document.querySelectorAll('#menu a').forEach(link => {
    link.addEventListener('click', () => {
        if (!menuEl) return;
        menuEl.classList.remove('open', 'active');
        if (toggleBtn) toggleBtn.textContent = '☰';
        document.body.style.overflow = '';
    });
});

// Close on outside click
document.addEventListener('click', e => {
    if (!menuEl || !menuEl.classList.contains('open')) return;
    if (!menuEl.contains(e.target) && e.target !== toggleBtn) {
        menuEl.classList.remove('open', 'active');
        if (toggleBtn) toggleBtn.textContent = '☰';
        document.body.style.overflow = '';
    }
});

document.querySelectorAll('#menu a').forEach(link => {
    link.addEventListener('click', () => {
        document.getElementById('menu').classList.remove('open');
    });
});

// Expose globally (called inline via onclick="toggleMenu()")
window.toggleMenu = toggleMenu;


/* ─── HERO SLIDER ─── */
(function () {
    const slideEls      = document.querySelectorAll('.slide');
    const dotsContainer = document.getElementById('sliderDots');
    const counterEl     = document.getElementById('slideCounter');

    if (!slideEls.length) return;

    const total   = slideEls.length;
    let current   = 0;
    let timer     = null;
    let isPaused  = false;

    // Build dots if container exists and is empty
    if (dotsContainer && !dotsContainer.children.length) {
        slideEls.forEach((_, i) => {
            const d = document.createElement('div');
            d.className = 'dot' + (i === 0 ? ' active' : '');
            d.setAttribute('role', 'button');
            d.setAttribute('aria-label', `Slide ${i + 1}`);
            d.addEventListener('click', () => goTo(i));
            dotsContainer.appendChild(d);
        });
    }

    function updateCounter() {
        if (!counterEl) return;
        counterEl.textContent =
            String(current + 1).padStart(2, '0') + ' / ' +
            String(total).padStart(2, '0');
    }

    function goTo(n) {
        slideEls[current].classList.remove('active');
        if (dotsContainer) dotsContainer.children[current]?.classList.remove('active');

        current = ((n % total) + total) % total;

        slideEls[current].classList.add('active');
        if (dotsContainer) dotsContainer.children[current]?.classList.add('active');
        updateCounter();
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startTimer() {
        clearInterval(timer);
        timer = setInterval(next, 5000);
    }

    startTimer();
    updateCounter();

    // Pause on hover
    const sliderEl = document.getElementById('slider');
    if (sliderEl) {
        sliderEl.addEventListener('mouseenter', () => { isPaused = true;  clearInterval(timer); });
        sliderEl.addEventListener('mouseleave', () => { isPaused = false; startTimer(); });
    }

    // Touch / swipe support
    let touchStartX = 0;
    document.querySelector('.slider')?.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    document.querySelector('.slider')?.addEventListener('touchend', e => {
        const delta = e.changedTouches[0].screenX - touchStartX;
        if (Math.abs(delta) > 50) {
            delta < 0 ? next() : prev();
            if (!isPaused) startTimer();
        }
    }, { passive: true });

    // Keyboard arrows when slider is in view
    document.addEventListener('keydown', e => {
        if (e.key === 'ArrowRight') { next(); startTimer(); }
        if (e.key === 'ArrowLeft')  { prev(); startTimer(); }
    });
})();


document.getElementById('password').addEventListener('keyup', function(e) {
    if (e.getModifierState('CapsLock')) {
        console.log('Caps Lock is ON');
    }
});


/* ─── SCROLL REVEAL (IntersectionObserver) ─── */
(function () {
    // Targets: product cards, category cards, section headers
    const revealTargets = document.querySelectorAll(
        '.product-card, .cat-card, .section-header h2'
    );

    if (!revealTargets.length) return;

    // Set initial hidden state
    revealTargets.forEach((el, i) => {
        el.style.opacity  = '0';
        el.style.transform = 'translateY(28px)';
        el.style.transition =
            `opacity 0.6s ease ${i * 0.06}s, transform 0.6s ease ${i * 0.06}s`;
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.style.opacity   = '1';
            entry.target.style.transform = 'translateY(0)';
            observer.unobserve(entry.target); // fire once
        });
    }, { threshold: 0.12 });

    revealTargets.forEach(el => observer.observe(el));
})();


/* ─── MARQUEE: duplicate content for seamless loop ─── */
(function () {
    const inner = document.getElementById('marqueeInner');
    if (!inner) return;

    // Clone children so the loop is seamless without a gap/jump
    const clone = inner.cloneNode(true);
    clone.setAttribute('aria-hidden', 'true');
    inner.parentElement.appendChild(clone);
})();


