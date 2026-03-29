<?php
session_start();
require_once '_shared.php';
auth_guard();
include '../config.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: orders.php'); exit; }

// ── Update status / payment ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $_SESSION['flash'] = ['type'=>'error','msg'=>'Invalid request.'];
        header("Location: order_detail.php?id={$id}"); exit;
    }
    $newStatus  = $_POST['status']         ?? '';
    $newPayment = $_POST['payment_status'] ?? '';
    $validS = ['pending','processing','delivered','cancelled'];
    $validP = ['unpaid','paid','refunded'];
    if (in_array($newStatus, $validS) && in_array($newPayment, $validP)) {
        $stmt = $conn->prepare("UPDATE orders SET status=?, payment_status=? WHERE id=?");
        $stmt->bind_param('ssi', $newStatus, $newPayment, $id);
        $_SESSION['flash'] = $stmt->execute()
            ? ['type'=>'success','msg'=>'Order updated.']
            : ['type'=>'error',  'msg'=>'Update failed.'];
        $stmt->close();
    }
    header("Location: order_detail.php?id={$id}"); exit;
}

// ── Fetch order ───────────────────────────────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT o.*, c.name AS customer_name, c.email AS customer_email,
            c.phone AS customer_phone, c.address AS customer_address
     FROM orders o LEFT JOIN customers c ON o.customer_id=c.id
     WHERE o.id=? LIMIT 1"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) { header('Location: orders.php'); exit; }

// ── Fetch items ───────────────────────────────────────────────────────────────
$istmt = $conn->prepare(
    "SELECT oi.*, p.image AS product_image
     FROM order_items oi LEFT JOIN products p ON oi.product_id=p.id
     WHERE oi.order_id=?"
);
$istmt->bind_param('i', $id);
$istmt->execute();
$items = $istmt->get_result()->fetch_all(MYSQLI_ASSOC);
$istmt->close();

admin_head("Order {$order['reference']}");
admin_sidebar('orders');
admin_flash();
?>
<style>
.order-grid{display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;}
.drow{display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--border);font-size:.85rem;}
.drow:last-child{border-bottom:none;}
.dlabel{color:var(--muted);font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;}
.dvalue{color:var(--cream);text-align:right;}
.total-row{font-family:var(--display);font-size:1.1rem;letter-spacing:.05em;padding:.8rem 0;}
.psm{width:44px;height:44px;object-fit:cover;border-radius:2px;border:1px solid var(--border);}
@media(max-width:900px){.order-grid{grid-template-columns:1fr;}}
</style>

<div class="page-header">
    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="orders.php" class="btn btn-ghost" style="padding:.4rem .9rem;font-size:.68rem;">Back</a>
        <h1 class="page-title"><?= htmlspecialchars($order['reference']) ?></h1>
    </div>
    <?php $sb=match($order['status']){'pending'=>'badge-warn','processing'=>'badge-green','delivered'=>'badge-gold','cancelled'=>'badge-red',default=>'badge-grey'}; ?>
    <span class="badge <?=$sb?>" style="font-size:.75rem;padding:.3rem .8rem;"><?=$order['status']?></span>
</div>

<div class="order-grid">
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        <div class="panel">
            <div class="panel-title">Order Items</div>
            <?php if(empty($items)): ?>
            <p style="color:var(--muted);font-style:italic;font-family:var(--serif);">No items recorded.</p>
            <?php else: ?>
            <div class="table-wrap" style="border:none;">
                <table>
                    <thead><tr><th>Item</th><th>Qty</th><th>Unit</th><th>Subtotal</th></tr></thead>
                    <tbody>
                    <?php foreach($items as $item): $ip=UPLOAD_DIR.$item['product_image']; ?>
                    <tr>
                        <td style="display:flex;align-items:center;gap:.75rem;">
                            <?php if($item['product_image']&&file_exists($ip)): ?>
                            <img class="psm" src="../<?=UPLOAD_URL.htmlspecialchars($item['product_image'])?>" alt="">
                            <?php else: ?><div class="psm" style="background:var(--surface);border:1px dashed var(--border);"></div><?php endif; ?>
                            <?=htmlspecialchars($item['product_name'])?>
                        </td>
                        <td><?=(int)$item['quantity']?></td>
                        <td>GHS <?=number_format((float)$item['unit_price'],2)?></td>
                        <td>GHS <?=number_format((float)$item['subtotal'],2)?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);">
                <div class="drow"><span class="dlabel">Subtotal</span><span class="dvalue">GHS <?=number_format((float)$order['subtotal'],2)?></span></div>
                <?php if($order['discount']>0): ?>
                <div class="drow"><span class="dlabel">Discount</span><span class="dvalue" style="color:var(--success);">- GHS <?=number_format((float)$order['discount'],2)?></span></div>
                <?php endif; ?>
                <div class="drow total-row"><span>Total</span><span>GHS <?=number_format((float)$order['total_amount'],2)?></span></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel-title">Customer</div>
            <div class="drow"><span class="dlabel">Name</span><span class="dvalue"><?=htmlspecialchars($order['customer_name']??'Guest')?></span></div>
            <div class="drow"><span class="dlabel">Email</span><span class="dvalue"><?=htmlspecialchars($order['customer_email']??'—')?></span></div>
            <div class="drow"><span class="dlabel">Phone</span><span class="dvalue"><?=htmlspecialchars($order['customer_phone']??'—')?></span></div>
            <div class="drow"><span class="dlabel">Address</span><span class="dvalue" style="max-width:220px;text-align:right;"><?=nl2br(htmlspecialchars($order['customer_address']??'—'))?></span></div>
            <?php if($order['notes']): ?><div class="drow"><span class="dlabel">Notes</span><span class="dvalue" style="max-width:220px;text-align:right;"><?=nl2br(htmlspecialchars($order['notes']))?></span></div><?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-title">Update Order</div>
        <form method="POST" action="order_detail.php?id=<?=$id?>">
            <?php csrf_field(); ?>
            <div class="form-group">
                <label>Order Status</label>
                <select name="status">
                    <?php foreach(['pending','processing','delivered','cancelled'] as $s): ?>
                    <option value="<?=$s?>" <?=$order['status']===$s?'selected':''?>><?=ucfirst($s)?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Payment Status</label>
                <select name="payment_status">
                    <?php foreach(['unpaid','paid','refunded'] as $p): ?>
                    <option value="<?=$p?>" <?=$order['payment_status']===$p?'selected':''?>><?=ucfirst($p)?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Payment Method</label>
                <p style="color:var(--cream);font-size:.85rem;padding:.7rem .9rem;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:2px;">
                    <?=htmlspecialchars($order['payment_method']??'Not specified')?>
                </p>
            </div>
            <button type="submit" class="btn btn-gold" style="width:100%;margin-top:.5rem;">Save Changes</button>
        </form>
        <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--border);">
            <div class="drow"><span class="dlabel">Order Date</span><span class="dvalue"><?=date('d M Y, H:i',strtotime($order['created_at']))?></span></div>
            <div class="drow"><span class="dlabel">Last Updated</span><span class="dvalue"><?=date('d M Y, H:i',strtotime($order['updated_at']))?></span></div>
        </div>
    </div>
</div>

<?php admin_foot(); ?>