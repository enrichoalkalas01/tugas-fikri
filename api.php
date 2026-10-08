<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
function reply(array $data, int $status=200): never { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); exit; }
try {
    if ($_SERVER['REQUEST_METHOD']==='GET') reply(['products'=>products(),'csrf'=>$_SESSION['csrf'],'token'=>bin2hex(random_bytes(24))]);
    if ($_SERVER['REQUEST_METHOD']!=='POST') { header('Allow: GET, POST'); reply(['message'=>'Metode tidak didukung.'],405); }
    $data=json_decode(file_get_contents('php://input'),true);
    if (!is_array($data)) reply(['message'=>'Permintaan tidak valid.'],400);
    if (!hash_equals($_SESSION['csrf'],scalar($data,'csrf'))) reply(['message'=>'Sesi kedaluwarsa. Muat ulang halaman dan coba lagi.'],403);
    $name=scalar($data,'name');$phone=scalar($data,'phone');$address=scalar($data,'address');$payment=scalar($data,'payment');$token=scalar($data,'token');
    if ($name==='' || mb_strlen($name)>100 || !preg_match('/^[+0-9 ()-]{8,25}$/D',$phone) || mb_strlen($address)<10 || mb_strlen($address)>500 || !in_array($payment,['cash','credit'],true) || !preg_match('/^[a-f0-9]{48}$/D',$token)) reply(['message'=>'Periksa nama, nomor telepon, alamat (10–500 karakter), dan pilihan pembayaran.'],422);
    $catalog=array_column(products(),null,'id');$items=[];$total=0;
    if (!is_array($data['items']??null) || count($data['items'])<1 || count($data['items'])>8) reply(['message'=>'Pilih minimal satu produk.'],422);
    foreach($data['items'] as $item) {
        if (!is_array($item)) reply(['message'=>'Produk tidak valid.'],422);
        $id=scalar($item,'id');$qty=$item['qty']??0;
        if (!isset($catalog[$id]) || !$catalog[$id]['stock'] || !is_int($qty) || $qty<1 || $qty>10 || isset($items[$id])) reply(['message'=>'Produk atau jumlah tidak valid. Jumlah maksimum 10 per produk.'],422);
        $items[$id]=['id'=>$id,'name'=>$catalog[$id]['name'],'qty'=>$qty,'price'=>$catalog[$id]['price']];$total+=$qty*$catalog[$id]['price'];
    }
    $check=db()->prepare('SELECT id,total FROM orders WHERE token=? AND owner=?');$check->execute([$token,$owner]);
    if($existing=$check->fetch()) reply(['message'=>'Pesanan demo sudah tersimpan.','id'=>$existing['id'],'total'=>$existing['total']]);
    $count=db()->prepare('SELECT COUNT(*) FROM orders WHERE owner=?');$count->execute([$owner]);
    if($count->fetchColumn()>=100) reply(['message'=>'Batas 100 pesanan demo per browser tercapai.'],429);
    $id='EAD-'.strtoupper(bin2hex(random_bytes(4)));
    db()->prepare('INSERT INTO orders(id,owner,name,phone,address,payment,items,total,token) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$id,$owner,$name,$phone,$address,$payment,json_encode(array_values($items)),$total,$token]);
    reply(['message'=>'Pesanan demo berhasil disimpan. Tidak ada pembayaran atau pengiriman nyata.','id'=>$id,'total'=>$total],201);
} catch(Throwable $error) { error_log('Fikri demo: '.$error->getMessage());reply(['message'=>'Penyimpanan sementara tidak tersedia. Silakan coba lagi.'],503); }
