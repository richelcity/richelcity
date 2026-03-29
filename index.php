<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RichelCity Enterprise</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <address>P.O.Box 253, Tarkwa - Western Region, Ghana Email: richelcity@gmail.com/ +233542848067</address>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* ─── RESET & BASE ─── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --ink:      #0e0b08;
            --cream:    #f5f0e8;
            --warm:     #e8dcc8;
            --gold:     #c9a84c;
            --rust:     #b5451b;
            --muted:    #7a7065;
            --serif:    'Cormorant Garamond', Georgia, serif;
            --display:  'Bebas Neue', sans-serif;
            --body:     'DM Sans', sans-serif;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--cream);
            color: var(--ink);
            font-family: var(--body);
            font-weight: 300;
            overflow-x: hidden;
        }

        /* ─── NOISE TEXTURE OVERLAY ─── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 1000;
        }

        /* ─── NAVBAR ─── */
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 900;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.2rem 3rem;
            background: rgba(245, 240, 232, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(201, 168, 76, 0.25);
            transition: padding 0.4s ease;
        }

        .navbar.scrolled { padding: 0.8rem 3rem; }

        .logo {
            height: 60px;
            width: auto;
            filter: drop-shadow(0 1px 3px rgba(0,0,0,0.15));
        }

        nav { display: flex; align-items: center; gap: 2.5rem; }

        nav a {
            font-family: var(--body);
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--ink);
            text-decoration: none;
            position: relative;
            padding-bottom: 3px;
        }

        nav a::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 0; height: 1px;
            background: var(--gold);
            transition: width 0.3s ease;
        }

        nav a:hover::after { width: 100%; }

        .menu-toggle {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--ink);
        }

        /* ─── HERO SLIDER ─── */
        .slider {
            position: relative;
            height: 100vh;
            overflow: hidden;
        }

        .slides { height: 100%; }

        .slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .slide.active { opacity: 1; }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scale(1.05);
            transition: transform 6s ease;
        }

        .slide.active img { transform: scale(1); }

        /* Dark gradient */
        .slide::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(14,11,8,0.15) 0%,
                rgba(14,11,8,0.05) 40%,
                rgba(14,11,8,0.55) 100%
            );
        }

        .caption {
            position: absolute;
            bottom: 12%;
            left: 8%;
            z-index: 10;
            color: var(--cream);
        }

        .caption-label {
            display: block;
            font-family: var(--body);
            font-size: 0.72rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 0.75rem;
        }

        .caption-title {
            font-family: var(--display);
            font-size: clamp(3.5rem, 8vw, 7rem);
            line-height: 0.95;
            letter-spacing: 0.02em;
            display: block;
        }

        /* Slider dots */
        .slider-dots {
            position: absolute;
            bottom: 2.5rem;
            right: 3rem;
            z-index: 20;
            display: flex;
            gap: 0.5rem;
        }

        .dot {
            width: 28px; height: 2px;
            background: rgba(245,240,232,0.4);
            cursor: pointer;
            transition: background 0.3s ease, width 0.3s ease;
        }

        .dot.active { background: var(--gold); width: 48px; }

        /* Slide counter */
        .slide-counter {
            position: absolute;
            bottom: 2rem;
            left: 8%;
            z-index: 20;
            font-family: var(--serif);
            font-style: italic;
            font-size: 0.85rem;
            color: rgba(245,240,232,0.6);
            letter-spacing: 0.1em;
        }

        /* ─── SECTION SHARED ─── */
        section { padding: 6rem 3rem; }

        .section-header {
            display: flex;
            align-items: baseline;
            gap: 1.5rem;
            margin-bottom: 3.5rem;
        }

        .section-header h2 {
            font-family: var(--display);
            font-size: clamp(2.5rem, 5vw, 4rem);
            letter-spacing: 0.04em;
            line-height: 1;
        }

        .section-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, var(--gold), transparent);
            max-width: 300px;
        }

        .section-sub {
            font-family: var(--serif);
            font-style: italic;
            font-size: 1rem;
            color: var(--muted);
        }

        /* ─── CATEGORIES ─── */
        #categories { background: var(--ink); color: var(--cream); }

        #categories .section-header h2 { color: var(--cream); }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1px;
        }

        .cat-card {
            padding: 2.5rem 1.5rem;
            background: rgba(245,240,232,0.04);
            border: 1px solid rgba(201,168,76,0.12);
            font-family: var(--serif);
            font-size: 1.25rem;
            font-style: italic;
            color: var(--cream);
            cursor: pointer;
            transition: background 0.3s ease, color 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .cat-card::before {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 0; height: 2px;
            background: var(--gold);
            transition: width 0.4s ease;
        }

        .cat-card:hover {
            background: rgba(201,168,76,0.08);
            color: var(--gold);
        }

        .cat-card:hover::before { width: 100%; }

        .cat-number {
            display: block;
            font-family: var(--body);
            font-size: 0.65rem;
            letter-spacing: 0.2em;
            color: var(--muted);
            margin-bottom: 0.75rem;
            font-style: normal;
        }

        /* ─── GALLERY ─── */
        #gallery { background: var(--cream); }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }

        /* Feature the first card */
        .product-card:first-child {
            grid-column: span 2;
            grid-row: span 2;
        }

        .product-card {
            position: relative;
            overflow: hidden;
            background: var(--warm);
            cursor: pointer;
        }

        .product-card-img {
            width: 100%;
            aspect-ratio: 3/4;
            object-fit: cover;
            display: block;
            transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .product-card:first-child .product-card-img {
            aspect-ratio: auto;
            height: 100%;
        }

        .product-card:hover .product-card-img { transform: scale(1.06); }

        .product-info {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            padding: 1.5rem;
            background: linear-gradient(to top, rgba(14,11,8,0.75) 0%, transparent 100%);
            color: var(--cream);
            transform: translateY(4px);
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-info { transform: translateY(0); }

        .product-name {
            font-family: var(--serif);
            font-size: 1.05rem;
            font-style: italic;
            display: block;
            margin-bottom: 0.25rem;
        }

        .product-price {
            font-family: var(--body);
            font-size: 0.78rem;
            letter-spacing: 0.12em;
            color: var(--gold);
        }

        /* ─── MARQUEE BAND ─── */
        .marquee-band {
            background: var(--rust);
            padding: 1rem 0;
            overflow: hidden;
            white-space: nowrap;
        }

        .marquee-inner {
            display: inline-flex;
            gap: 3rem;
            animation: marquee 20s linear infinite;
        }

        .marquee-item {
            font-family: var(--display);
            font-size: 1.1rem;
            letter-spacing: 0.2em;
            color: var(--cream);
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .marquee-item::after {
            content: '✦';
            color: var(--gold);
            font-size: 0.6rem;
        }

        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* ─── FOOTER ─── */
        footer {
            background: var(--ink);
            color: var(--muted);
            text-align: center;
            padding: 3rem;
            font-size: 0.78rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        footer span { color: var(--gold); }

        /* ─── MOBILE ─── */
        @media (max-width: 768px) {
            .navbar { padding: 1rem 1.5rem; }

            nav {
                display: none;
                position: fixed;
                top: 0; right: 0; bottom: 0;
                width: 75vw;
                background: var(--ink);
                flex-direction: column;
                justify-content: center;
                align-items: flex-start;
                padding: 3rem 2.5rem;
                gap: 2rem;
                z-index: 950;
            }

            nav.open { display: flex; }

            nav a { color: var(--cream); font-size: 0.9rem; }

            .menu-toggle { display: block; z-index: 960; }

            section { padding: 4rem 1.5rem; }

            .caption { left: 5%; }

            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }

            .product-card:first-child {
                grid-column: span 2;
                grid-row: span 1;
            }

            .categories-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body>

<!-- ─── NAVBAR ─── -->
<header class="navbar" id="navbar">
    <a href="index.php">
        <img src="assets/images/logo.png" class="logo" alt="RichelCity Logo">
    </a>

    <div class="menu-toggle" id="menuToggle" onclick="toggleMenu()">☰</div>

    <nav id="menu">
        <a href="#">Home</a>
        <a href="#categories">Categories</a>
        <a href="#gallery">Gallery</a>
    </nav>
</header>


<!-- ─── HERO SLIDER ─── -->
<section class="slider" id="slider">
    <div class="slides">
        <?php
        $res = $conn->query("SELECT * FROM sliders WHERE status='active' ORDER BY display_order ASC");
        $slides = [];
        while($row = $res->fetch_assoc()) $slides[] = $row;
        $total = count($slides);
        foreach($slides as $i => $row):
        ?>
        <div class="slide <?php echo $i === 0 ? 'active' : ''; ?>">
            <img src="assets/images/<?php echo htmlspecialchars($row['image']); ?>" alt="">
            <div class="caption">
                <span class="caption-label">New Collection</span>
                <span class="caption-title"><?php echo htmlspecialchars($row['caption']); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="slider-dots" id="sliderDots"></div>
    <div class="slide-counter" id="slideCounter">01 / <?php echo str_pad($total, 2, '0', STR_PAD_LEFT); ?></div>
</section>


<!-- ─── MARQUEE ─── -->
<div class="marquee-band">
    <div class="marquee-inner" id="marqueeInner">
        <?php
        $tags = ['New Arrivals', 'Premium Quality', 'Made in Ghana', 'Fashion Forward', 'RichelCity', 'Exclusive Styles', 'New Arrivals', 'Premium Quality', 'Made in Ghana', 'Fashion Forward', 'RichelCity', 'Exclusive Styles'];
        foreach($tags as $t) echo "<span class='marquee-item'>$t</span>";
        ?>
    </div>
</div>


<!-- ─── CATEGORIES ─── -->
<section id="categories">
    <div class="section-header">
        <h2>Categories</h2>
        <div class="section-line"></div>
        <span class="section-sub">Explore the collection</span>
    </div>
    <div class="categories-grid">
        <?php
        $res = $conn->query("SELECT * FROM categories WHERE status='active'");
        $idx = 1;
        while($row = $res->fetch_assoc()):
        ?>
        <div class="cat-card">
            <span class="cat-number"><?php echo str_pad($idx++, 2, '0', STR_PAD_LEFT); ?></span>
            <?php echo htmlspecialchars($row['name']); ?>
        </div>
        <?php endwhile; ?>
    </div>
</section>


<!-- ─── GALLERY ─── -->
<section id="gallery">
    <div class="section-header">
        <h2>Fashion Gallery</h2>
        <div class="section-line"></div>
        <span class="section-sub">Latest pieces</span>
    </div>
    <div class="gallery-grid">
        <?php
        $res = $conn->query("SELECT * FROM products WHERE status='available' ORDER BY id DESC LIMIT 8");
        while($row = $res->fetch_assoc()):
        ?>
        <div class="product-card">
            <img class="product-card-img"
                 src="assets/images/<?php echo htmlspecialchars($row['image']); ?>"
                 alt="<?php echo htmlspecialchars($row['name']); ?>">
            <div class="product-info">
                <span class="product-name"><?php echo htmlspecialchars($row['name']); ?></span>
                <span class="product-price">GHS <?php echo number_format($row['price'], 2); ?></span>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</section>


<!-- ─── FOOTER ─── -->

<footer>
    <p>P.O.Box 253, Tarkwa - Western Region, Ghana</p>
    <p>Email: richelcity@gmail.com | Tel: +233542848067</p>
    © <?php echo date('Y'); ?> <span>RichelCity Enterprise</span>
</footer>

<script>
    /* ── Navbar shrink on scroll ── */
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 60);
    });

    /* ── Mobile menu ── */
    function toggleMenu() {
        document.getElementById('menu').classList.toggle('open');
    }

    /* ── Slider ── */
    const slides = document.querySelectorAll('.slide');
    const dotsContainer = document.getElementById('sliderDots');
    const counter = document.getElementById('slideCounter');
    let current = 0;
    const total = slides.length;

    // Build dots
    slides.forEach((_, i) => {
        const d = document.createElement('div');
        d.className = 'dot' + (i === 0 ? ' active' : '');
        d.onclick = () => goTo(i);
        dotsContainer.appendChild(d);
    });

    function goTo(n) {
        slides[current].classList.remove('active');
        dotsContainer.children[current].classList.remove('active');
        current = (n + total) % total;
        slides[current].classList.add('active');
        dotsContainer.children[current].classList.add('active');
        counter.textContent =
            String(current + 1).padStart(2, '0') + ' / ' +
            String(total).padStart(2, '0');
    }

    // Auto-advance every 5s
    setInterval(() => goTo(current + 1), 5000);

    /* ── Scroll-reveal for cards ── */
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.style.opacity = '1';
                e.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.product-card, .cat-card').forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(24px)';
        el.style.transition = `opacity 0.6s ease ${i * 0.07}s, transform 0.6s ease ${i * 0.07}s`;
        observer.observe(el);
    });
</script>

</body>
</html>