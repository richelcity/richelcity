<?php
/**
 * One-time admin account creator — DELETE after use
 */
require_once 'config.php';

$msg = ''; $ok = false;

// Show existing accounts
$rows = [];
$r = $conn->query("SELECT id, username, LEFT(password,7) AS hp, role FROM admin ORDER BY id");
if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user  = trim($_POST['username'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $conf  = $_POST['confirm']  ?? '';
    $errs  = [];
    if (strlen($user) < 3) $errs[] = 'Username must be at least 3 characters.';
    if (strlen($pass) < 6) $errs[] = 'Password must be at least 6 characters.';
    if ($pass !== $conf)   $errs[] = 'Passwords do not match.';

    if (empty($errs)) {
        $chk = $conn->prepare("SELECT id FROM admin WHERE username=?");
        $chk->bind_param('s', $user); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) $errs[] = "Username '$user' already taken.";
        $chk->close();
    }

    if (empty($errs)) {
        $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost'=>12]);
        $role = 'superadmin';
        $st   = $conn->prepare("INSERT INTO admin (username,password,role) VALUES (?,?,?)");
        $st->bind_param('sss', $user, $hash, $role);
        if ($st->execute()) { $ok = true; $msg = "Account '$user' created. Delete this file now!"; }
        else                { $msg = 'DB error: ' . $conn->error; }
        $st->close();
    } else {
        $msg = implode(' | ', $errs);
    }
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Setup</title>
<style>
body{font-family:sans-serif;background:#0e0b08;color:#f5f0e8;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:2rem;}
.box{background:#1c1916;border:1px solid rgba(255,255,255,.1);border-radius:4px;padding:2rem;width:100%;max-width:420px;}
h1{color:#c9a84c;margin-bottom:.5rem;font-size:1.4rem;}
.warn{background:rgba(232,168,56,.1);border:1px solid rgba(232,168,56,.3);border-radius:3px;padding:.75rem 1rem;font-size:.82rem;color:#e8a838;margin-bottom:1.5rem;line-height:1.5;}
.msg-ok {background:rgba(76,175,125,.1);border:1px solid rgba(76,175,125,.3);color:#4caf7d;padding:.75rem 1rem;border-radius:3px;margin-bottom:1rem;font-size:.85rem;}
.msg-err{background:rgba(224,82,82,.1); border:1px solid rgba(224,82,82,.3); color:#e05252;padding:.75rem 1rem;border-radius:3px;margin-bottom:1rem;font-size:.85rem;}
.fg{margin-bottom:1rem;}
label{display:block;font-size:.65rem;letter-spacing:.15em;text-transform:uppercase;color:#6b6460;margin-bottom:.35rem;}
input{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:2px;color:#f5f0e8;font-size:.9rem;padding:.65rem .85rem;outline:none;}
input:focus{border-color:#c9a84c;}
.btn{width:100%;background:#c9a84c;color:#0e0b08;border:none;padding:.85rem;font-size:.75rem;font-weight:600;letter-spacing:.2em;text-transform:uppercase;cursor:pointer;border-radius:2px;margin-top:.5rem;}
.accounts{margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid rgba(255,255,255,.08);}
.acc-row{display:flex;justify-content:space-between;font-size:.8rem;padding:.4rem 0;border-bottom:1px solid rgba(255,255,255,.05);}
.bcrypt{color:#4caf7d;} .plain{color:#e05252;}
a{color:#c9a84c;}
</style></head><body>
<div class="box">
    <h1>Create Admin Account</h1>
    <p style="font-size:.8rem;color:#6b6460;margin-bottom:1.5rem;">RichelCity one-time setup</p>
    <div class="warn">⚠ No authentication on this page. <strong>Delete setup.php immediately after use.</strong></div>

    <?php if ($msg): ?>
    <div class="<?= $ok ? 'msg-ok' : 'msg-err' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if (!$ok): ?>
    <form method="POST">
        <div class="fg"><label>Username</label><input type="text" name="username" autocomplete="off" value="<?= htmlspecialchars($_POST['username']??'') ?>"></div>
        <div class="fg"><label>Password (min 6 chars)</label><input type="password" name="password"></div>
        <div class="fg"><label>Confirm Password</label><input type="password" name="confirm"></div>
        <button type="submit" class="btn">Create Account</button>
    </form>
    <?php else: ?>
    <p style="margin-top:1rem;"><a href="login.php">→ Go to Login</a></p>
    <?php endif; ?>

    <?php if (!empty($rows)): ?>
    <div class="accounts">
        <p style="font-size:.65rem;letter-spacing:.15em;text-transform:uppercase;color:#6b6460;margin-bottom:.75rem;">Existing Accounts</p>
        <?php foreach ($rows as $row):
            $bcrypt = str_starts_with($row['hp'], '$2y$');
        ?>
        <div class="acc-row">
            <span><?= htmlspecialchars($row['username']) ?> <span style="color:#6b6460;">(<?= $row['role'] ?>)</span></span>
            <span class="<?= $bcrypt ? 'bcrypt' : 'plain' ?>"><?= $bcrypt ? '✅ bcrypt' : '❌ plain text — reset needed' ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</body></html>
