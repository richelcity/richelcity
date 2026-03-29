<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'layout.php';
auth_guard();

// ── Delete ────────────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $st  = $conn->prepare("SELECT image FROM products WHERE id=?");
    $st->bind_param('i',$did); $st->execute();
    $img = $st->get_result()->fetch_row()[0] ?? ''; $st->close();
    $d   = $conn->prepare("DELETE FROM products WHERE id=?");
    $d->bind_param('i',$did);
    if ($d->execute()) { delete_image($img); flash('ok','Product deleted.'); }
    else flash('error','Could not delete product.');
    $d->close();
    header('Location: products.php'); exit;
}

// ── Upload ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!csrf_verify()) { flash('error','Invalid request.'); header('Location: products.php'); exit; }

    $name  = trim($_POST['name']  ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock']   ?? 0);
    $catId = ($_POST['category_id'] ?? '') !== '' ? (int)$_POST['category_id'] : null;
    $status= in_array($_POST['status']??'',['available','out_of_stock','archived']) ? $_POST['status'] : 'available';
    $feat  = isset($_POST['featured']) ? 1 : 0;

    if ($name==='' || $price<=0) { flash('error','Name and a valid price are required.'); header('Location: products.php'); exit; }

    $up = upload_image($_FILES['image']??[], 'prod');
    if (!$up['ok']) { flash('error',$up['error']); header('Location: products.php'); exit; }

    $img  = $up['filename'];
    $slug = unique_slug($conn,'products',$name);

    $st = $conn->prepare("INSERT INTO products (name,slug,description,price,image,category_id,stock,status,featured) VALUES (?,?,?,?,?,?,?,?,?)");
    $st->bind_param('sssdsissi',$name,$slug,$desc,$price,$img,$catId,$stock,$status,$feat);
    if ($st->execute()) flash('ok',"\"$name\" added.");
    else { delete_image($img); flash('error','DB error: '.$conn->error); }
    $st->close();
    header('Location: products.php'); exit;
}

$cats = $conn->query("SELECT id,name FROM categories WHERE status='active' ORDER BY display_order")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("SELECT p.*,c.name AS cname FROM products p LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.created_at DESC")->fetch_all(MYSQLI_ASSOC);

layout_head('Products');
layout_flash();
?>
<div class="ph">
    <h1>Products</h1>
    <span class="ph-meta"><?= count($products) ?> items</span>
</div>
<div class="cgrid">
<div class="panel">
    <div class="panel-title">Add Product</div>
    <form method="POST" enctype="multipart/form-data" novalidate>
        <?php csrf_input(); ?>
        <div class="fg"><label>Name *</label><input type="text" name="name" required></div>
        <div class="frow">
            <div class="fg"><label>Price (GHS) *</label><input type="number" name="price" min="0" step="0.01"></div>
            <div class="fg"><label>Stock</label><input type="number" name="stock" min="0" value="0"></div>
        </div>
        <div class="fg"><label>Category</label>
            <select name="category_id">
                <option value="">— None —</option>
                <?php foreach($cats as $c): ?>
                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fg"><label>Description</label><textarea name="description" placeholder="Optional…"></textarea></div>
        <div class="fg"><label>Status</label>
            <select name="status">
                <option value="available">Available</option>
                <option value="out_of_stock">Out of Stock</option>
                <option value="archived">Archived</option>
            </select>
        </div>
        <div class="fg">
            <label style="display:flex;align-items:center;gap:.5rem;text-transform:none;letter-spacing:0;font-size:.82rem;cursor:pointer;">
                <input type="checkbox" name="featured" value="1" style="width:auto;accent-color:var(--gold);"> Feature on homepage
            </label>
        </div>
        <div class="fg"><label>Image * (JPG/PNG/WebP, max 4MB)</label>
            <label class="file-lbl" for="img">📁 <span id="fn">Choose file…</span></label>
            <input type="file" id="img" name="image" accept="image/jpeg,image/png,image/webp" required>
            <img id="prev" style="display:none;width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:2px;border:1px solid var(--border);margin-top:.6rem;" alt="">
        </div>
        <button type="submit" class="btn btn-gold" style="width:100%;">Add Product</button>
    </form>
</div>
<div>
<?php if(empty($products)): ?><div class="empty"><p>No products yet.</p></div>
<?php else: ?>
<div class="tw"><table>
    <thead><tr><th>Img</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach($products as $p):
        $sb=match($p['status']){'available'=>'b-ok','out_of_stock'=>'b-err',default=>'b-grey'};
        $ip=UPLOAD_DIR.$p['image'];
    ?>
    <tr>
        <td><?php if($p['image']&&file_exists($ip)): ?><img class="thumb" src="<?= UPLOAD_URL.e($p['image']) ?>" alt=""><?php else: ?><div class="thumb-nil">N/A</div><?php endif; ?></td>
        <td><?= e($p['name']) ?><?= $p['featured']?' <span class="badge b-gold">Featured</span>':'' ?></td>
        <td><?= e($p['cname']??'—') ?></td>
        <td>GHS <?= number_format((float)$p['price'],2) ?></td>
        <td><?= (int)$p['stock'] ?></td>
        <td><span class="badge <?= $sb ?>"><?= e($p['status']) ?></span></td>
        <td><div class="acts">
            <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
            <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete \'<?= e(addslashes($p['name'])) ?>\'?')">Del</a>
        </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?php endif; ?>
</div>
</div>
<script>
document.getElementById('img').onchange=function(){
    var f=this.files[0]; if(!f) return;
    document.getElementById('fn').textContent=f.name;
    var r=new FileReader(); r.onload=function(e){var p=document.getElementById('prev');p.src=e.target.result;p.style.display='block';};r.readAsDataURL(f);
};
</script>
<?php layout_end(); ?>
