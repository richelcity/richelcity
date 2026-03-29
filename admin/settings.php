<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'layout.php';
auth_guard();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!csrf_verify()) { flash('error','Invalid request.'); header('Location: settings.php'); exit; }

    if (isset($_POST['save_site'])) {
        foreach(['site_name','site_email','site_phone','currency_symbol','items_per_page'] as $k) {
            $v=trim($_POST[$k]??'');
            $st=$conn->prepare("INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
            $st->bind_param('ss',$k,$v); $st->execute(); $st->close();
        }
        flash('ok','Settings saved.');
    }

    if (isset($_POST['change_pass'])) {
        $cur=$_POST['current_password']??'';
        $new=$_POST['new_password']??'';
        $con=$_POST['confirm_password']??'';
        if (strlen($new)<6) { flash('error','Password must be at least 6 characters.'); }
        elseif ($new!==$con) { flash('error','Passwords do not match.'); }
        else {
            $st=$conn->prepare("SELECT password FROM admin WHERE id=?");
            $st->bind_param('i',$_SESSION['admin_id']); $st->execute();
            $hash=$st->get_result()->fetch_row()[0]??''; $st->close();
            if (!password_verify($cur,$hash)) { flash('error','Current password is incorrect.'); }
            else {
                $h=password_hash($new,PASSWORD_BCRYPT,['cost'=>12]);
                $st=$conn->prepare("UPDATE admin SET password=? WHERE id=?");
                $st->bind_param('si',$h,$_SESSION['admin_id']); $st->execute(); $st->close();
                flash('ok','Password updated.');
            }
        }
    }
    header('Location: settings.php'); exit;
}

$cfg=[];
$r=$conn->query("SELECT setting_key,setting_value FROM settings");
if($r) while($row=$r->fetch_assoc()) $cfg[$row['setting_key']]=$row['setting_value'];

layout_head('Settings');
layout_flash();
?>
<div class="ph"><h1>Settings</h1></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">

<div class="panel">
    <div class="panel-title">Site Settings</div>
    <form method="POST">
        <?php csrf_input(); ?>
        <input type="hidden" name="save_site" value="1">
        <div class="fg"><label>Site Name</label><input type="text" name="site_name" value="<?= e($cfg['site_name']??'RichelCity Enterprise') ?>"></div>
        <div class="fg"><label>Contact Email</label><input type="email" name="site_email" value="<?= e($cfg['site_email']??'') ?>"></div>
        <div class="fg"><label>Contact Phone</label><input type="text" name="site_phone" value="<?= e($cfg['site_phone']??'') ?>"></div>
        <div class="frow">
            <div class="fg"><label>Currency</label><input type="text" name="currency_symbol" value="<?= e($cfg['currency_symbol']??'GHS') ?>"></div>
            <div class="fg"><label>Items per Page</label><input type="number" name="items_per_page" min="4" max="100" value="<?= e($cfg['items_per_page']??'12') ?>"></div>
        </div>
        <button type="submit" class="btn btn-gold">Save Settings</button>
    </form>
</div>

<div class="panel">
    <div class="panel-title">Change Password</div>
    <form method="POST">
        <?php csrf_input(); ?>
        <input type="hidden" name="change_pass" value="1">
        <div class="fg"><label>Current Password</label><input type="password" name="current_password" autocomplete="current-password"></div>
        <div class="fg"><label>New Password</label><input type="password" name="new_password" autocomplete="new-password"><p class="hint">Minimum 6 characters.</p></div>
        <div class="fg"><label>Confirm New Password</label><input type="password" name="confirm_password" autocomplete="new-password"></div>
        <button type="submit" class="btn btn-gold">Update Password</button>
    </form>
    <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--border);">
        <div style="font-size:.62rem;letter-spacing:.15em;text-transform:uppercase;color:var(--muted);margin-bottom:.4rem;">Logged in as</div>
        <div style="font-family:var(--display);font-size:1.3rem;color:var(--gold);"><?= e($_SESSION['admin_user']) ?></div>
        <div style="font-size:.75rem;color:var(--muted);">Role: <?= e($_SESSION['admin_role']) ?></div>
    </div>
</div>

</div>
<?php layout_end(); ?>
