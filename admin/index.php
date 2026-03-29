<?php
include 'admin/config.php';

// Fetch settings
$cfg = [];
$sr  = $conn->query("SELECT setting_key, setting_value FROM settings");
if ($sr) while ($r = $sr->fetch_assoc()) $cfg[$r['setting_key']] = $r['setting_value'];

$siteName  = $cfg['site_name']  ?? 'RichelCity Enterprise';
$sitePhone = $cfg['site_phone'] ?? '';
$siteEmail = $cfg['site_email'] ?? '';
$waPhone   = preg_replace('/\D/', '', $sitePhone);

// Fetch sliders
$sliders = [];
$sr = $conn->query("SELECT * FROM sliders WHERE status='active' ORDER BY display_order ASC");
if ($sr) while ($r = $sr->fetch_assoc()) $sliders[] = $r;

// Fetch categories
$categories = [];
$cr = $conn->query("SELECT * FROM categories WHERE status='active' ORDER BY display_order ASC");
if ($cr) while ($r = $cr->fetch_assoc()) $categories[] = $r;

// Fetch one featured product
$featured = null;
$fr = $conn->query("SELECT * FROM products WHERE status='available' AND featured=1 ORDER BY id DESC LIMIT 1");
if ($fr) $featured = $fr->fetch_assoc();

