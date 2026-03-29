<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'layout.php';
auth_guard();

if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $conn->query("UPDATE products SET category_id=NULL WHERE category_id=$did");
    $st = $conn->prepare("DELETE FROM categories WHERE id=?");
    $st->bind_param('i',$did);
    flash($st->execute()?'ok':'error', $st->execute()?'Deleted.':'Could not delete.');
    $st->close(); header('Location: categories.php'); exit;
}
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $conn->query("UPDATE categories SET status=IF(status='active','inactive','active') WHERE id=$tid");
    header('Location: categories.php'); exit;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!csrf_verify()) { flash('error','Invalid request.'); header('Location: categories.php'); exit; }
    $cid    = (int)($_POST['cat_id'] ?? 0);
    $name   = trim($_POST['name'] ?? '');
    $order  = max(1,(int)($_POST['display_order']??1));
    $status = in_array($_POST['status']??'',['active','inactive']) ? $_POST['status'] : 'active';
    if ($name==='') { flash('error','Name required.'); header('Location: categories.php'); exit; }
    $slug = unique_slug($conn,'categories',$name,$cid);
    $img  = null;
    if (!empty($_FILES['image']['name'])) { $up=upload_image($_FILES['image'],'cat'); if($up['ok']) $img=$up['filename']; }

    if ($cid) {
        if ($img) { $st=$conn->prepare("UPDATE categories SET name=?,slug=?,display_order=?,status=?,image=? WHERE id=?"); $st->bind_param('ssissi',$name,$slug,$order,$status,$img,$cid); }
        else      { $st=$conn->prepare("UPDATE categories SET name=?,slug=?,display_order=?,status=? WHERE id=?"); $st->bind_param('ssisi',$name,$slug,$order,$status,$cid); }
        flash($st->execute()?'ok':'error',$st->execute()?'Updated.':'DB error.');
    } else {
        $st=$conn->prepare("INSERT INTO categories (name,slug,display_order,status,image) VALUES (?,?,?,?,?)");
        $st->bind_param('ssiss',$name,$slug,$order,$status,$img);
        flash($st->execute()?'ok':'error',$st->execute()?"\"$name\" added.":'DB error.');
    }
    $st->close(); header('Location: categories.php'); exit;
}

$cats = $conn->query("SELECT c.*,(SELECT COUNT(*) FROM products p WHERE p.category_id=c.id) AS pc FROM categories c ORDER BY c.display_order")->fetch_all(MYSQLI_ASSOC);
$edit = null;
if (isset($_GET['edit'])) { foreach($cats as $c) if($c['id']==(int)$_GET['edit']){$edit=$c;break;} }

layout_head('Categories');
layout_flash();
?>
<div class="ph"><h1>Categories</h1><span class="ph-meta"><?= count($cats) ?> total</span></div>
<div class="cgrid">
<div class="panel">
    <div class="panel-title"><?= $edit?'Edit':'Add' ?> Category</div>
    <form method="POST" enctype="multipart/form-data">
        <?php csrf_input(); ?>
        <input type="hidden" name="cat_id" value="<?= $edit?(int)$edit['id']:0 ?>">
        <div class="fg"><label>Name *</label><input type="text" name="name" required value="<?= e($edit['name']??'') ?>"></div>
        <div class="frow">
            <div class="fg"><label>Order</label><input type="number" name="display_order" min="1" value="<?= (int)($edit['display_order']??1) ?>"></div>
            <div class="fg"><label>Status</label><select name="status">
                <option value="active" <?= ($edit['status']??'active')==='active'?'selected':'' ?>>Active</option>
                <option value="inactive" <?= ($edit['status']??'')==='inactive'?'selected':'' ?>>Inactive</option>
            </select></div>
        </div>
        <div class="fg"><label>Image (optional)</label>
            <?php if(!empty($edit['image'])&&file_exists(UPLOAD_DIR.$edit['image'])): ?>
            <img src="<?= UPLOAD_URL.e($edit['image']) ?>" style="width:100%;aspect-ratio:3/2;object-fit:cover;border-radius:2px;border:1px solid var(--border);margin-bottom:.6rem;">
            <?php endif; ?>
            <label class="file-lbl" for="ci">📁 <span id="cfn">Choose image…</span></label>
            <input type="file" id="ci" name="image" accept="image/jpeg,image/png,image/webp">
        </div>
        <div style="display:flex;gap:.75rem;">
            <button type="submit" class="btn btn-gold"><?= $edit?'Save':'Add Category' ?></button>
            <?php if($edit): ?><a href="categories.php" class="btn btn-ghost">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>
<div>
<?php if(empty($cats)): ?><div class="empty"><p>No categories yet.</p></div>
<?php else: ?>
<div class="tw"><table>
    <thead><tr><th>Name</th><th>Slug</th><th>Order</th><th>Products</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach($cats as $c): ?>
    <tr>
        <td><?= e($c['name']) ?></td>
        <td style="color:var(--muted);font-size:.78rem;"><?= e($c['slug']) ?></td>
        <td><?= (int)$c['display_order'] ?></td>
        <td><?= (int)$c['pc'] ?></td>
        <td><span class="badge <?= $c['status']==='active'?'b-ok':'b-grey' ?>"><?= $c['status'] ?></span></td>
        <td><div class="acts">
            <a href="categories.php?edit=<?= $c['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
            <a href="categories.php?toggle=<?= $c['id'] ?>" class="btn btn-ghost btn-sm">Toggle</a>
            <a href="categories.php?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Del</a>
        </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?php endif; ?>
</div></div>
<script>document.getElementById('ci').onchange=function(){document.getElementById('cfn').textContent=this.files[0]?.name||'Choose image…';};</script>
<?php layout_end(); ?>
