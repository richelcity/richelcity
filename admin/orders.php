<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'layout.php';
auth_guard();

// Update status from detail form
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['order_id'])) {
    if (!csrf_verify()) { flash('error','Invalid request.'); header('Location: orders.php'); exit; }
    $oid    = (int)$_POST['order_id'];
    $status = in_array($_POST['status']??'',['pending','processing','delivered','cancelled']) ? $_POST['status'] : null;
    $pay    = in_array($_POST['payment_status']??'',['unpaid','paid','refunded']) ? $_POST['payment_status'] : null;
    if ($status && $pay) {
        $st=$conn->prepare("UPDATE orders SET status=?,payment_status=? WHERE id=?");
        $st->bind_param('ssi',$status,$pay,$oid); $st->execute(); $st->close();
        flash('ok','Order updated.');
    }
    header("Location: orders.php?view=$oid"); exit;
}

$view = (int)($_GET['view'] ?? 0);
$order = null; $items = [];
if ($view) {
    $st=$conn->prepare("SELECT o.*,c.name AS cname,c.email AS cemail,c.phone AS cphone,c.address AS caddress FROM orders o LEFT JOIN customers c ON o.customer_id=c.id WHERE o.id=? LIMIT 1");
    $st->bind_param('i',$view); $st->execute();
    $order=$st->get_result()->fetch_assoc(); $st->close();
    if ($order) {
        $ist=$conn->prepare("SELECT oi.*,p.image AS pimg FROM order_items oi LEFT JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
        $ist->bind_param('i',$view); $ist->execute();
        $items=$ist->get_result()->fetch_all(MYSQLI_ASSOC); $ist->close();
    }
}

$orders=$conn->query("SELECT o.id,o.reference,o.total_amount,o.status,o.payment_status,o.created_at,c.name AS cname FROM orders o LEFT JOIN customers c ON o.customer_id=c.id ORDER BY o.created_at DESC")->fetch_all(MYSQLI_ASSOC);

layout_head('Orders');
layout_flash();
?>
<div class="ph"><h1>Orders</h1><span class="ph-meta"><?= count($orders) ?> total</span></div>

<?php if ($order): ?>
<!-- Order Detail -->
<div style="margin-bottom:1.5rem;"><a href="orders.php" class="btn btn-ghost btn-sm">← All Orders</a></div>
<div style="display:grid;grid-template-columns:1fr 280px;gap:1.5rem;align-items:start;">
<div style="display:flex;flex-direction:column;gap:1.5rem;">
    <div class="panel">
        <div class="panel-title">Items — <?= e($order['reference']) ?></div>
        <?php if(empty($items)): ?><p style="color:var(--muted);font-style:italic;">No items.</p>
        <?php else: ?>
        <div class="tw" style="border:none;"><table>
            <thead><tr><th>Product</th><th>Qty</th><th>Unit</th><th>Subtotal</th></tr></thead>
            <tbody>
            <?php foreach($items as $it): $ip=UPLOAD_DIR.($it['pimg']??''); ?>
            <tr>
                <td style="display:flex;align-items:center;gap:.75rem;">
                    <?php if($it['pimg']&&file_exists($ip)): ?><img class="thumb" src="<?= UPLOAD_URL.e($it['pimg']) ?>" alt=""><?php else: ?><div class="thumb-nil">N/A</div><?php endif; ?>
                    <?= e($it['product_name']) ?>
                </td>
                <td><?= (int)$it['quantity'] ?></td>
                <td>GHS <?= number_format((float)$it['unit_price'],2) ?></td>
                <td>GHS <?= number_format((float)$it['subtotal'],2) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);">
            <?php if($order['discount']>0): ?><div style="display:flex;justify-content:space-between;padding:.4rem 0;font-size:.85rem;"><span style="color:var(--muted);">Discount</span><span style="color:var(--ok);">- GHS <?= number_format((float)$order['discount'],2) ?></span></div><?php endif; ?>
            <div style="display:flex;justify-content:space-between;padding:.4rem 0;font-family:var(--display);font-size:1.1rem;"><span>Total</span><span>GHS <?= number_format((float)$order['total_amount'],2) ?></span></div>
        </div>
        <?php endif; ?>
    </div>
    <div class="panel">
        <div class="panel-title">Customer</div>
        <?php foreach(['cname'=>'Name','cemail'=>'Email','cphone'=>'Phone','caddress'=>'Address'] as $k=>$l): ?>
        <div style="display:flex;justify-content:space-between;padding:.55rem 0;border-bottom:1px solid var(--border);font-size:.85rem;">
            <span style="color:var(--muted);font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;"><?= $l ?></span>
            <span><?= e($order[$k]??'—') ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="panel">
    <div class="panel-title">Update Order</div>
    <form method="POST" action="orders.php">
        <?php csrf_input(); ?>
        <input type="hidden" name="order_id" value="<?= $view ?>">
        <div class="fg"><label>Order Status</label><select name="status">
            <?php foreach(['pending','processing','delivered','cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $order['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select></div>
        <div class="fg"><label>Payment Status</label><select name="payment_status">
            <?php foreach(['unpaid','paid','refunded'] as $s): ?>
            <option value="<?= $s ?>" <?= $order['payment_status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select></div>
        <div class="fg"><label>Payment Method</label><div style="padding:.7rem .9rem;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:2px;font-size:.85rem;"><?= e($order['payment_method']??'Not specified') ?></div></div>
        <button type="submit" class="btn btn-gold" style="width:100%;">Save</button>
    </form>
    <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--border);">
        <div style="display:flex;justify-content:space-between;padding:.4rem 0;font-size:.78rem;"><span style="color:var(--muted);">Date</span><span><?= date('d M Y, H:i',strtotime($order['created_at'])) ?></span></div>
    </div>
</div>
</div>

<?php else: ?>
<!-- Orders List -->
<?php if(empty($orders)): ?><div class="empty"><p>No orders yet.</p></div>
<?php else: ?>
<div class="tw"><table>
    <thead><tr><th>Reference</th><th>Customer</th><th>Amount</th><th>Status</th><th>Payment</th><th>Date</th><th></th></tr></thead>
    <tbody>
    <?php foreach($orders as $o):
        $sb=match($o['status']){'pending'=>'b-warn','processing'=>'b-ok','delivered'=>'b-gold','cancelled'=>'b-err',default=>'b-grey'};
        $pb=$o['payment_status']==='paid'?'b-ok':'b-grey';
    ?>
    <tr>
        <td style="font-family:var(--display);letter-spacing:.05em;"><?= e($o['reference']) ?></td>
        <td><?= e($o['cname']??'Guest') ?></td>
        <td>GHS <?= number_format((float)$o['total_amount'],2) ?></td>
        <td><span class="badge <?= $sb ?>"><?= $o['status'] ?></span></td>
        <td><span class="badge <?= $pb ?>"><?= $o['payment_status'] ?></span></td>
        <td style="color:var(--muted);font-size:.78rem;"><?= date('d M Y',strtotime($o['created_at'])) ?></td>
        <td><a href="orders.php?view=<?= $o['id'] ?>" class="btn btn-ghost btn-sm">View</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?php endif; ?>
<?php endif; ?>

<?php layout_end(); ?>
