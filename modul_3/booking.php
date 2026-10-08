<?php
require dirname(__DIR__).'/app/bootstrap.php';
$title='Plan your stay — EAD Stay';$rooms=rooms();$extras=services();$errors=[];
$today=date('Y-m-d');$tomorrow=date('Y-m-d',strtotime('+1 day'));
$values=['name'=>'','email'=>'','phone'=>'','checkin'=>scalar($_GET,'checkin')?:$tomorrow,'checkout'=>scalar($_GET,'checkout')?:date('Y-m-d',strtotime('+2 days')),'room'=>scalar($_GET,'room')?:'deluxe','guests'=>scalar($_GET,'guests')?:'2'];
if(!isset($rooms[$values['room']]))$values['room']='deluxe';
if(!isset($_GET['guests']))$values['guests']=(string)min(2,$rooms[$values['room']]['capacity']);
elseif((int)$values['guests']>2&&!isset($_GET['room']))$values['room']='luxury';
$selected=[];$token=bin2hex(random_bytes(24));
if($_SERVER['REQUEST_METHOD']==='POST') {
    foreach($values as $key=>$value)$values[$key]=scalar($_POST,$key);
    $token=scalar($_POST,'token');
    if(!hash_equals($_SESSION['csrf'],scalar($_POST,'csrf')))$errors[]='Your session has expired. Refresh this page and try again.';
    if(!preg_match('/^[a-f0-9]{48}$/D',$token))$errors[]='Invalid reservation request.';
    if($values['name']===''||mb_strlen($values['name'])>100)$errors[]='Please enter a name of 1–100 characters.';
    if(!filter_var($values['email'],FILTER_VALIDATE_EMAIL)||strlen($values['email'])>150)$errors[]='Please enter a valid email address.';
    if(!preg_match('/^[+0-9 ()-]{8,25}$/D',$values['phone']))$errors[]='Please enter a valid phone number.';
    $arrival=preg_match('/^\d{4}-\d{2}-\d{2}$/D',$values['checkin'])?DateTimeImmutable::createFromFormat('!Y-m-d',$values['checkin']):false;
    $departure=preg_match('/^\d{4}-\d{2}-\d{2}$/D',$values['checkout'])?DateTimeImmutable::createFromFormat('!Y-m-d',$values['checkout']):false;
    $validDates=$arrival&&$departure&&$arrival->format('Y-m-d')===$values['checkin']&&$departure->format('Y-m-d')===$values['checkout'];
    $nights=$validDates?(int)$arrival->diff($departure)->format('%r%a'):0;
    if(!$validDates||$values['checkin']<$today||$values['checkin']>date('Y-m-d',strtotime('+1 year'))||$nights<1||$nights>30)$errors[]='Choose a future stay of 1–30 nights, with arrival within one year.';
    if(!isset($rooms[$values['room']]))$errors[]='Please choose a valid room.';
    if(!ctype_digit($values['guests'])||(int)$values['guests']<1||(int)$values['guests']>($rooms[$values['room']]['capacity']??0))$errors[]='The selected room does not accommodate this number of guests.';
    $requested=$_POST['services']??[];
    if(!is_array($requested))$errors[]='Please choose valid extras.';
    else foreach($requested as $service) { if(!is_string($service)||!isset($extras[$service]))$errors[]='An extra is not available.';else $selected[]=$service; }
    $selected=array_values(array_unique($selected));
    if(!$errors) {
        try {
            $find=db()->prepare('SELECT id FROM bookings WHERE owner=? AND token=?');$find->execute([$owner,$token]);$existing=$find->fetchColumn();
            if($existing){header('Location: booking-list.php?ref='.rawurlencode($existing),true,303);exit;}
            $count=db()->prepare('SELECT COUNT(*) FROM bookings WHERE owner=?');$count->execute([$owner]);
            if($count->fetchColumn()>=100)throw new RuntimeException('Demo booking limit reached.');
            $total=$rooms[$values['room']]['price']*$nights;
            foreach($selected as $service)$total+=$extras[$service]['price'];
            $ref='STAY-'.strtoupper(bin2hex(random_bytes(4)));
            db()->prepare('INSERT INTO bookings(id,owner,name,email,phone,room,checkin,checkout,guests,nights,services,total,token) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([$ref,$owner,$values['name'],$values['email'],$values['phone'],$values['room'],$values['checkin'],$values['checkout'],(int)$values['guests'],$nights,json_encode($selected),$total,$token]);
            header('Location: booking-list.php?ref='.rawurlencode($ref),true,303);exit;
        } catch(Throwable $error) {error_log($error->getMessage());$errors[]='We could not save your demo reservation. Please try again.';http_response_code(503);}
    } else http_response_code(422);
}
$chosen=$rooms[$values['room']]??$rooms['deluxe'];include 'header.php';
?>
<main id="main" class="wrap booking-main"><a class="back-link" href="index.php#rooms">← Back to our rooms</a><div class="section-heading"><div><p class="eyebrow">YOUR NEXT LITTLE ESCAPE</p><h1>Make room<br>for <em>yourself.</em></h1></div><p>01 / Your stay &nbsp; — &nbsp; 02 / Confirmation<br><small>Portfolio demo · no payment required</small></p></div>
<?php if($errors): ?><div class="feedback error" role="alert"><strong>A few details need your attention.</strong><ul><?php foreach($errors as $error): ?><li><?= e($error) ?></li><?php endforeach ?></ul></div><?php endif ?>
<div class="booking-grid"><form method="post" class="booking-form" id="booking-form"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="token" value="<?= e($token) ?>"><fieldset><legend><span>01</span> A stay that suits you</legend><div class="form-grid"><div><label for="checkin">Check-in</label><input type="date" id="checkin" name="checkin" required min="<?= $today ?>" max="<?= date('Y-m-d',strtotime('+1 year')) ?>" value="<?= e($values['checkin']) ?>"></div><div><label for="checkout">Check-out</label><input type="date" id="checkout" name="checkout" required min="<?= $tomorrow ?>" value="<?= e($values['checkout']) ?>"></div><div><label for="room">Your room</label><select name="room" id="room"><?php foreach($rooms as $id=>$room): ?><option value="<?= e($id) ?>" <?= $values['room']===$id?'selected':'' ?>><?= e($room['name']) ?> · <?= usd($room['price']) ?>/night</option><?php endforeach ?></select></div><div><label for="guests">Guests</label><select id="guests" name="guests"><?php for($i=1;$i<=4;$i++): ?><option value="<?= $i ?>" <?= $values['guests']===(string)$i?'selected':'' ?>><?= $i ?> <?= $i===1?'guest':'guests' ?></option><?php endfor ?></select></div></div></fieldset><fieldset><legend><span>02</span> The little extras</legend><p class="form-hint">Optional. Each extra is charged once per stay, not per night.</p><?php foreach($extras as $id=>$service): ?><label class="extra-option"><input type="checkbox" name="services[]" value="<?= e($id) ?>" <?= in_array($id,$selected,true)?'checked':'' ?>><span><?= e($service['name']) ?><small><?= $id==='breakfast'?'Start your stay on a delicious note.':'A little more ease, delivered to your room.' ?></small></span><b>+<?= usd($service['price']) ?></b></label><?php endforeach ?></fieldset><fieldset><legend><span>03</span> A little about you</legend><div class="form-grid"><div class="full"><label for="name">Full name</label><input id="name" name="name" autocomplete="name" maxlength="100" required value="<?= e($values['name']) ?>" placeholder="How should we call you?"></div><div><label for="email">Email address</label><input id="email" name="email" type="email" maxlength="150" autocomplete="email" required value="<?= e($values['email']) ?>" placeholder="you@example.com"></div><div><label for="phone">Phone number</label><input id="phone" name="phone" type="tel" maxlength="25" autocomplete="tel" required value="<?= e($values['phone']) ?>" placeholder="+62 812 3456 7890"></div></div></fieldset><p class="form-hint">Use test details. This reserves a demo only: no real room, payment, or confirmation email.</p><button class="button wide" type="submit">Confirm demo reservation <span>↗</span></button></form>
<aside class="booking-summary"><img id="summary-image" src="assets/img/<?= e($chosen['image']) ?>" alt="Selected room interior"><div class="summary-body"><p class="eyebrow">YOUR LITTLE ESCAPE</p><h2 id="summary-name"><?= e($chosen['name']) ?></h2><p id="summary-capacity">Up to <?= $chosen['capacity'] ?> guests</p><dl><div><dt id="summary-night-label">Room rate</dt><dd id="summary-rate"><?= usd($chosen['price']) ?></dd></div><div><dt>Extras / per stay</dt><dd id="summary-extras">$0</dd></div><div class="summary-total"><dt>Demo total</dt><dd id="summary-total">—</dd></div></dl><p class="form-hint" id="booking-hint" role="status">Final price is verified by the server.</p><div class="summary-note">✳ A little less ordinary.<br><small>No hidden charges in this demo.</small></div></div></aside></div></main>
<script id="room-data" type="application/json"><?= json_encode(['rooms'=>$rooms,'services'=>$extras],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script><?php include 'footer.php'; ?>
