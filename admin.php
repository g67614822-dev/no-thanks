<?php include 'config.php';
$db->exec("CREATE TABLE IF NOT EXISTS settings (k TEXT PRIMARY KEY, v TEXT)");
if(!$db->query("SELECT v FROM settings WHERE k='admin_pass'")->fetchColumn()){
  $db->exec("INSERT INTO settings (k,v) VALUES ('admin_pass','1234')");
}
$admin_pass=$db->query("SELECT v FROM settings WHERE k='admin_pass'")->fetchColumn();

if(!isset($_SESSION['admin'])){
  if(isset($_POST['pass']) && $_POST['pass']==$admin_pass){ $_SESSION['admin']=1; header("Location:/admin"); exit; }
  echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-[#06060a] text-white flex items-center justify-center min-h-screen p-6"><form method="post" class="bg-[#12121c] border border-white/10 p-8 rounded-[24px] w-full max-w-sm"><div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-400 to-orange-600 flex items-center justify-center font-black text-black mx-auto">NM</div><h2 class="text-center font-black mt-4">ADMIN NO MERCY</h2><input name="pass" type="password" placeholder="Mot de passe" class="w-full bg-black border border-white/10 p-4 rounded-xl text-xs mt-6"><button class="w-full bg-white text-black p-4 rounded-xl mt-4 text-xs font-black">ENTRER</button></form></body></html>'; exit;
}
if(isset($_GET['del'])){ $db->exec("DELETE FROM orders WHERE id=".intval($_GET['del'])); header("Location:/admin"); exit; }
if(isset($_POST['status'])) $db->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$_POST['status'],$_POST['id']]);
if(isset($_POST['addprod'])) $db->prepare("INSERT INTO products (cat,name,price,image) VALUES (?,?,?,?)")->execute([$_POST['cat'],$_POST['name'],$_POST['price'],$_POST['image']]);
if(isset($_POST['delprod'])) $db->exec("DELETE FROM products WHERE id=".intval($_POST['delprod']));
if(isset($_POST['addpay'])) $db->prepare("INSERT INTO payments (nom,numero,proprio,logo) VALUES (?,?,?,?)")->execute([$_POST['nom'],$_POST['numero'],$_POST['proprio'],$_POST['logo']]);
if(isset($_POST['delpay'])) $db->exec("DELETE FROM payments WHERE id=".intval($_POST['delpay']));
if(isset($_POST['newpass'])){ $db->prepare("UPDATE settings SET v=? WHERE k='admin_pass'")->execute([$_POST['newpass']]); $admin_pass=$_POST['newpass']; }
if(isset($_GET['logout'])){ session_destroy(); header("Location:/admin"); exit; }

$orders=$db->query("SELECT o.*, p.nom as pay_nom FROM orders o LEFT JOIN payments p ON o.pay_id=p.id ORDER BY o.id DESC")->fetchAll();
$products=$db->query("SELECT * FROM products")->fetchAll();
$pays=$db->query("SELECT * FROM payments")->fetchAll();
$total=$db->query("SELECT SUM(total) FROM orders WHERE status='finis'")->fetchColumn()?:0;
$pending=count(array_filter($orders,fn($o)=>$o['status']=='pending'));
$finis=count(array_filter($orders,fn($o)=>$o['status']=='finis'));
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><script src="https://cdn.tailwindcss.com"></script><link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@800&display=swap" rel="stylesheet"><style>.orbit{font-family:'Orbitron'} body{background:#06060a}.glass{background:rgba(18,18,28,0.85);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.06)}.tab-active{background:white!important;color:black!important}</style></head>
<body class="text-white">
<header class="glass sticky top-0 z-50 p-4 flex justify-between items-center"><div class="flex gap-3 items-center"><div class="w-8 h-8 rounded-lg bg-gradient-to-br from-yellow-400 to-orange-600 flex items-center justify-center text-black font-black text-xs">NM</div><h1 class="orbit text-[11px]">ADMIN</h1></div><div class="flex gap-2"><a href="/" class="bg-white/10 px-3 py-1.5 rounded-full text-[10px]">Shop</a><a href="/admin?logout=1" class="bg-red-500/20 text-red-300 px-3 py-1.5 rounded-full text-[10px]">Logout</a></div></header>

<div class="max-w-6xl mx-auto p-3">
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
<div class="glass rounded-[18px] p-4"><p class="text-[9px] text-white/40">TOTAL ARGENT</p><p class="font-black text-lg text-yellow-400"><?= number_format($total)?> Ar</p></div>
<div class="glass rounded-[18px] p-4"><p class="text-[9px] text-white/40">PENDING</p><p class="font-black text-lg text-yellow-400"><?= $pending?></p></div>
<div class="glass rounded-[18px] p-4"><p class="text-[9px] text-white/40">FINIS</p><p class="font-black text-lg text-green-400"><?= $finis?></p></div>
<div class="glass rounded-[18px] p-4"><p class="text-[9px] text-white/40">COMMANDES</p><p class="font-black text-lg"><?= count($orders)?></p></div>
</div>

<div class="flex gap-2 mt-6 overflow-x-auto">
<button onclick="showTab('orders',this)" class="tabBtn tab-active px-5 py-2.5 rounded-full text-[11px] font-bold">📦 Commandes</button>
<button onclick="showTab('tarifs',this)" class="tabBtn bg-white/10 px-5 py-2.5 rounded-full text-[11px]">💎 Tarifs</button>
<button onclick="showTab('pays',this)" class="tabBtn bg-white/10 px-5 py-2.5 rounded-full text-[11px]">💳 Payements</button>
<button onclick="showTab('settings',this)" class="tabBtn bg-white/10 px-5 py-2.5 rounded-full text-[11px]">⚙️ Mdp</button>
</div>

