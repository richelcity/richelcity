<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'layout.php';
auth_guard();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) { flash('error','Invalid ID.'); header('Location: products.php'); exit; }

$st = $conn->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
$st->bind_param('i',$id); $st->execute();
$p  = $st->get_result()->fetch_assoc(); $st->close();
if (!$p) { flash('error','Product not found.'); header('Location: products.php'); exit; }

$cats = $conn->query("SELECT id,name FROM categories WHERE status='active' ORDER BY display_order")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!csrf_verify()) { flash('error','Invalid request.'); header("Location: edit_product.php?id=$id"); exit; }

    $name   = trim($_POST['name']  ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $price  = (float)($_POST['price']  ?? 0);
    $sprice = $_POST['sale_price']!=='' ? (float)$_POST['sale_price'] : null;
    $stock  = (int)($_POST['stock']    ?? 0);
    $catId  = ($_POST['category_id']??'')!=='' ? (int)$_POST['category_id'] : null;
    $status = in_array($_POST['status']??'',['available','out_of_stock','archived']) ? $_POST['status'] : 'available';
    $feat   = isset($_POST['featured']) ? 1 : 0;

    if ($name==='' || $price<=0) { flash('error','Name and valid price required.'); header("Location: edit_product.php?id=$id"); exit; }

    $newImg = null;
    if (!empty($_FILES['image']['name'])) {
        $up = upload_image($_FILES['image'],'prod');
        if (!$up['ok']) { flash('error',$up['error']); header("Location: edit_product.php?id=$id"); exit; }
        $newImg = $up['filename'];
    }

    $slug = $name !== $p['name'] ? unique_slug($conn,'products',$name,$id) : $p['slug'];
    $img  = $newImg ?? $p['image'];

    $st = $conn->prepare("UPDATE products SET name=?,slug=?,description=?,price=?,sale_price=?,image=?,category_id=?,stock=?,status=?,featured=? WHERE id=?");
    $st->bind_param('sssddsisiii',$name,$slug,$desc,$price,$sprice,$img,$catId,$stock,$status,$feat,$id);
    if ($st->execute()) {
        if ($newImg && $p['image']) delete_image($p['image']);
        flash('ok',"\"$name\" updated.");
        header('Location: products.php'); exit;
    } else {
        if ($newImg) delete_image($newImg);
        flash('error','DB error: '.$conn->error);
    }
    $st->close();
}

layout_head('Edit Product');
layout_flash();
?>
<div class="ph">
    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="products.php" class="btn btn-ghost btn-sm">← Back</a>
        <h1>Edit Product</h1>
    </div>
</div>
<div class="panel" style="max-width:700px;">
<form method="POST" enctype="multipart/form-data" novalidate>
    <?php csrf_input(); ?>
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="fg"><label>Name *</label><input type="text" name="name" required value="<?= e($p['name']) ?>"></div>
    <div class="fg"><label>Description</label><textarea name="description"><?= e($p['description']) ?></textarea></div>
    <div class="frow">
        <div class="fg"><label>Price (GHS) *</label><input type="number" name="price" min="0" step="0.01" value="<?= e($p['price']) ?>"></div>
        <div class="fg"><label>Sale Price</label><input type="number" name="sale_price" min="0" step="0.01" value="<?= e($p['sale_price']??'') ?>" placeholder="Leave blank if none"></div>
    </div>
    <div class="frow">
        <div class="fg"><label>Stock</label><input type="number" name="stock" min="0" value="<?= (int)$p['stock'] ?>"></div>
        <div class="fg"><label>Category</label>
            <select name="category_id">
                <option value="">— None —</option>
                <?php foreach($cats as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $p['category_id']==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="frow">
        <div class="fg"><label>Status</label>
            <select name="status">
                <?php foreach(['available','out_of_stock','archived'] as $s): ?>
                <option value="<?= $s ?>" <?= $p['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fg" style="display:flex;align-items:center;padding-top:1.5rem;">
            <label style="display:flex;align-items:center;gap:.5rem;text-transform:none;letter-spacing:0;font-size:.82rem;cursor:pointer;">
                <input type="checkbox" name="featured" value="1" style="width:auto;accent-color:var(--gold);" <?= $p['featured']?'checked':'' ?>> Feature on homepage
            </label>
        </div>
    </div>
    <div class="fg"><label>Replace Image</label>
        <?php if($p['image']&&file_exists(UPLOAD_DIR.$p['image'])): ?>
        <img src="<?= UPLOAD_URL.e($p['image']) ?>" style="width:100%;max-height:180px;object-fit:cover;border-radius:2px;border:1px solid var(--border);margin-bottom:.6rem;">
        <?php endif; ?>
        <label class="file-lbl" for="img2">📁 <span id="fn2">Choose replacement…</span></label>
        <input type="file" id="img2" name="image" accept="image/jpeg,image/png,image/webp">
        <img id="prev2" style="display:none;width:100%;max-height:160px;object-fit:cover;border-radius:2px;border:1px solid var(--gold);margin-top:.6rem;" alt="">
        <p class="hint">Leave blank to keep current image.</p>
    </div>
    <div style="display:flex;gap:1rem;margin-top:1rem;">
        <button type="submit" class="btn btn-gold">Save Changes</button>
        <a href="products.php" class="btn btn-ghost">Cancel</a>
    </div>
</form>
</div>
<script>
document.getElementById('img2').onchange=function(){
    var f=this.files[0]; if(!f) return;
    document.getElementById('fn2').textContent=f.name;
    var r=new FileReader(); r.onload=function(e){var p=document.getElementById('prev2');p.src=e.target.result;p.style.display='block';};r.readAsDataURL(f);
};
</script>
<?php layout_end(); ?>