// Fetch gallery products
$products = [];
$pr = $conn->query("SELECT * FROM products WHERE status='available' ORDER BY id DESC LIMIT 8");
if ($pr) while ($r = $pr->fetch_assoc()) $products[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($siteName) ?></title>
<meta name="description" content="<?= htmlspecialchars($siteName) ?> — Premium African Fashion. Discover curated collections for men, women, and kids.">
<link rel="icon" type="image/png" href="assets/images/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400;1,600&family=Bebas+Neue&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<style>
:root {
    --ink:#0e0b08; --ink2:#1a1510;
    --cream:#f5f0e8; --warm:#ede5d5; --warm2:#e3d8c4;
    --gold:#c9a84c; --gold2:#a8872c;
    --rust:#b5451b;
    --muted:#7a7065; --muted2:#a09890;
    --serif:'Cormorant Garamond',Georgia,serif;
    --display:'Bebas Neue',sans-serif;
    --body:'DM Sans',sans-serif;
    --ease:cubic-bezier(0.4,0,0.2,1);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;font-size:16px;}
body{background:var(--cream);color:var(--ink);font-family:var(--body);font-weight:300;overflow-x:hidden;-webkit-font-smoothing:antialiased;}
img{display:block;max-width:100%;}
a{color:inherit;text-decoration:none;}

/* Noise */
body::before{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");pointer-events:none;z-index:9999;}

/* ── NAVBAR ── */
.nav{position:fixed;top:0;left:0;right:0;z-index:500;display:flex;align-items:center;justify-content:space-between;padding:1.25rem 4rem;transition:padding .4s var(--ease),background .4s ease,box-shadow .4s ease;}
.nav.solid{background:rgba(245,240,232,.96);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-bottom:1px solid rgba(201,168,76,.18);padding:.85rem 4rem;}
.nav-logo{height:42px;width:auto;transition:opacity .2s;}
.nav-logo:hover{opacity:.8;}
.nav-links{display:flex;align-items:center;gap:2.75rem;}
.nav-links a{font-size:.72rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;color:var(--cream);position:relative;padding-bottom:2px;transition:color .25s;}
.nav.solid .nav-links a{color:var(--ink);}
.nav-links a::after{content:'';position:absolute;bottom:0;left:0;width:0;height:1px;background:var(--gold);transition:width .3s var(--ease);}
.nav-links a:hover::after{width:100%;}
.nav-cta{padding:.58rem 1.4rem !important;border:1px solid rgba(245,240,232,.45) !important;border-radius:1px;color:var(--cream) !important;transition:background .25s,border-color .25s,color .25s !important;}
.nav-cta:hover{background:var(--gold) !important;border-color:var(--gold) !important;color:var(--ink) !important;}
.nav-cta::after{display:none !important;}
.nav.solid .nav-cta{border-color:var(--ink) !important;color:var(--ink) !important;}
.nav.solid .nav-cta:hover{background:var(--ink) !important;color:var(--cream) !important;border-color:var(--ink) !important;}
.nav-hbg{display:none;background:none;border:none;cursor:pointer;flex-direction:column;gap:5px;padding:4px;z-index:510;}
.nav-hbg span{display:block;width:24px;height:1.5px;background:var(--cream);transition:background .25s,transform .3s,opacity .3s;}
.nav.solid .nav-hbg span{background:var(--ink);}
.nav-hbg.open span:nth-child(1){transform:translateY(6.5px) rotate(45deg);}
.nav-hbg.open span:nth-child(2){opacity:0;}
.nav-hbg.open span:nth-child(3){transform:translateY(-6.5px) rotate(-45deg);}
.mob-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:503;}
.mob-overlay.open{display:block;}
.mob-menu{display:none;position:fixed;top:0;right:0;bottom:0;width:min(80vw,320px);background:var(--ink);z-index:505;padding:5rem 2.5rem 3rem;flex-direction:column;gap:0;transform:translateX(100%);transition:transform .4s var(--ease);}
.mob-menu.open{transform:translateX(0);display:flex;}
.mob-menu a{display:block;font-size:.8rem;font-weight:500;letter-spacing:.18em;text-transform:uppercase;color:rgba(245,240,232,.5);padding:1.1rem 0;border-bottom:1px solid rgba(255,255,255,.06);transition:color .2s;}
.mob-menu a:hover{color:var(--gold);}

/* ── HERO ── */
.hero{position:relative;height:100svh;min-height:560px;overflow:hidden;background:var(--ink);}
.hero-slides{position:absolute;inset:0;}
.hero-slide{position:absolute;inset:0;opacity:0;transition:opacity 1.4s var(--ease);}
.hero-slide.active{opacity:1;}
.hero-slide img{width:100%;height:100%;object-fit:cover;transform:scale(1.06);transition:transform 7s var(--ease);will-change:transform;}
.hero-slide.active img{transform:scale(1);}
.hero-slide::after{content:'';position:absolute;inset:0;background:linear-gradient(to right,rgba(14,11,8,.65) 0%,rgba(14,11,8,.1) 60%,transparent 100%),linear-gradient(to top,rgba(14,11,8,.5) 0%,transparent 50%);}
.hero-content{position:absolute;bottom:0;left:0;right:0;z-index:10;padding:0 8% 9%;display:flex;flex-direction:column;}
.hero-eyebrow{font-size:.68rem;font-weight:500;letter-spacing:.35em;text-transform:uppercase;color:var(--gold);margin-bottom:1rem;opacity:0;transform:translateY(12px);transition:opacity .8s ease .2s,transform .8s ease .2s;}
.hero-slide.active .hero-eyebrow{opacity:1;transform:translateY(0);}
.hero-title{font-family:var(--display);font-size:clamp(3.5rem,9vw,8rem);line-height:.9;letter-spacing:.02em;color:var(--cream);max-width:700px;opacity:0;transform:translateY(20px);transition:opacity .9s ease .35s,transform .9s ease .35s;}
.hero-slide.active .hero-title{opacity:1;transform:translateY(0);}
.hero-sub{font-family:var(--serif);font-style:italic;font-size:clamp(1rem,2vw,1.3rem);color:rgba(245,240,232,.7);margin-top:1.25rem;max-width:420px;opacity:0;transition:opacity .8s ease .6s;}
.hero-slide.active .hero-sub{opacity:1;}
.hero-actions{display:flex;gap:1rem;margin-top:2.5rem;flex-wrap:wrap;opacity:0;transition:opacity .8s ease .75s;}
.hero-slide.active .hero-actions{opacity:1;}
.btn-primary{display:inline-flex;align-items:center;gap:.6rem;background:var(--gold);color:var(--ink);font-size:.72rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;padding:.9rem 2.2rem;border:1px solid var(--gold);border-radius:1px;transition:background .25s,border-color .25s;white-space:nowrap;}
.btn-primary:hover{background:var(--gold2);border-color:var(--gold2);}
.btn-outline{display:inline-flex;align-items:center;gap:.6rem;background:transparent;color:var(--cream);font-size:.72rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;padding:.9rem 2.2rem;border:1px solid rgba(245,240,232,.4);border-radius:1px;transition:border-color .25s,background .25s;white-space:nowrap;}
.btn-outline:hover{border-color:var(--cream);background:rgba(245,240,232,.08);}
.hero-nav{position:absolute;right:4%;bottom:9%;z-index:20;display:flex;flex-direction:column;gap:.5rem;align-items:flex-end;}
.hero-dot{width:32px;height:2px;background:rgba(245,240,232,.3);cursor:pointer;border:none;transition:background .3s,width .3s;}
.hero-dot.active{background:var(--gold);width:52px;}
.hero-counter{position:absolute;right:4%;top:50%;transform:translateY(-50%);z-index:20;writing-mode:vertical-rl;font-family:var(--serif);font-style:italic;font-size:.78rem;color:rgba(245,240,232,.4);letter-spacing:.1em;}
.scroll-cue{position:absolute;bottom:2.5rem;left:50%;transform:translateX(-50%);z-index:20;display:flex;flex-direction:column;align-items:center;gap:.5rem;opacity:.55;animation:sbounce 2s ease infinite;}
.scroll-cue span{font-size:.58rem;letter-spacing:.2em;text-transform:uppercase;color:var(--cream);}
.scroll-line{width:1px;height:40px;background:linear-gradient(to bottom,var(--cream),transparent);}
@keyframes sbounce{0%,100%{transform:translateX(-50%) translateY(0);}50%{transform:translateX(-50%) translateY(6px);}}

/* ── MARQUEE ── */
.marquee{background:var(--ink2);border-top:1px solid rgba(201,168,76,.12);border-bottom:1px solid rgba(201,168,76,.12);padding:.85rem 0;overflow:hidden;white-space:nowrap;}
.marquee-track{display:inline-flex;align-items:center;animation:mscroll 28s linear infinite;}
.marquee:hover .marquee-track{animation-play-state:paused;}
.marquee-item{display:inline-flex;align-items:center;gap:2.5rem;font-family:var(--display);font-size:.9rem;letter-spacing:.25em;color:rgba(245,240,232,.5);padding:0 2.5rem;}
.marquee-item::after{content:'✦';color:var(--gold);font-size:.45rem;opacity:.6;}
@keyframes mscroll{from{transform:translateX(0);}to{transform:translateX(-50%);}}

/* ── SHARED SECTION ── */
.section{padding:7rem 4rem;}
.section-label{font-size:.64rem;font-weight:500;letter-spacing:.3em;text-transform:uppercase;color:var(--gold);margin-bottom:.75rem;display:flex;align-items:center;gap:1rem;}
.section-label::before{content:'';display:block;width:32px;height:1px;background:var(--gold);flex-shrink:0;}
.section-heading{font-family:var(--display);font-size:clamp(2.5rem,5vw,4.5rem);letter-spacing:.04em;line-height:.95;color:inherit;}
.section-sub{font-family:var(--serif);font-style:italic;font-size:1.05rem;color:var(--muted);margin-top:.75rem;max-width:480px;}

/* ── CATEGORIES ── */
.cats-section{background:var(--ink);color:var(--cream);padding:7rem 4rem;}
.cats-header{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:3.5rem;gap:2rem;flex-wrap:wrap;}
.cats-header .section-sub{color:rgba(245,240,232,.45);}
.cats-all{font-size:.68rem;font-weight:500;letter-spacing:.18em;text-transform:uppercase;color:var(--gold);border-bottom:1px solid rgba(201,168,76,.3);padding-bottom:2px;white-space:nowrap;transition:border-color .2s;flex-shrink:0;}
.cats-all:hover{border-color:var(--gold);}
.cats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.5px;}
.cat-img{position:relative;overflow:hidden;aspect-ratio:3/4;background:var(--ink2);cursor:pointer;}
.cat-img img{width:100%;height:100%;object-fit:cover;transition:transform .7s var(--ease);}
.cat-img:hover img{transform:scale(1.06);}
.cat-img::after{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(14,11,8,.8) 0%,rgba(14,11,8,.1) 60%);transition:opacity .4s;}
.cat-img:hover::after{opacity:.7;}
.cat-plain{position:relative;padding:2.5rem 2rem;background:rgba(245,240,232,.04);border:1px solid rgba(201,168,76,.1);min-height:200px;display:flex;flex-direction:column;justify-content:flex-end;cursor:pointer;overflow:hidden;transition:background .3s;}
.cat-plain:hover{background:rgba(201,168,76,.07);}
.cat-plain::before{content:'';position:absolute;bottom:0;left:0;width:0;height:2px;background:var(--gold);transition:width .4s var(--ease);}
.cat-plain:hover::before{width:100%;}
.cat-body{position:absolute;bottom:0;left:0;right:0;z-index:5;padding:1.5rem 1.75rem;}
.cat-plain .cat-body{position:relative;padding:0;}
.cat-num{font-size:.6rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;color:var(--gold);opacity:.7;margin-bottom:.5rem;}
.cat-name{font-family:var(--serif);font-style:italic;font-size:1.35rem;color:var(--cream);line-height:1.2;transition:color .25s;}
.cat-plain:hover .cat-name{color:var(--gold);}

