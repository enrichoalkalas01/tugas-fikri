<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Jakarta');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$rootUrl = preg_replace('~/(?:modul_3/)?[^/]+\.php$~', '/', $script);
session_name('fikri_showcase');
session_set_cookie_params(['path' => $rootUrl, 'httponly' => true, 'samesite' => 'Lax']);
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$owner = $_COOKIE['fikri_demo'] ?? '';
if (!is_string($owner) || !preg_match('/^[a-f0-9]{64}$/D', $owner)) {
    $owner = bin2hex(random_bytes(32));
    setcookie('fikri_demo', $owner, ['expires' => time() + 30*86400, 'path' => $rootUrl, 'httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
}
$owner = hash('sha256', $owner);
function e(mixed $text): string { return htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function scalar(array $data, string $key): string { return is_string($data[$key] ?? null) ? trim($data[$key]) : ''; }
function money(int $amount): string { return 'Rp ' . number_format($amount, 0, ',', '.'); }
function usd(int $amount): string { return '$' . number_format($amount); }
function db(): PDO {
    static $db;
    if (!$db) {
        $db = new PDO('sqlite:' . dirname(__DIR__) . '/storage/demo.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $db->exec('PRAGMA busy_timeout=5000');
        $db->exec('CREATE TABLE IF NOT EXISTS bookings (id TEXT PRIMARY KEY, owner TEXT NOT NULL, name TEXT NOT NULL, email TEXT NOT NULL, phone TEXT NOT NULL, room TEXT NOT NULL, checkin TEXT NOT NULL, checkout TEXT NOT NULL, guests INTEGER NOT NULL, nights INTEGER NOT NULL, services TEXT NOT NULL, total INTEGER NOT NULL, status TEXT NOT NULL DEFAULT "confirmed", created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, token TEXT UNIQUE NOT NULL)');
        $db->exec('CREATE TABLE IF NOT EXISTS orders (id TEXT PRIMARY KEY, owner TEXT NOT NULL, name TEXT NOT NULL, phone TEXT NOT NULL, address TEXT NOT NULL, payment TEXT NOT NULL, items TEXT NOT NULL, total INTEGER NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, token TEXT UNIQUE NOT NULL)');
    }
    return $db;
}
function rooms(): array { return [
    'standard' => ['name'=>'The Essential','label'=>'Standard Room','price'=>90,'capacity'=>1,'size'=>'24','bed'=>'1 single bed','image'=>'room-1.jpg','description'=>'A quiet corner to call your own. All the essentials, with a little extra warmth.','features'=>['Private bathroom','Fresh linen','A restful single bed']],
    'deluxe' => ['name'=>'The Slow Stay','label'=>'Deluxe Room','price'=>90,'capacity'=>2,'size'=>'36','bed'=>'1 double bed','image'=>'room-2.jpg','description'=>'Room to unwind, reconnect, and leave your everyday routine at the door.','features'=>['Double bed','Television & Wi-Fi','Hot-water bathroom']],
    'luxury' => ['name'=>'The Grand Retreat','label'=>'Luxury Suite','price'=>200,'capacity'=>4,'size'=>'58','bed'=>'2 double beds','image'=>'room-3.jpg','description'=>'More space for the moments that matter. A considered retreat for your whole stay.','features'=>['Two double beds','Kitchen & work space','Television & Wi-Fi']],
]; }
function services(): array { return ['breakfast'=>['name'=>'Breakfast','price'=>20], 'roomservice'=>['name'=>'Room service','price'=>20]]; }
function products(): array { return [
 ['id'=>'hp-aio','name'=>'HP All-in-One 24','model'=>'24-f0051d · 3JV73AA','brand'=>'HP','category'=>'Komputer','price'=>11358000,'stock'=>true,'art'=>'monitor','tone'=>'sage','description'=>'Satu perangkat untuk meja kerja yang lebih ringkas. Referensi produk dari katalog awal.'],
 ['id'=>'asus-aio','name'=>'ASUS All-in-One V222','model'=>'V222UAK-BA341T','brand'=>'ASUS','category'=>'Komputer','price'=>8099000,'stock'=>true,'art'=>'monitor','tone'=>'lilac','description'=>'Pilihan all-in-one untuk kebutuhan kerja dan belajar sehari-hari.'],
 ['id'=>'logitech-k480','name'=>'Logitech K480','model'=>'Bluetooth Multi-Device · Black','brand'=>'Logitech','category'=>'Keyboard','price'=>470000,'stock'=>true,'art'=>'keyboard','tone'=>'peach','description'=>'Keyboard multi-device untuk berpindah dari satu pekerjaan ke pekerjaan berikutnya.'],
 ['id'=>'dell-km636','name'=>'Dell KM636','model'=>'Wireless Keyboard & Mouse','brand'=>'Dell','category'=>'Keyboard','price'=>399000,'stock'=>true,'art'=>'keyboard','tone'=>'gray','description'=>'Satu set keyboard dan mouse wireless untuk setup yang lebih bersih.'],
 ['id'=>'logitech-m238','name'=>'Logitech M238','model'=>'Fan Collection · Wireless Mouse','brand'=>'Logitech','category'=>'Mouse','price'=>175000,'stock'=>true,'art'=>'mouse','tone'=>'lilac','description'=>'Mouse ringkas untuk menemani pekerjaan sehari-hari. Data merek dan harga diselaraskan dari baris katalog yang tertukar.'],
 ['id'=>'lenovo-aio','name'=>'Lenovo IdeaCentre 520','model'=>'AIO520-22IKL · F0D4005MID','brand'=>'Lenovo','category'=>'Komputer','price'=>10850000,'stock'=>true,'art'=>'monitor','tone'=>'peach','description'=>'Komputer all-in-one dengan ruang meja yang tetap lega. Data katalog demo, bukan spesifikasi terverifikasi.'],
 ['id'=>'logitech-m590','name'=>'Logitech M590','model'=>'Multi-Device Silent Mouse','brand'=>'Logitech','category'=>'Mouse','price'=>394000,'stock'=>false,'art'=>'mouse','tone'=>'sage','description'=>'Pilihan mouse multi-device dalam koleksi. Belum tersedia pada simulasi katalog ini.'],
 ['id'=>'apple-keyboard','name'=>'Apple Smart Keyboard','model'=>'iPad Pro 10.5-inch · MPTL22ZA/A','brand'=>'Apple','category'=>'Keyboard','price'=>3000800,'stock'=>false,'art'=>'keyboard','tone'=>'gray','description'=>'Keyboard untuk iPad Pro 10.5-inch. Ditampilkan sebagai bagian dari katalog demo.'],
]; }
