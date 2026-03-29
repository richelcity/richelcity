<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'layout.php';
auth_guard();

$q = fn(string $sql) => (int)$conn->query($sql)->fetch_row()[0];

$stats = [
    'products'  => $q("SELECT COUNT(*) FROM products WHERE status!='archived'"),
    'orders'    => $q("SELECT COUNT(*) FROM orders"),
    'pending'   => $q("SELECT COUNT(*) FROM orders WHERE status='pending'"),
    'customers' => $q("SELECT COUNT(*) FROM customers"),
    'enquiries' => $q("SELECT COUNT(*) FROM enquiries WHERE is_read=0"),
];
$rev = $conn->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status='paid'");
$stats['revenue'] = (float)$rev->fetch_row()[0];

$orders = $conn->query(
    "SELECT o.reference,o.total_amount,o.status,o.payment_status,o.created_at,c.name AS cname
     FROM orders o LEFT JOIN customers c ON o.customer_id=c.id
     ORDER BY o.created_at DESC LIMIT 8"
)->fetch_all(MYSQLI_ASSOC);

$lowstock = $conn->query(
    "SELECT name,stock FROM products WHERE status='available' AND stock<=5 ORDER BY stock ASC LIMIT 6"
)->fetch_all(MYSQLI_ASSOC);

layout_head('Dashboard');
layout_flash();
?>
<style>
.sgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:1rem;margin-bottom:2.5rem;}
.scard{background:var(--panel);border:1px solid var(--border);border-radius:2px;padding:1.5rem;border-top:2px solid var(--gold);}
.sval{font-family:var(--display);font-size:2.4rem;letter-spacing:.04em;line-height:1;}
.slbl{font-size:.62rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;color:var(--muted);margin-bottom:.4rem;}
.ssub{font-size:.75rem;color:var(--muted);margin-top:.25rem;}
.dgrid{display:grid;grid-template-columns:1fr 260px;gap:1.5rem;}
.li{display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--border);font-size:.82rem;}
.li:last-child{border-bottom:none;}
@media(max-width:900px){.dgrid{grid-template-columns:1fr;}}
</style>

<div class="ph">
    <h1>Dashboard</h1>
    <span class="ph-meta">Welcome, <?= e($_SESSION['admin_user']) ?></span>
</div>

<div class="sgrid">
    <div class="scard"><div class="slbl">Products</div><div class="sval"><?= $stats['products'] ?></div></div>
    <div class="scard"><div class="slbl">Orders</div><div class="sval"><?= $stats['orders'] ?></div><div class="ssub"><?= $stats['pending'] ?> pending</div></div>
    <div class="scard"><div class="slbl">Revenue (Paid)</div><div class="sval" style="font-size:1.4rem;color:var(--gold);">GHS <?= number_format($stats['revenue'],2) ?></div></div>
    <div class="scard"><div class="slbl">Customers</div><div class="sval"><?= $stats['customers'] ?></div></div>
    <div class="scard"><div class="slbl">Unread Enquiries</div><div class="sval" style="<?= $stats['enquiries']>0?'color:var(--gold)':'' ?>"><?= $stats['enquiries'] ?></div></div>
</div>

<div class="dgrid">
    <div class="panel">
        <div class="panel-title">Recent Orders</div>
        <?php if (empty($orders)): ?><div class="empty"><p>No orders yet.</p></div>
        <?php else: ?>
        <div class="tw" style="border:none;">
            <table>
                <thead><tr><th>Ref</th><th>Customer</th><th>Amount</th><th>Status</th><th>Payment</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $o):
                    $sb = match($o['status']){'pending'=>'b-warn','processing'=>'b-ok','delivered'=>'b-gold','cancelled'=>'b-err',default=>'b-grey'};
                    $pb = $o['payment_status']==='paid' ? 'b-ok' : 'b-grey';
                ?>
                <tr>
                    <td style="font-family:var(--display);letter-spacing:.05em;"><?= e($o['reference']) ?></td>
                    <td><?= e($o['cname'] ?? 'Guest') ?></td>
                    <td>GHS <?= number_format((float)$o['total_amount'],2) ?></td>
                    <td><span class="badge <?= $sb ?>"><?= $o['status'] ?></span></td>
                    <td><span class="badge <?= $pb ?>"><?= $o['payment_status'] ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;"><a href="orders.php" class="btn btn-ghost btn-sm">All Orders →</a></div>
        <?php endif; ?>
    </div>

    <div class="panel">
        <div class="panel-title">Low Stock</div>
        <?php if (empty($lowstock)): ?><p style="font-family:var(--serif);font-style:italic;color:var(--muted);font-size:.9rem;">All products well stocked.</p>
        <?php else: ?>
        <?php foreach ($lowstock as $p): ?>
        <div class="li">
            <span><?= e($p['name']) ?></span>
            <span style="font-family:var(--display);color:var(--err);"><?= (int)$p['stock'] ?></span>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:1rem;"><a href="products.php" class="btn btn-ghost btn-sm">Manage →</a></div>
        <?php endif; ?>
    </div>
</div>

<?php layout_end(); ?>