/* ── FEATURED ── */
.featured{display:grid;grid-template-columns:1fr 1fr;min-height:560px;}
.featured-img{position:relative;overflow:hidden;background:var(--ink2);}
.featured-img img{width:100%;height:100%;object-fit:cover;transition:transform .7s var(--ease);}
.featured:hover .featured-img img{transform:scale(1.03);}
.featured-body{background:var(--warm);display:flex;flex-direction:column;justify-content:center;padding:5rem;}
.featured-tag{font-size:.62rem;font-weight:500;letter-spacing:.3em;text-transform:uppercase;color:var(--rust);margin-bottom:1.5rem;}
.featured-name{font-family:var(--display);font-size:clamp(2.5rem,4vw,4rem);letter-spacing:.03em;line-height:.95;margin-bottom:1.25rem;}
.featured-desc{font-family:var(--serif);font-style:italic;font-size:1.05rem;color:var(--muted);line-height:1.8;max-width:360px;margin-bottom:2.5rem;}
.featured-price{font-family:var(--display);font-size:1.8rem;letter-spacing:.04em;margin-bottom:2rem;}
.featured-price small{font-family:var(--body);font-size:.72rem;font-weight:500;letter-spacing:.15em;text-transform:uppercase;color:var(--muted);vertical-align:middle;margin-right:.4rem;}
.btn-dark{display:inline-flex;align-items:center;gap:.75rem;background:var(--ink);color:var(--cream);font-size:.72rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;padding:1rem 2.5rem;border:1px solid var(--ink);border-radius:1px;transition:background .25s;width:fit-content;}
.btn-dark:hover{background:var(--ink2);}