<div id="tab-orders" class="tab mt-5 space-y-3">
<?php foreach($orders as $o): $col=$o['status']=='pending'?'yellow':($o['status']=='finis'?'green':'red');?>
<div class="glass rounded-[16px] p-4">
<div class="flex justify-between"><p class="font-bold text-xs">#<?= $o['id']?> • <?= $o['pseudo']?> (<?= $o['uid']?>)</p><span class="bg-<?= $col?>-500/20 text-<?= $col?>-300 px-2 py-1 rounded-full text-[9px]"><?= strtoupper($o['status'])?></span></div>
<p class="text-[10px] text-white/40 mt-2"><?= $o['items']?> • <?= $o['total']?> Ar • <?= $o['pay_nom']?></p>
<div class="bg-yellow-500/10 border border-yellow-500/10 p-2 rounded-lg mt-2 text-[11px]"><?= htmlspecialchars($o['trans_msg'])?></div>
<div class="flex gap-2 mt-3"><form method="post" class="flex gap-2 flex-1"><input type="hidden" name="id" value="<?= $o['id']?>"><button name="status" value="finis" class="flex-1 bg-green-500 text-black py-2.5 rounded-full text-[11px] font-bold">✓ Finis</button><button name="status" value="rejeter" class="flex-1 bg-red-500/20 text-red-300 border border-red-500/30 py-2.5 rounded-full text-[11px]">✕ Rejeter</button></form><a href="/admin?del=<?= $o['id']?>" class="bg-white/10 px-3 py-2.5 rounded-full text-[11px]">Suppr</a></div>
</div>
<?php endforeach; if(empty($orders)) echo "<p class='text-white/30 text-xs'>Aucune commande</p>";?>
</div>

<div id="tab-tarifs" class="tab hidden mt-5">
<div class="glass rounded-[20px] p-5"><h3 class="font-bold text-xs mb-3">Ajouter Tarif</h3><form method="post" class="space-y-2"><select name="cat" class="w-full bg-black border border-white/10 p-3 rounded-xl text-xs"><option value="topup">Top Up</option><option value="abonnement">Abonnement</option><option value="levelup">Level Up</option></select><input name="name" required placeholder="Nom" class="w-full bg-black border border-white/10 p-3 rounded-xl text-xs"><input name="price" type="number" required placeholder="Prix Ar" class="w-full bg-black border border-white/10 p-3 rounded-xl text-xs"><input name="image" required placeholder="URL image" class="w-full bg-black border border-white/10 p-3 rounded-xl text-xs"><button name="addprod" class="w-full bg-yellow-400 text-black p-3 rounded-xl text-xs font-black">AJOUTER</button></form></div>
<div class="mt-3 space-y-2"><?php foreach($products as $p) echo "<div class='glass p-3 rounded-xl flex justify-between text-xs'><span>{$p['cat']} - {$p['name']} - {$p['price']} Ar</span><form method='post'><button name='delprod' value='{$p['id']}' class='text-red-400'>X</button></form></div>";?></div>
</div>

<div id="tab-pays" class="tab hidden mt-5">
<div class="glass rounded-[20px] p-5"><h3 class="font-bold text-xs mb-3">Ajouter Payement</h3><form method="post" class="space-y-2"><input name="nom" required placeholder="Mvola / Orange" class="w-full bg-black border border-white/10 p-3 rounded-xl text-xs"><input name="numero" required placeholder="Numero" class="w-full bg-black border border-white/10 p-3 rounded-xl text-xs"><input name="proprio" required placeholder="Proprio" class="w-full bg-black border border-white/10 p-3 rounded-xl text-xs"><input name="logo" required placeholder="URL logo" class="w-full bg-black border border-white/10 p-3 rounded-xl text-xs"><button name="addpay" class="w-full bg-white text-black p-3 rounded-xl text-xs font-black">AJOUTER</button></form></div>
<div class="mt-3 space-y-2"><?php foreach($pays as $pa) echo "<div class='glass p-3 rounded-xl flex justify-between text-xs'><span>{$pa['nom']} - {$pa['numero']}</span><form method='post'><button name='delpay' value='{$pa['id']}' class='text-red-400'>X</button></form></div>";?></div>
</div>

<div id="tab-settings" class="tab hidden mt-5">
<div class="glass rounded-[20px] p-5"><p class="text-[11px] text-white/40">Mdp actuel: <b class="text-white"><?= $admin_pass?></b></p><form method="post" class="mt-4 space-y-2"><input name="newpass" required placeholder="Nouveau mdp" class="w-full bg-black border border-white/10 p-3 rounded-xl text-xs"><button class="w-full bg-white text-black p-3 rounded-xl text-xs font-black">CHANGER MDP</button></form></div>
</div>
</div>
<script>function showTab(n,el){document.querySelectorAll('.tab').forEach(t=>t.classList.add('hidden'));document.getElementById('tab-'+n).classList.remove('hidden');document.querySelectorAll('.tabBtn').forEach(b=>b.className='tabBtn bg-white/10 px-5 py-2.5 rounded-full text-[11px]');el.className='tabBtn tab-active px-5 py-2.5 rounded-full text-[11px] font-bold';}</script>
</body></html>