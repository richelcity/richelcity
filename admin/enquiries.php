<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'layout.php';
auth_guard();

if (isset($_GET['delete'])) {
    $did=(int)$_GET['delete'];
    $st=$conn->prepare("DELETE FROM enquiries WHERE id=?");
    $st->bind_param('i',$did); flash($st->execute()?'ok':'error','Enquiry deleted.'); $st->close();
    header('Location: enquiries.php'); exit;
}

$detail=null;
if (isset($_GET['id'])) {
    $did=(int)$_GET['id'];
    $st=$conn->prepare("SELECT * FROM enquiries WHERE id=? LIMIT 1");
    $st->bind_param('i',$did); $st->execute();
    $detail=$st->get_result()->fetch_assoc(); $st->close();
    if ($detail && !$detail['is_read']) {
        $conn->query("UPDATE enquiries SET is_read=1 WHERE id=$did");
        $detail['is_read']=1;
    }
}

$all=$conn->query("SELECT * FROM enquiries ORDER BY is_read ASC, created_at DESC")->fetch_all(MYSQLI_ASSOC);
$unread=count(array_filter($all,fn($e)=>!$e['is_read']));

layout_head('Enquiries');
layout_flash();
?>
<style>
.elay{display:grid;grid-template-columns:300px 1fr;gap:1.5rem;align-items:start;}
.elist{background:var(--panel);border:1px solid var(--border);border-radius:2px;overflow:hidden;}
.eitem{display:block;padding:.9rem 1.1rem;border-bottom:1px solid var(--border);transition:background var(--t);position:relative;}
.eitem:last-child{border-bottom:none;}
.eitem:hover{background:rgba(255,255,255,.03);}
.eitem.on{background:rgba(201,168,76,.06);border-left:2px solid var(--gold);}
.eitem.unread .ename::after{content:'';width:7px;height:7px;background:var(--gold);border-radius:50%;display:inline-block;margin-left:6px;vertical-align:middle;}
.ename{font-size:.85rem;color:var(--cream);margin-bottom:.2rem;}
.epreview{font-size:.75rem;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.edate{font-size:.65rem;color:var(--muted);margin-top:.25rem;}
@media(max-width:900px){.elay{grid-template-columns:1fr;}}
</style>

<div class="ph"><h1>Enquiries</h1><span class="ph-meta"><?= $unread ?> unread · <?= count($all) ?> total</span></div>

<?php if(empty($all)): ?><div class="empty"><p>No enquiries yet.</p></div>
<?php else: ?>
<div class="elay">
<div class="elist">
    <?php foreach($all as $e):
        $on=$detail&&$detail['id']===$e['id']?'on':'';
        $ur=$e['is_read']?'':'unread';
    ?>
    <a href="enquiries.php?id=<?= $e['id'] ?>" class="eitem <?= $on ?> <?= $ur ?>">
        <div class="ename"><?= e($e['name']) ?></div>
        <div class="epreview"><?= e(mb_substr($e['message'],0,60)) ?></div>
        <div class="edate"><?= date('d M Y · H:i',strtotime($e['created_at'])) ?></div>
    </a>
    <?php endforeach; ?>
</div>

<?php if($detail): ?>
<div class="panel">
    <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid var(--border);">
        <?php foreach(['name'=>'From','email'=>'Email','phone'=>'Phone'] as $k=>$l): if(!$detail[$k]) continue; ?>
        <div><div style="font-size:.62rem;letter-spacing:.15em;text-transform:uppercase;color:var(--muted);margin-bottom:.2rem;"><?= $l ?></div>
        <?php if($k==='email'): ?><a href="mailto:<?= e($detail[$k]) ?>" style="color:var(--gold);"><?= e($detail[$k]) ?></a>
        <?php else: ?><div style="font-size:.88rem;"><?= e($detail[$k]) ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
        <div><div style="font-size:.62rem;letter-spacing:.15em;text-transform:uppercase;color:var(--muted);margin-bottom:.2rem;">Received</div>
        <div style="font-size:.78rem;color:var(--muted);"><?= date('d M Y, H:i',strtotime($detail['created_at'])) ?></div></div>
    </div>
    <div style="font-family:var(--serif);font-style:italic;font-size:1.05rem;line-height:1.75;white-space:pre-wrap;"><?= e($detail['message']) ?></div>
    <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--border);">
        <?php if($detail['email']): ?><a href="mailto:<?= e($detail['email']) ?>?subject=Re: Your Enquiry" class="btn btn-gold">Reply</a><?php endif; ?>
        <?php if($detail['phone']): ?><a href="tel:<?= e($detail['phone']) ?>" class="btn btn-ghost">Call</a><?php endif; ?>
        <a href="enquiries.php?delete=<?= $detail['id'] ?>" class="btn btn-danger" style="margin-left:auto;" onclick="return confirm('Delete?')">Delete</a>
    </div>
</div>
<?php else: ?>
<div style="display:flex;align-items:center;justify-content:center;min-height:200px;">
    <p style="font-family:var(--serif);font-style:italic;color:var(--muted);">Select an enquiry to read it.</p>
</div>
<?php endif; ?>
</div>
<?php endif; ?>

<?php layout_end(); ?>