/* ── GALLERY ── */
.gallery-section{background:var(--cream);padding:7rem 4rem;}
.gallery-header{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:3.5rem;gap:2rem;flex-wrap:wrap;}
.gallery-link{font-size:.68rem;font-weight:500;letter-spacing:.18em;text-transform:uppercase;color:var(--muted);border-bottom:1px solid var(--warm2);padding-bottom:2px;white-space:nowrap;transition:color .2s,border-color .2s;}
.gallery-link:hover{color:var(--ink);border-color:var(--ink);}
.gallery-grid{display:grid;grid-template-columns:repeat(4,1fr);grid-template-rows:auto auto;gap:1.25rem;}
.product-card{position:relative;overflow:hidden;background:var(--warm);cursor:pointer;border-radius:1px;}
.product-card:first-child{grid-column:span 2;grid-row:span 2;}
.product-card-img{width:100%;aspect-ratio:3/4;object-fit:cover;display:block;transition:transform .7s var(--ease);}
.product-card:first-child .product-card-img{aspect-ratio:unset;height:100%;}
.product-card:hover .product-card-img{transform:scale(1.05);}
.product-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(14,11,8,.72) 0%,rgba(14,11,8,.1) 45%,transparent 70%);display:flex;flex-direction:column;justify-content:flex-end;padding:1.5rem 1.25rem 1.25rem;transition:background .3s;}
.product-card:hover .product-overlay{background:linear-gradient(to top,rgba(14,11,8,.82) 0%,rgba(14,11,8,.2) 50%,transparent 70%);}
.product-name{font-family:var(--serif);font-style:italic;font-size:1rem;color:var(--cream);display:block;margin-bottom:.3rem;line-height:1.3;}
.product-card:first-child .product-name{font-size:1.3rem;}
.product-price{font-size:.75rem;font-weight:500;letter-spacing:.12em;color:var(--gold);}
.product-cta{display:inline-block;margin-top:.85rem;font-size:.65rem;font-weight:500;letter-spacing:.18em;text-transform:uppercase;color:var(--cream);border:1px solid rgba(245,240,232,.4);padding:.4rem .9rem;border-radius:1px;opacity:0;transform:translateY(6px);transition:opacity .3s,transform .3s,background .2s;width:fit-content;}
.product-card:hover .product-cta{opacity:1;transform:translateY(0);}
.product-cta:hover{background:rgba(245,240,232,.12);}

/* ── TRUST ── */
.trust-section{background:var(--ink);color:var(--cream);padding:6rem 4rem;}
.trust-inner{display:grid;grid-template-columns:1fr 3fr;gap:5rem;align-items:start;}
.trust-feats{display:grid;grid-template-columns:1fr 1fr;gap:3rem;}
.trust-feat-icon{font-size:1.4rem;margin-bottom:1rem;opacity:.65;}
.trust-feat-title{font-family:var(--display);font-size:1.1rem;letter-spacing:.06em;color:var(--cream);margin-bottom:.5rem;}
.trust-feat-desc{font-family:var(--serif);font-style:italic;font-size:.9rem;color:rgba(245,240,232,.45);line-height:1.7;}

