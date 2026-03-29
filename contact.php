<?php
include 'config.php';

$success = false;
$errors  = [];

// Pre-fill product name from query string
$prefillProduct = htmlspecialchars(trim($_GET['product'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name    === '') $errors[] = 'Name is required.';
    if ($message === '') $errors[] = 'Message is required.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO enquiries (name, email, phone, message) VALUES (?,?,?,?)"
        );
        $stmt->bind_param('ssss', $name, $email, $phone, $message);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = 'Could not save your message. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact — RichelCity Enterprise</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&family=Cormorant+Garamond:ital,wght@1,400&display=swap" rel="stylesheet">
    <style>
        :root{--ink:#0e0b08;--cream:#f5f0e8;--warm:#e8dcc8;--gold:#c9a84c;--muted:#7a7065;--serif:'Cormorant Garamond',serif;--display:'Bebas Neue',sans-serif;--body:'DM Sans',sans-serif;--t:.25s ease;}
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
        body{background:var(--cream);color:var(--ink);font-family:var(--body);font-weight:300;}
        a{color:inherit;text-decoration:none;}
        .navbar{position:sticky;top:0;z-index:900;display:flex;align-items:center;justify-content:space-between;padding:1.1rem 3rem;background:rgba(245,240,232,.92);backdrop-filter:blur(12px);border-bottom:1px solid rgba(201,168,76,.2);}
        .logo{height:40px;width:auto;}
        nav{display:flex;gap:2rem;}
        nav a{font-size:.72rem;font-weight:500;letter-spacing:.18em;text-transform:uppercase;}
        nav a:hover{color:var(--gold);}

        .contact-page{max-width:900px;margin:0 auto;padding:5rem 2rem;}
        .page-header{margin-bottom:3rem;}
        .page-header h1{font-family:var(--display);font-size:clamp(3rem,6vw,5rem);letter-spacing:.04em;line-height:.95;}
        .page-header p{font-family:var(--serif);font-style:italic;font-size:1.1rem;color:var(--muted);margin-top:.75rem;}

        .contact-grid{display:grid;grid-template-columns:1fr 380px;gap:4rem;align-items:start;}

        /* Form */
        .form-group{margin-bottom:1.4rem;}
        label{display:block;font-size:.65rem;font-weight:500;letter-spacing:.18em;text-transform:uppercase;color:var(--muted);margin-bottom:.45rem;}
        input[type="text"],input[type="email"],input[type="tel"],textarea{width:100%;background:transparent;border:none;border-bottom:1px solid rgba(14,11,8,.2);color:var(--ink);font-family:var(--body);font-size:.95rem;font-weight:300;padding:.6rem 0;outline:none;border-radius:0;transition:border-color var(--t);}
        input:focus,textarea:focus{border-bottom-color:var(--gold);}
        textarea{resize:vertical;min-height:130px;}
        .btn-send{background:var(--ink);color:var(--cream);border:none;font-family:var(--body);font-size:.72rem;font-weight:500;letter-spacing:.22em;text-transform:uppercase;padding:1rem 3rem;cursor:pointer;border-radius:2px;transition:background var(--t);}
        .btn-send:hover{background:#1e1a16;}

        /* Feedback */
        .success-box{background:rgba(76,175,125,.1);border:1px solid rgba(76,175,125,.3);border-radius:2px;padding:1.25rem;margin-bottom:2rem;}
        .success-box p{font-family:var(--serif);font-style:italic;font-size:1.05rem;color:#4caf7d;}
        .error-list{background:rgba(224,82,82,.08);border:1px solid rgba(224,82,82,.25);border-radius:2px;padding:1rem 1.25rem;margin-bottom:1.5rem;}
        .error-list li{font-size:.82rem;color:#e05252;margin-bottom:.25rem;}
        .error-list li:last-child{margin-bottom:0;}

        /* Info panel */
        .info-panel{background:var(--ink);color:var(--cream);padding:2.5rem;border-radius:2px;}
        .info-title{font-family:var(--display);font-size:1.5rem;letter-spacing:.06em;color:var(--gold);margin-bottom:1.5rem;}
        .info-item{margin-bottom:1.5rem;}
        .info-label{font-size:.62rem;letter-spacing:.18em;text-transform:uppercase;color:var(--muted);margin-bottom:.3rem;}
        .info-value{font-size:.9rem;}
        .info-value a{color:var(--cream);border-bottom:1px solid rgba(201,168,76,.3);transition:border-color var(--t);}
        .info-value a:hover{border-color:var(--gold);}

        footer{background:var(--ink);color:var(--muted);text-align:center;padding:2.5rem;font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;margin-top:6rem;}
        footer b{color:var(--gold);}

        @media(max-width:768px){
            .navbar{padding:1rem 1.5rem;}nav{display:none;}
            .contact-grid{grid-template-columns:1fr;}
            .contact-page{padding:3rem 1.25rem;}
        }
    </style>
</head>
<body>
<header class="navbar">
    <a href="index.php"><img src="assets/images/logo.png" class="logo" alt="RichelCity"></a>
    <nav>
        <a href="index.php">Home</a>
        <a href="index.php#categories">Categories</a>
        <a href="index.php#gallery">Gallery</a>
        <a href="contact.php">Contact</a>
    </nav>
</header>

<div class="contact-page">
    <div class="page-header">
        <h1>Get In Touch</h1>
        <p>Questions about a piece? We'd love to hear from you.</p>
    </div>

    <div class="contact-grid">
        <div>
            <?php if ($success): ?>
            <div class="success-box">
                <p>Thank you! Your message has been received. We'll be in touch shortly.</p>
            </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <ul class="error-list">
                <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="contact.php" novalidate>
                <div class="form-group">
                    <label for="name">Your Name *</label>
                    <input type="text" id="name" name="name" required
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" required><?= htmlspecialchars(
                        $_POST['message'] ?? ($prefillProduct ? "Hi, I'm interested in: {$prefillProduct}" : '')
                    ) ?></textarea>
                </div>
                <button type="submit" class="btn-send">Send Message</button>
            </form>
            <?php endif; ?>
        </div>

        <?php
        $settings = [];
        $sr = $conn->query("SELECT setting_key, setting_value FROM settings");
        if ($sr) while ($row = $sr->fetch_assoc()) $settings[$row['setting_key']] = $row['setting_value'];
        $siteName  = htmlspecialchars($settings['site_name']  ?? 'RichelCity Enterprise');
        $siteEmail = htmlspecialchars($settings['site_email'] ?? '');
        $sitePhone = htmlspecialchars($settings['site_phone'] ?? '');
        $waPhone   = preg_replace('/\D/', '', $settings['site_phone'] ?? '');
        ?>
        <div class="info-panel">
            <div class="info-title"><?= $siteName ?></div>
            <?php if ($siteEmail): ?>
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value"><a href="mailto:<?= $siteEmail ?>"><?= $siteEmail ?></a></div>
            </div>
            <?php endif; ?>
            <?php if ($sitePhone): ?>
            <div class="info-item">
                <div class="info-label">Phone / WhatsApp</div>
                <div class="info-value">
                    <a href="tel:<?= $sitePhone ?>"><?= $sitePhone ?></a>
                    <?php if ($waPhone): ?>
                    <br><a href="https://wa.me/<?= $waPhone ?>" target="_blank" rel="noopener"
                           style="font-size:.72rem;letter-spacing:.1em;color:#25d366;border-color:rgba(37,211,102,.3);">
                        Chat on WhatsApp
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="info-item">
                <div class="info-label">Hours</div>
                <div class="info-value" style="color:var(--muted);">Mon – Sat · 8am – 7pm</div>
            </div>
        </div>
    </div>
</div>

<footer>© <?= date('Y') ?> <b>RichelCity Enterprise</b> — All Rights Reserved</footer>
</body>
</html>