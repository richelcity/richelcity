<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'layout.php';
auth_guard();

if (isset($_GET['delete'])) {
    $did=(int)$_GET['delete'];
    $st=$conn->prepare("SELECT image FROM sliders WHERE id=?");
    $st->bind_param('i',$did); $st->execute();
    $img=$st->get_result()->fetch_row()[0]??''; $st->close();
    $d=$conn->prepare("DELETE FROM sliders WHERE id=?");
    $d->bind_param('i',$did);
    if($d->execute()) delete_image($img);
    flash($d->execute()?'ok':'error','Slider deleted.');
    $d->close(); header('Location: sliders.php'); exit;
}
if (isset($_GET['toggle'])) {
    $tid=(int)$_GET['toggle'];
    $conn->query("UPDATE sliders SET status=IF(status='active','inactive','active') WHERE id=$tid");
    header('Location: sliders.php'); exit;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!csrf_verify()) { flash('error','Invalid request.'); header('Location: sliders.php'); exit; }
    $sid     = (int)($_POST['slider_id'] ?? 0);
    $caption = trim($_POST['caption']     ?? '');
    $sub     = trim($_POST['sub_caption'] ?? '');
    $link    = trim($_POST['link_url']    ?? '');
    $order   = max(1,(int)($_POST['display_order']??1));
    $status  = in_array($_POST['status']??'',['active','inactive']) ? $_POST['status'] : 'active';
    if ($caption==='') { flash('error','Caption required.'); header('Location: sliders.php'); exit; }

    $newImg = null;
    if (!empty($_FILES['image']['name'])) {
        $up=upload_image($_FILES['image'],'slider');
        if(!$up['ok']) { flash('error',$up['error']); header('Location: sliders.php'); exit; }
        $newImg=$up['filename'];
    }

    if ($sid) {
        // Edit
        $old=$conn->query("SELECT image FROM sliders WHERE id=$sid")->fetch_row()[0]??'';
        $img=$newImg??$old;
        $st=$conn->prepare("UPDATE sliders SET caption=?,sub_caption=?,link_url=?,display_order=?,status=?,image=? WHERE id=?");
        $st->bind_param('sssissi',$caption,$sub,$link,$order,$status,$img,$sid);
        if($st->execute()) { if($newImg&&$old) delete_image($old); flash('ok','Slider updated.'); }
        else { if($newImg) delete_image($newImg); flash('error','DB error.'); }
        $st->close();
    } else {
        // Add — image required
        if (!$newImg) { flash('error','Image required.'); header('Location: sliders.php'); exit; }
        $st=$conn->prepare("INSERT INTO sliders (image,caption,sub_caption,link_url,display_order,status) VALUES (?,?,?,?,?,?)");
        $st->bind_param('ssssis',$newImg,$caption,$sub,$link,$order,$status);
        if($st->execute()) flash('ok',"Slide \"$caption\" added.");
        else { delete_image($newImg); flash('error','DB error.'); }
        $st->close();
    }
    header('Location: sliders.php'); exit;
}

$sliders=$conn->query("SELECT * FROM sliders ORDER BY display_order,created_at DESC")->fetch_all(MYSQLI_ASSOC);
$edit=null;
if(isset($_GET['edit'])) { foreach($sliders as $s) if($s['id']==(int)$_GET['edit']){$edit=$s;break;} }