/* ── CONTACT STRIP ── */
.cstrip{background:var(--gold);padding:4rem;display:grid;grid-template-columns:1fr auto;align-items:center;gap:3rem;}
.cstrip h2{font-family:var(--display);font-size:clamp(2rem,4vw,3rem);letter-spacing:.04em;color:var(--ink);line-height:1;margin-bottom:.5rem;}
.cstrip p{font-family:var(--serif);font-style:italic;font-size:1rem;color:rgba(14,11,8,.6);}
.cstrip-acts{display:flex;gap:1rem;flex-wrap:wrap;flex-shrink:0;}
.btn-ink{display:inline-flex;align-items:center;gap:.6rem;background:var(--ink);color:var(--cream);font-size:.72rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;padding:.9rem 2rem;border:1px solid var(--ink);border-radius:1px;transition:background .25s;white-space:nowrap;}
.btn-ink:hover{background:var(--ink2);}
.btn-ghost-ink{display:inline-flex;align-items:center;gap:.6rem;background:transparent;color:var(--ink);font-size:.72rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;padding:.9rem 2rem;border:1px solid rgba(14,11,8,.3);border-radius:1px;transition:border-color .25s,background .25s;white-space:nowrap;}
.btn-ghost-ink:hover{border-color:var(--ink);background:rgba(14,11,8,.06);}

/* ── FOOTER ── */
footer{background:var(--ink2);color:var(--muted);padding:4rem 4rem 2.5rem;}
.footer-top{display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:4rem;padding-bottom:3rem;border-bottom:1px solid rgba(255,255,255,.06);margin-bottom:2.5rem;}
.footer-logo{height:34px;width:auto;margin-bottom:1.25rem;opacity:.75;}
.footer-tagline{font-family:var(--serif);font-style:italic;font-size:.92rem;color:rgba(245,240,232,.4);line-height:1.7;max-width:220px;}
.footer-col h4{font-size:.62rem;font-weight:500;letter-spacing:.22em;text-transform:uppercase;color:var(--cream);margin-bottom:1.25rem;}
.footer-col a,.footer-col p{display:block;font-size:.82rem;color:rgba(245,240,232,.38);margin-bottom:.65rem;transition:color .2s;line-height:1.4;}
.footer-col a:hover{color:var(--gold);}
.footer-bot{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;}
.footer-copy{font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;color:rgba(245,240,232,.22);}
.footer-copy span{color:var(--gold);}

/* ── REVEAL ── */
.reveal{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease);}
.reveal.visible{opacity:1;transform:translateY(0);}

/* ── RESPONSIVE ── */
@media(max-width:1200px){.footer-top{grid-template-columns:1fr 1fr;gap:3rem;}}
@media(max-width:1024px){
    .section,.cats-section,.gallery-section,.trust-section{padding:5rem 2.5rem;}
    .nav{padding:1.1rem 2.5rem;} .nav.solid{padding:.8rem 2.5rem;}
    .gallery-grid{grid-template-columns:repeat(3,1fr);}
    .product-card:first-child{grid-column:span 2;}
    .trust-inner{grid-template-columns:1fr;gap:3rem;}
    .featured-body{padding:3.5rem;}
    .cstrip{padding:3rem 2.5rem;} footer{padding:3rem 2.5rem 2rem;}
}
@media(max-width:768px){
    .nav{padding:1rem 1.5rem;} .nav.solid{padding:.75rem 1.5rem;}
    .nav-links{display:none;} .nav-hbg{display:flex;}
    .section,.cats-section,.gallery-section,.trust-section,.cstrip{padding:4rem 1.5rem;}
    .cats-header,.gallery-header{flex-direction:column;align-items:flex-start;gap:1rem;}
    .cats-grid{grid-template-columns:repeat(2,1fr);}
    .featured{grid-template-columns:1fr;}
    .featured-img{aspect-ratio:4/3;} .featured-img img{height:100%;}
    .featured-body{padding:2.5rem 1.5rem;}
    .gallery-grid{grid-template-columns:repeat(2,1fr);gap:.75rem;}
    .product-card:first-child{grid-column:span 2;grid-row:span 1;}
    .product-card:first-child .product-card-img{aspect-ratio:4/3;height:auto;}
    .trust-feats{grid-template-columns:1fr;gap:2rem;}
    .cstrip{grid-template-columns:1fr;} .cstrip-acts{width:100%;}
    .footer-top{grid-template-columns:1fr 1fr;gap:2rem;} footer{padding:3rem 1.5rem 1.5rem;}
    .hero-content{padding:0 6% 14%;}
    .hero-counter,.scroll-cue{display:none;}
}
@media(max-width:480px){
    .gallery-grid{grid-template-columns:1fr;}
    .product-card:first-child{grid-column:span 1;}
    .cats-grid{grid-template-columns:1fr;}
    .footer-top{grid-template-columns:1fr;}
    .cstrip-acts{flex-direction:column;}
    .btn-ink,.btn-ghost-ink{width:100%;justify-content:center;}
}
</style>
</head>
<body>

