<?php
// layout.php — call layout_head(), layout_nav(), layout_end() in each page

function layout_head(string $title): void {
    global $conn;
    $unread = 0;
    if ($conn) {
        $r = $conn->query("SELECT COUNT(*) FROM enquiries WHERE is_read=0");
        if ($r) $unread = (int)$r->fetch_row()[0];
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> — RichelCity Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&family=Cormorant+Garamond:ital,wght@1,300;1,400&display=swap" rel="stylesheet">
<style>
:root{
    --ink:#0e0b08; --surface:#141210; --panel:#1c1916;
    --border:rgba(255,255,255,.07); --cream:#f5f0e8;
    --gold:#c9a84c; --rust:#b5451b; --muted:#6b6460;
    --ok:#4caf7d; --err:#e05252; --warn:#e8a838;
    --serif:'Cormorant Garamond',serif;
    --display:'Bebas Neue',sans-serif;
    --body:'DM Sans',sans-serif;
    --t:.25s ease;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{background:var(--ink);color:var(--cream);font-family:var(--body);font-weight:300;min-height:100vh;display:flex;}
a{color:inherit;text-decoration:none;}
img{display:block;max-width:100%;}

/* Sidebar */
.sb{width:220px;flex-shrink:0;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:2rem 1.5rem;position:sticky;top:0;height:100vh;overflow-y:auto;}
.sb-brand{font-family:var(--display);font-size:1.3rem;letter-spacing:.1em;color:var(--gold);margin-bottom:.15rem;}
.sb-sub{font-size:.62rem;letter-spacing:.18em;text-transform:uppercase;color:var(--muted);margin-bottom:2.5rem;}
.sb nav{display:flex;flex-direction:column;gap:.15rem;}
.sb nav a{font-size:.75rem;font-weight:400;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);padding:.6rem .75rem;border-radius:2px;transition:color var(--t),background var(--t);display:flex;align-items:center;justify-content:space-between;}
.sb nav a:hover{color:var(--cream);background:rgba(255,255,255,.05);}
.sb nav a.on{color:var(--gold);background:rgba(201,168,76,.07);}
.sb-badge{background:var(--rust);color:#fff;font-size:.55rem;padding:1px 5px;border-radius:10px;line-height:1.4;}
.sb-logout{margin-top:auto;font-size:.68rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);padding:.5rem .75rem;border:1px solid var(--border);border-radius:2px;text-align:center;transition:color var(--t),border-color var(--t);}
.sb-logout:hover{color:var(--err);border-color:var(--err);}

/* Main */
.main{flex:1;padding:2.5rem 2.5rem 4rem;overflow-y:auto;}
.ph{display:flex;align-items:baseline;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:2.5rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border);}
.ph h1{font-family:var(--display);font-size:2.2rem;letter-spacing:.05em;}
.ph-meta{font-family:var(--serif);font-style:italic;font-size:.9rem;color:var(--muted);}

/* Flash */
.flash{padding:.85rem 1.2rem;border-radius:2px;font-size:.82rem;margin-bottom:2rem;display:flex;align-items:center;gap:.6rem;animation:fadein .3s ease;}
.flash-ok  {background:rgba(76,175,125,.1);border:1px solid rgba(76,175,125,.28);color:var(--ok);}
.flash-err {background:rgba(224,82,82,.1); border:1px solid rgba(224,82,82,.28); color:var(--err);}
.flash-warn{background:rgba(232,168,56,.1);border:1px solid rgba(232,168,56,.28);color:var(--warn);}
@keyframes fadein{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}

/* Panel */
.panel{background:var(--panel);border:1px solid var(--border);border-radius:2px;padding:2rem;}
.panel-title{font-family:var(--display);font-size:1.1rem;letter-spacing:.08em;color:var(--gold);margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:1px solid var(--border);}

/* Form */
.fg{margin-bottom:1.2rem;}
.frow{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
label{display:block;font-size:.63rem;font-weight:500;letter-spacing:.18em;text-transform:uppercase;color:var(--muted);margin-bottom:.4rem;}
input[type=text],input[type=number],input[type=email],input[type=password],input[type=url],textarea,select{width:100%;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:2px;color:var(--cream);font-family:var(--body);font-size:.88rem;font-weight:300;padding:.7rem .9rem;outline:none;transition:border-color var(--t);appearance:none;}
input:focus,textarea:focus,select:focus{border-color:var(--gold);}
textarea{resize:vertical;min-height:80px;}
select option{background:var(--panel);}
.hint{font-size:.67rem;color:var(--muted);margin-top:.3rem;}
.file-lbl{display:flex;align-items:center;gap:.75rem;border:1px dashed var(--border);border-radius:2px;padding:.8rem;cursor:pointer;color:var(--muted);font-size:.8rem;transition:border-color var(--t),color var(--t);}
.file-lbl:hover{border-color:var(--gold);color:var(--gold);}
input[type=file]{display:none;}

/* Buttons */
.btn{display:inline-block;font-family:var(--body);font-size:.72rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;padding:.8rem 2rem;border-radius:2px;cursor:pointer;border:1px solid transparent;transition:opacity var(--t),transform var(--t),background var(--t),color var(--t);}
.btn:hover{opacity:.88;transform:translateY(-1px);}
.btn-gold{background:var(--gold);color:var(--ink);}
.btn-ghost{background:transparent;color:var(--muted);border-color:var(--border);}
.btn-ghost:hover{color:var(--cream);border-color:rgba(255,255,255,.2);opacity:1;transform:none;}
.btn-danger{background:rgba(224,82,82,.15);color:var(--err);border-color:rgba(224,82,82,.3);}
.btn-danger:hover{background:rgba(224,82,82,.25);opacity:1;transform:none;}
.btn-sm{padding:.35rem .7rem;font-size:.65rem;}

/* Table */
.tw{overflow-x:auto;border:1px solid var(--border);border-radius:2px;}
table{width:100%;border-collapse:collapse;font-size:.82rem;}
thead{background:var(--panel);border-bottom:1px solid var(--border);}
th{padding:.85rem 1rem;text-align:left;font-size:.62rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;color:var(--muted);white-space:nowrap;}
td{padding:.8rem 1rem;border-bottom:1px solid var(--border);vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:rgba(255,255,255,.02);}

/* Badges */
.badge{display:inline-block;padding:.18rem .55rem;border-radius:2px;font-size:.6rem;font-weight:500;letter-spacing:.1em;text-transform:uppercase;}
.b-ok  {background:rgba(76,175,125,.12);color:var(--ok);}
.b-err {background:rgba(224,82,82,.12); color:var(--err);}
.b-gold{background:rgba(201,168,76,.12);color:var(--gold);}
.b-grey{background:rgba(255,255,255,.06);color:var(--muted);}
.b-warn{background:rgba(232,168,56,.12);color:var(--warn);}

/* Misc */
.acts{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;}
.thumb{width:48px;height:48px;object-fit:cover;border-radius:2px;border:1px solid var(--border);}
.thumb-nil{width:48px;height:48px;background:var(--surface);border:1px dashed var(--border);border-radius:2px;display:flex;align-items:center;justify-content:center;font-size:.55rem;color:var(--muted);text-transform:uppercase;}
.cgrid{display:grid;grid-template-columns:320px 1fr;gap:2rem;align-items:start;}
.empty{text-align:center;padding:4rem 2rem;color:var(--muted);}
.empty p{font-family:var(--serif);font-style:italic;font-size:1.1rem;}

@media(max-width:1024px){.cgrid{grid-template-columns:1fr;}.frow{grid-template-columns:1fr;}}
@media(max-width:768px){.sb{display:none;}.main{padding:1.5rem;}}
</style>
</head>
<body>
<aside class="sb">
    <div class="sb-brand">RichelCity</div>
    <div class="sb-sub">Admin Panel</div>
    <nav>
        <?php
        $nav = [
            'dashboard'  => 'Dashboard',
            'products'   => 'Products',
            'categories' => 'Categories',
            'sliders'    => 'Sliders',
            'orders'     => 'Orders',
            'enquiries'  => 'Enquiries',
            'settings'   => 'Settings',
        ];
        $cur = basename($_SERVER['PHP_SELF'], '.php');
        foreach ($nav as $page => $label):
            $on  = ($cur === $page || ($cur === 'index' && $page === 'dashboard')) ? 'on' : '';
        ?>
        <a href="<?= $page ?>.php" class="<?= $on ?>">
            <?= $label ?>
            <?php if ($page === 'enquiries' && $unread > 0): ?>
            <span class="sb-badge"><?= $unread ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <a href="logout.php" class="sb-logout" style="margin-top:2rem;">Log Out</a>
</aside>
<main class="main">
<?php
}

function layout_flash(): void {
    $f = get_flash();
    if (!$f) return;
    $cls = ['ok'=>'flash-ok','error'=>'flash-err','warn'=>'flash-warn'][$f['type']] ?? 'flash-warn';
    echo "<div class='flash {$cls}'>" . e($f['msg']) . "</div>";
    echo "<script>setTimeout(()=>{var el=document.querySelector('.flash');if(el){el.style.transition='0.5s';el.style.opacity='0';}},4000);</script>";
}

function layout_end(): void {
    echo '</main></body></html>';
}
