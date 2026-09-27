<?php
session_start();

// Vercel est en lecture seule, on met la DB dans /tmp
$dbPath = '/tmp/shop.db';
$uploadDir = '/tmp/uploads';

if(!is_dir($uploadDir)){
    @mkdir($uploadDir, 0777, true);
}

// Si pas sur Vercel (test local XAMPP)
if(!file_exists('/tmp')){
    $dbPath = __DIR__.'/shop.db';
    $uploadDir = __DIR__.'/uploads';
}

$db = new PDO('sqlite:'.$dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY KEY, cat TEXT, name TEXT, price INTEGER, image TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS payments (id INTEGER PRIMARY KEY, nom TEXT, numero TEXT, proprio TEXT, logo TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY AUTOINCREMENT, pseudo TEXT, uid TEXT, items TEXT, total INTEGER, pay_id INTEGER, trans_msg TEXT, proof TEXT, status TEXT DEFAULT 'pending', date DATETIME DEFAULT CURRENT_TIMESTAMP)");
$db->exec("CREATE TABLE IF NOT EXISTS settings (k TEXT PRIMARY KEY, v TEXT)");

if($db->query("SELECT COUNT(*) FROM products")->fetchColumn()==0){
  $db->exec("INSERT INTO products (cat,name,price,image) VALUES 
  ('topup','110 Diamonds',4800,'https://i.imgur.com/JqYeZ3n.png'),
  ('topup','530 Diamonds',23000,'https://i.imgur.com/JqYeZ3n.png'),
  ('abonnement','Abo Hebdo',6500,'https://i.imgur.com/5X2XqQa.png'),
  ('levelup','Level Up Pass',15000,'https://i.imgur.com/8Km9tLL.png')");

  $db->exec("INSERT INTO payments (nom,numero,proprio,logo) VALUES 
  ('Mvola','034 00 000 00','NO MERCY','https://i.imgur.com/0K1b9T1.png'),
  ('Orange Money','032 00 000 00','NO MERCY','https://i.imgur.com/3y9Yf7H.png')");

  $db->exec("INSERT INTO settings (k,v) VALUES ('admin_pass','1234')");
}
?>