<!-- NAVBAR -->
<header class="nav" id="mainNav">
    <a href="index.php"><img src="assets/images/logo.png" class="nav-logo" alt="<?= htmlspecialchars($siteName) ?>"></a>
    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="#categories">Categories</a>
        <a href="#gallery">Gallery</a>
        <a href="contact.php" class="nav-cta">Contact</a>
    </nav>
    <button class="nav-hbg" id="hbg" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
</header>
<div class="mob-overlay" id="mobOverlay"></div>
<nav class="mob-menu" id="mobMenu">
    <a href="index.php">Home</a>
    <a href="#categories">Categories</a>
    <a href="#gallery">Gallery</a>
    <a href="contact.php">Contact</a>
</nav>


<!-- HERO -->
<section class="hero" id="hero">
    <div class="hero-slides">
        <?php if (empty($sliders)): ?>
        <div class="hero-slide active" style="background:linear-gradient(135deg,#1a1510 0%,#0e0b08 100%);">
            <div class="hero-content">
                <span class="hero-eyebrow">Premium African Fashion</span>
                <h1 class="hero-title"><?= htmlspecialchars($siteName) ?></h1>
                <p class="hero-sub">Curated collections crafted for the modern wardrobe.</p>
                <div class="hero-actions">
                    <a href="#gallery" class="btn-primary">Shop Now</a>
                    <a href="contact.php" class="btn-outline">Contact Us</a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($sliders as $i => $s): ?>
        <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>">
            <img src="assets/images/<?= htmlspecialchars($s['image']) ?>" alt="<?= htmlspecialchars($s['caption']) ?>">
            <div class="hero-content">
                <span class="hero-eyebrow"><?= htmlspecialchars($s['sub_caption'] ?: 'New Collection') ?></span>
                <h1 class="hero-title"><?= htmlspecialchars($s['caption']) ?></h1>
                <div class="hero-actions">
                    <a href="<?= $s['link_url'] ? htmlspecialchars($s['link_url']) : '#gallery' ?>" class="btn-primary">Shop Now</a>
                    <a href="contact.php" class="btn-outline">Enquire</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (count($sliders) > 1): ?>
    <div class="hero-nav" id="heroDots"></div>
    <div class="hero-counter" id="heroCounter">01 / <?= str_pad(count($sliders),2,'0',STR_PAD_LEFT) ?></div>
    <?php endif; ?>

    <div class="scroll-cue">
        <span>Scroll</span>
        <div class="scroll-line"></div>
    </div>
</section>


<!-- MARQUEE -->
<div class="marquee" aria-hidden="true">
    <div class="marquee-track">
        <?php
        $tags = ['New Arrivals','Premium Quality','Made in Ghana','Fashion Forward','RichelCity Enterprise','Exclusive Styles','African Fashion','Curated Collections'];
        foreach (array_merge($tags,$tags) as $t) echo "<span class='marquee-item'>".htmlspecialchars($t)."</span>";
        ?>
    </div>
</div>


<!-- CATEGORIES -->
<section class="cats-section" id="categories">
    <div class="cats-header">
        <div>
            <div class="section-label">Browse</div>
            <h2 class="section-heading">Collections</h2>
            <p class="section-sub">Discover pieces curated for every style.</p>
        </div>
        <a href="#gallery" class="cats-all">View all pieces →</a>
    </div>
    <div class="cats-grid">
        <?php if (empty($categories)):
            foreach (['Men Fashion','Women Fashion','Kids Wear','Accessories'] as $i => $name): ?>
        <div class="cat-plain reveal">
            <div class="cat-body">
                <div class="cat-num"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></div>
                <div class="cat-name"><?= htmlspecialchars($name) ?></div>
            </div>
        </div>
        <?php endforeach; else:
            foreach ($categories as $i => $cat):
                $ip = 'assets/images/' . $cat['image'];
                $hasImg = !empty($cat['image']) && file_exists($ip);
        ?>
        <?php if ($hasImg): ?>
        <div class="cat-img reveal">
            <img src="<?= htmlspecialchars($ip) ?>" alt="<?= htmlspecialchars($cat['name']) ?>">
            <div class="cat-body">
                <div class="cat-num"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></div>
                <div class="cat-name"><?= htmlspecialchars($cat['name']) ?></div>
            </div>
        </div>
        <?php else: ?>
        <div class="cat-plain reveal">
            <div class="cat-body">
                <div class="cat-num"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></div>
                <div class="cat-name"><?= htmlspecialchars($cat['name']) ?></div>
            </div>
        </div>
        <?php endif; endforeach; endif; ?>
    </div>
