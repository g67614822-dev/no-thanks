<?php
chdir(__DIR__.'/..');
require_once 'config.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
if($uri == '') $uri = '/';

if($uri == '/tarifs'){ require 'tarifs.php'; exit; }
if($uri == '/panier'){ require 'panier.php'; exit; }
if($uri == '/paiement'){ require 'paiement.php'; exit; }
if($uri == '/historique'){ require 'historique.php'; exit; }
if($uri == '/admin'){ require 'admin.php'; exit; }

require 'index.php';