layout_head('Sliders');
layout_flash();
?>
<div class="ph"><h1>Sliders</h1><span class="ph-meta"><?= count($sliders) ?> slides</span></div>
<div class="cgrid">
<div class="panel">
    <div class="panel-title"><?= $edit?'Edit':'Add' ?> Slide</div>
    <form method="POST" enctype="multipart/form-data">
        <?php csrf_input(); ?>
        <input type="hidden" name="slider_id" value="<?= $edit?(int)$edit['id']:0 ?>">
        <div class="fg"><label>Caption *</label><input type="text" name="caption" required value="<?= e($edit['caption']??'') ?>"></div>
        <div class="fg"><label>Sub-caption</label><input type="text" name="sub_caption" value="<?= e($edit['sub_caption']??'') ?>"></div>
        <div class="fg"><label>Link URL</label><input type="text" name="link_url" placeholder="https://…" value="<?= e($edit['link_url']??'') ?>"></div>
        <div class="frow">
            <div class="fg"><label>Order</label><input type="number" name="display_order" min="1" value="<?= (int)($edit['display_order']??1) ?>"></div>
            <div class="fg"><label>Status</label><select name="status">
                <option value="active" <?= ($edit['status']??'active')==='active'?'selected':'' ?>>Active</option>
                <option value="inactive" <?= ($edit['status']??'')==='inactive'?'selected':'' ?>>Inactive</option>
            </select></div>
        </div>
        <div class="fg"><label>Image<?= $edit?'':' *' ?></label>
            <?php if(!empty($edit['image'])&&file_exists(UPLOAD_DIR.$edit['image'])): ?>
            <img src="<?= UPLOAD_URL.e($edit['image']) ?>" style="width:100%;aspect-ratio:16/7;object-fit:cover;border-radius:2px;border:1px solid var(--border);margin-bottom:.6rem;">
            <?php endif; ?>
            <label class="file-lbl" for="si">📁 <span id="sfn"><?= $edit?'Replace image…':'Choose file…' ?></span></label>
            <input type="file" id="si" name="image" accept="image/jpeg,image/png,image/webp" <?= $edit?'':'required' ?>>
            <img id="sprev" style="display:none;width:100%;aspect-ratio:16/7;object-fit:cover;border-radius:2px;border:1px solid var(--gold);margin-top:.6rem;" alt="">
            <p class="hint">JPG/PNG/WebP · max 4MB<?= $edit?' · Leave blank to keep current':'' ?></p>
        </div>
        <div style="display:flex;gap:.75rem;">
            <button type="submit" class="btn btn-gold"><?= $edit?'Save Changes':'Add Slide' ?></button>
            <?php if($edit): ?><a href="sliders.php" class="btn btn-ghost">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>
<div>
<?php if(empty($sliders)): ?><div class="empty"><p>No slides yet.</p></div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1rem;">
<?php foreach($sliders as $s): $ip=UPLOAD_DIR.$s['image']; ?>
<div style="background:var(--panel);border:1px solid var(--border);border-radius:2px;overflow:hidden;">
    <?php if($s['image']&&file_exists($ip)): ?><img src="<?= UPLOAD_URL.e($s['image']) ?>" style="width:100%;aspect-ratio:16/7;object-fit:cover;"><?php else: ?><div style="width:100%;aspect-ratio:16/7;background:var(--surface);display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:.7rem;">No image</div><?php endif; ?>
    <div style="padding:1rem;">
        <div style="font-family:var(--display);font-size:1rem;letter-spacing:.04em;margin-bottom:.3rem;"><?= e($s['caption']) ?></div>
        <?php if($s['sub_caption']): ?><div style="font-family:var(--serif);font-style:italic;font-size:.8rem;color:var(--muted);margin-bottom:.5rem;"><?= e($s['sub_caption']) ?></div><?php endif; ?>
        <div style="display:flex;gap:.4rem;align-items:center;margin-bottom:.75rem;">
            <span class="badge b-gold">Order <?= (int)$s['display_order'] ?></span>
            <span class="badge <?= $s['status']==='active'?'b-ok':'b-grey' ?>"><?= $s['status'] ?></span>
        </div>
        <div class="acts">
            <a href="sliders.php?edit=<?= $s['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
            <a href="sliders.php?toggle=<?= $s['id'] ?>" class="btn btn-ghost btn-sm">Toggle</a>
            <a href="sliders.php?delete=<?= $s['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Del</a>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div></div>
<script>document.getElementById('si').onchange=function(){var f=this.files[0];if(!f)return;document.getElementById('sfn').textContent=f.name;var r=new FileReader();r.onload=function(e){var p=document.getElementById('sprev');p.src=e.target.result;p.style.display='block';};r.readAsDataURL(f);};</script>
<?php layout_end(); ?>
