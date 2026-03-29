<?php
session_start();

// ── If already logged in, go straight to dashboard ───────────────────────────
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

// ── Config is in the same folder as login.php ─────────────────────────────────
require_once 'config.php';
require_once 'functions.php';

// Show errors so nothing fails silently during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ── Generate CSRF token BEFORE handling POST ──────────────────────────────────
$token = csrf_token();

$error = '';
$attempts = $_SESSION['login_attempts'] ?? 0;
$lockout  = $_SESSION['lockout_until']  ?? 0;

// ── Check lockout ─────────────────────────────────────────────────────────────
$locked = time() < $lockout;
if ($locked) {
    $error = 'Too many failed attempts. Try again in ' . ceil(($lockout - time()) / 60) . ' min.';
}

// ── Handle POST ───────────────────────────────────────────────────────────────
if (!$locked && $_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_verify()) {
        // Token mismatch — regenerate and tell user to try again
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        $token = $_SESSION['csrf'];
        $error = 'Form expired. Please try again.';

    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password']       ?? '';

        if ($username === '' || $password === '') {
            $error = 'Enter both username and password.';
        } else {
            // Fetch account
            $st = $conn->prepare("SELECT id, username, password, role FROM admin WHERE username=? LIMIT 1");
            $st->bind_param('s', $username);
            $st->execute();
            $row = $st->get_result()->fetch_assoc();
            $st->close();

            // Always call password_verify (dummy hash when user not found = no timing leak)
            $hash   = $row['password'] ?? '$2y$12$aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
            $valid  = password_verify($password, $hash);

            if ($row && $valid) {
                // ── SUCCESS ───────────────────────────────────────────────────
                $_SESSION['login_attempts'] = 0;
                $_SESSION['lockout_until']  = 0;

                session_regenerate_id(true);

                $_SESSION['admin_id']   = $row['id'];
                $_SESSION['admin_user'] = $row['username'];
                $_SESSION['admin_role'] = $row['role'] ?? 'editor';

                // Update last_login timestamp
                $upd = $conn->prepare("UPDATE admin SET last_login=NOW() WHERE id=?");
                $upd->bind_param('i', $row['id']);
                $upd->execute();
                $upd->close();

                header('Location: dashboard.php');
                exit;

            } else {
                // ── FAILURE ───────────────────────────────────────────────────
                $_SESSION['login_attempts'] = $attempts + 1;

                if ($_SESSION['login_attempts'] >= 5) {
                    $_SESSION['lockout_until'] = time() + 900;
                    $error = 'Too many failed attempts. Locked for 15 minutes.';
                } else {
                    $left  = 5 - $_SESSION['login_attempts'];
                    $error = 'Incorrect username or password. ' . $left . ' attempt(s) left.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — RichelCity Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:opsz,wght@9..40,300;9..40,500&family=Cormorant+Garamond:ital,wght@1,300&display=swap" rel="stylesheet">
<style>
:root{--ink:#0e0b08;--panel:#161310;--border:rgba(255,255,255,.09);--cream:#f5f0e8;--gold:#c9a84c;--muted:#6b6460;--err:#e05252;}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html,body{height:100%;background:var(--ink);color:var(--cream);font-family:'DM Sans',system-ui,sans-serif;font-weight:300;}
body::before{content:'';position:fixed;inset:0;background:repeating-linear-gradient(45deg,transparent,transparent 40px,rgba(201,168,76,.025) 40px,rgba(201,168,76,.025) 41px),repeating-linear-gradient(-45deg,transparent,transparent 40px,rgba(201,168,76,.025) 40px,rgba(201,168,76,.025) 41px);pointer-events:none;}
body::after{content:'';position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:600px;height:600px;background:radial-gradient(circle,rgba(201,168,76,.06) 0%,transparent 70%);pointer-events:none;}
.page{min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem;position:relative;z-index:1;}
.brand{text-align:center;margin-bottom:2.5rem;}
.brand-name{font-family:'Bebas Neue',sans-serif;font-size:3rem;letter-spacing:.12em;color:var(--gold);line-height:1;}
.brand-sub{font-family:'Cormorant Garamond',serif;font-style:italic;font-size:.95rem;color:var(--muted);margin-top:.3rem;}
.card{width:100%;max-width:400px;background:var(--panel);border:1px solid var(--border);border-radius:2px;padding:2.5rem;}
.card-label{font-size:.63rem;font-weight:500;letter-spacing:.25em;text-transform:uppercase;color:var(--muted);margin-bottom:2rem;display:flex;align-items:center;gap:.75rem;}
.card-label::after{content:'';flex:1;height:1px;background:var(--border);}
.err{background:rgba(224,82,82,.1);border:1px solid rgba(224,82,82,.3);border-radius:2px;color:var(--err);font-size:.82rem;padding:.85rem 1rem;margin-bottom:1.5rem;line-height:1.5;}
.fg{margin-bottom:1.2rem;}
label{display:block;font-size:.63rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;color:var(--muted);margin-bottom:.45rem;}
.iw{position:relative;}
input[type=text],input[type=password]{width:100%;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:2px;color:var(--cream);font-family:'DM Sans',sans-serif;font-size:.9rem;font-weight:300;padding:.75rem 2.75rem .75rem .9rem;outline:none;transition:border-color .2s;-webkit-appearance:none;}
input:focus{border-color:var(--gold);background:rgba(201,168,76,.04);}
input:disabled{opacity:.4;cursor:not-allowed;}
.eye{position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:.9rem;padding:.2rem;user-select:none;transition:color .2s;}
.eye:hover{color:var(--cream);}
.btn{width:100%;background:var(--gold);color:var(--ink);border:none;border-radius:2px;padding:.9rem;font-family:'DM Sans',sans-serif;font-size:.75rem;font-weight:600;letter-spacing:.22em;text-transform:uppercase;cursor:pointer;margin-top:.5rem;transition:opacity .2s;}
.btn:hover:not(:disabled){opacity:.88;}
.btn:disabled{opacity:.4;cursor:not-allowed;}
.foot{text-align:center;margin-top:1.5rem;font-size:.65rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);}
</style>
</head>
<body>
<div class="page">
    <div class="brand">
        <div class="brand-name">RichelCity</div>
        <div class="brand-sub">Enterprise Admin</div>
    </div>

    <div class="card">
        <div class="card-label">Secure Sign In</div>

        <?php if ($error): ?>
        <div class="err">⚠ <?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <input type="hidden" name="csrf" value="<?= e($token) ?>">

            <div class="fg">
                <label for="u">Username</label>
                <div class="iw">
                    <input type="text" id="u" name="username"
                           autocomplete="username" spellcheck="false"
                           <?= $locked ? 'disabled' : '' ?>
                           value="<?= e($_POST['username'] ?? '') ?>">
                </div>
            </div>

            <div class="fg">
                <label for="p">Password</label>
                <div class="iw">
                    <input type="password" id="p" name="password"
                           autocomplete="current-password"
                           <?= $locked ? 'disabled' : '' ?>>
                    <button type="button" class="eye" id="eye">👁</button>
                </div>
            </div>

            <button type="submit" class="btn" <?= $locked ? 'disabled' : '' ?>>
                Sign In
            </button>
        </form>
    </div>

    <div class="foot">© <?= date('Y') ?> RichelCity Enterprise</div>
</div>
<script>
var eye = document.getElementById('eye');
var pwd = document.getElementById('p');
eye.onclick = function() {
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
    eye.textContent = pwd.type === 'password' ? '👁' : '🙈';
};
var u = document.getElementById('u');
if (u && !u.disabled) { u.value ? pwd.focus() : u.focus(); }
</script>
</body>
</html>