</section>


<!-- FEATURED PRODUCT -->
<?php if ($featured): ?>
<div class="featured">
    <div class="featured-img">
        <img src="assets/images/<?= htmlspecialchars($featured['image']) ?>"
             alt="<?= htmlspecialchars($featured['name']) ?>">
    </div>
    <div class="featured-body">
        <div class="featured-tag">Featured Piece</div>
        <h2 class="featured-name"><?= htmlspecialchars($featured['name']) ?></h2>
        <?php if ($featured['description']): ?>
        <p class="featured-desc"><?= htmlspecialchars(mb_substr($featured['description'],0,160)) ?>…</p>
        <?php endif; ?>
        <div class="featured-price">
            <small>GHS</small><?= number_format((float)$featured['price'],2) ?>
        </div>
        <a href="product.php?slug=<?= urlencode($featured['slug']) ?>" class="btn-dark">View Details →</a>
    </div>
</div>
<?php endif; ?>


<!-- GALLERY -->
<section class="gallery-section" id="gallery">
    <div class="gallery-header">
        <div>
            <div class="section-label">Gallery</div>
            <h2 class="section-heading">Latest Pieces</h2>
        </div>
        <a href="contact.php" class="gallery-link">Enquire about any piece →</a>
    </div>
    <div class="gallery-grid">
        <?php foreach ($products as $p): ?>
        <a href="product.php?slug=<?= urlencode($p['slug']) ?>" class="product-card reveal">
            <img class="product-card-img"
                 src="assets/images/<?= htmlspecialchars($p['image']) ?>"
                 alt="<?= htmlspecialchars($p['name']) ?>">
            <div class="product-overlay">
                <span class="product-name"><?= htmlspecialchars($p['name']) ?></span>
                <span class="product-price">GHS <?= number_format((float)$p['price'],2) ?></span>
                <span class="product-cta">View Details</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>


<!-- WHY RICHELCITY -->
<section class="trust-section">
    <div class="trust-inner">
        <div>
            <div class="section-label" style="color:rgba(201,168,76,.6);">Why Us</div>
            <h2 class="section-heading">Crafted With<br>Intention</h2>
            <p class="section-sub" style="color:rgba(245,240,232,.4);margin-top:1rem;">
                Every piece is chosen for quality, style, and the story it tells.
            </p>
        </div>
        <div class="trust-feats">
            <div class="trust-feat reveal">
                <div class="trust-feat-icon">✦</div>
                <div class="trust-feat-title">Premium Quality</div>
                <p class="trust-feat-desc">Carefully selected materials and craftsmanship you can feel from the first touch.</p>
            </div>
            <div class="trust-feat reveal">
                <div class="trust-feat-icon">✦</div>
                <div class="trust-feat-title">African Heritage</div>
                <p class="trust-feat-desc">Rooted in Ghanaian culture, designed for the modern world.</p>
            </div>
            <div class="trust-feat reveal">
                <div class="trust-feat-icon">✦</div>
                <div class="trust-feat-title">Curated Selection</div>
                <p class="trust-feat-desc">Every item is handpicked — no filler, only pieces worth wearing.</p>
            </div>
            <div class="trust-feat reveal">
                <div class="trust-feat-icon">✦</div>
                <div class="trust-feat-title">Personal Service</div>
                <p class="trust-feat-desc">Direct access to our team for sizing, enquiries, and custom orders.</p>
            </div>
        </div>
    </div>
</section>


<!-- CONTACT STRIP -->
<div class="cstrip">
    <div>
        <h2>Find Your Next Favourite Piece</h2>
        <p>Get in touch — we're happy to help with sizing, availability, and orders.</p>
    </div>
    <div class="cstrip-acts">
        <a href="contact.php" class="btn-ink">Send Enquiry</a>
        <?php if ($waPhone): ?>
        <a href="https://wa.me/<?= $waPhone ?>?text=<?= urlencode("Hi RichelCity! I'd like to enquire about your collection.") ?>"
           target="_blank" rel="noopener" class="btn-ghost-ink">WhatsApp Us</a>
        <?php endif; ?>
    </div>
</div>


<!-- FOOTER -->
<footer>
    <div class="footer-top">
        <div>
            <img src="assets/images/logo.png" class="footer-logo" alt="<?= htmlspecialchars($siteName) ?>">
            <p class="footer-tagline">Premium African fashion, curated for the modern wardrobe.</p>
        </div>
        <div class="footer-col">
            <h4>Navigate</h4>
            <a href="index.php">Home</a>
            <a href="#categories">Categories</a>
            <a href="#gallery">Gallery</a>
            <a href="contact.php">Contact</a>
        </div>
        <div class="footer-col">
            <h4>Collections</h4>
            <?php foreach (array_slice($categories,0,4) as $cat): ?>
            <a href="#categories"><?= htmlspecialchars($cat['name']) ?></a>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
            <p>Men Fashion</p><p>Women Fashion</p><p>Kids Wear</p>
            <?php endif; ?>
        </div>
        <div class="footer-col">
            <h4>Get in Touch</h4>
            <?php if ($sitePhone): ?><p><?= htmlspecialchars($sitePhone) ?></p><?php endif; ?>
            <?php if ($siteEmail): ?><a href="mailto:<?= htmlspecialchars($siteEmail) ?>"><?= htmlspecialchars($siteEmail) ?></a><?php endif; ?>
            <?php if ($waPhone): ?><a href="https://wa.me/<?= $waPhone ?>" target="_blank" rel="noopener" style="color:rgba(76,175,125,.7);">WhatsApp</a><?php endif; ?>
            <p style="margin-top:.5rem;">Mon – Sat · 8am – 7pm</p>
        </div>
    </div>
    <div class="footer-bot">
        <p class="footer-copy">© <?= date('Y') ?> <span><?= htmlspecialchars($siteName) ?></span> — All Rights Reserved</p>
        <p class="footer-copy" style="color:rgba(245,240,232,.12);">Ghana</p>
    </div>
</footer>


<script>
/* ── Navbar ── */
const nav = document.getElementById('mainNav');
const syncNav = () => nav.classList.toggle('solid', window.scrollY > 80);
window.addEventListener('scroll', syncNav, { passive: true });
syncNav();

/* ── Mobile menu ── */
const hbg        = document.getElementById('hbg');
const mobMenu    = document.getElementById('mobMenu');
const mobOverlay = document.getElementById('mobOverlay');

function toggleMob() {
    const open = mobMenu.classList.toggle('open');
    hbg.classList.toggle('open', open);
    mobOverlay.classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
}

hbg.addEventListener('click', toggleMob);
mobOverlay.addEventListener('click', toggleMob);
mobMenu.querySelectorAll('a').forEach(a => a.addEventListener('click', toggleMob));

/* ── Hero Slider ── */
(function () {
    const slides   = document.querySelectorAll('.hero-slide');
    const dotsWrap = document.getElementById('heroDots');
    const counter  = document.getElementById('heroCounter');
    if (slides.length < 2) return;

    const total = slides.length;
    let cur = 0, timer = null;

    if (dotsWrap) {
        slides.forEach((_, i) => {
            const b = document.createElement('button');
            b.className = 'hero-dot' + (i === 0 ? ' active' : '');
            b.setAttribute('aria-label', 'Slide ' + (i + 1));
            b.addEventListener('click', () => { goTo(i); reset(); });
            dotsWrap.appendChild(b);
        });
    }

    function goTo(n) {
        slides[cur].classList.remove('active');
        if (dotsWrap) dotsWrap.children[cur].classList.remove('active');
        cur = ((n % total) + total) % total;
        slides[cur].classList.add('active');
        if (dotsWrap) dotsWrap.children[cur].classList.add('active');
        if (counter) counter.textContent = String(cur+1).padStart(2,'0')+' / '+String(total).padStart(2,'0');
    }

    function reset() { clearInterval(timer); timer = setInterval(() => goTo(cur + 1), 5500); }

    // Touch swipe
    let tx = 0;
    const heroEl = document.getElementById('hero');
    heroEl.addEventListener('touchstart', e => { tx = e.changedTouches[0].screenX; }, { passive: true });
    heroEl.addEventListener('touchend',   e => {
        const dx = e.changedTouches[0].screenX - tx;
        if (Math.abs(dx) > 50) { goTo(cur + (dx < 0 ? 1 : -1)); reset(); }
    }, { passive: true });

    document.addEventListener('keydown', e => {
        if (e.key === 'ArrowRight') { goTo(cur + 1); reset(); }
        if (e.key === 'ArrowLeft')  { goTo(cur - 1); reset(); }
    });

    reset();
})();

/* ── Scroll Reveal ── */
(function () {
    const els = document.querySelectorAll('.reveal');
    if (!els.length) return;
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const siblings = [...entry.target.parentElement.querySelectorAll('.reveal:not(.visible)')];
            const idx = siblings.indexOf(entry.target);
            setTimeout(() => entry.target.classList.add('visible'), Math.max(0, idx) * 80);
            obs.unobserve(entry.target);
        });
    }, { threshold: 0.1 });
    els.forEach(el => obs.observe(el));
})();
</script>

</body>
</html>
