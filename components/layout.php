<?php
require_once __DIR__ . '/../db/app.php';

function icon(string $name, int $size = 20): string {
    $paths = [
        'arrow'=>'<path d="M5 12h14m-6-6 6 6-6 6"/>',
        'check'=>'<path d="m5 12 4 4L19 6"/>',
        'menu'=>'<path d="M4 6h16M4 12h16M4 18h16"/>',
        'user'=>'<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'bag'=>'<path d="M5 7h14l1 14H4L5 7Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/>',
        'leaf'=>'<path d="M20 3C6 1 2 10 6 16s15 2 14-13Z"/><path d="M4 21 16 9"/>',
        'shirt'=>'<path d="m8 3-6 4 3 5 3-2v11h8V10l3 2 3-5-6-4a4 4 0 0 1-8 0Z"/>',
        'clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'calendar'=>'<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18"/>',
        'pin'=>'<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'spark'=>'<path d="m12 3 2.6 6.4L21 12l-6.4 2.6L12 21l-2.6-6.4L3 12l6.4-2.6L12 3Z"/>',
        'wash'=>'<rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="14" r="5"/><path d="M7 6h3m5 0h2"/>',
        'plus'=>'<path d="M12 5v14M5 12h14"/>',
        'minus'=>'<path d="M5 12h14"/>',
        'search'=>'<circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/>',
        'chevron'=>'<path d="m6 9 6 6 6-6"/>',
        'star'=>'<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3 6.4 20.2l1.1-6.2L3 9.6l6.2-.9L12 3Z"/>',
        'lock'=>'<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'pause'=>'<path d="M8 5v14M16 5v14"/>',
        'rotate'=>'<path d="M3 10a9 9 0 1 1 2 8M3 4v6h6"/>',
    ];
    return '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['leaf']).'</svg>';
}
function asset(string $path): string {
    $file = dirname(__DIR__) . '/' . $path;
    return url($path) . '?v=' . (is_file($file) ? filemtime($file) : '1');
}
function brand(): string {
    return '<a class="brand" href="'.h(url('home.php')).'" aria-label="EcoLeaf home"><img src="'.h(asset('images/leaf.png')).'" width="31" height="31" alt=""><span>Eco<span class="brand-leaf">Leaf</span></span></a>';
}
function page_start(string $title, string $active = '', string $description = 'Laundry care made easier. Explore EcoLeaf services, schedule a pickup, and manage your bookings.'): void {
    $user = current_user();
    ?><!doctype html>
<html lang="en" data-type="service">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= h($description) ?>">
    <meta name="theme-color" content="#156c45">
    <meta name="page-type" content="service">
    <meta name="site-kind" content="other">
    <meta property="og:title" content="<?= h($title) ?> | EcoLeaf">
    <meta property="og:description" content="<?= h($description) ?>">
    <meta property="og:type" content="website">
    <meta property="og:image" content="https://ecoleaf.wuaze.com/images/leaf.png">
    <title><?= h($title) ?> | EcoLeaf</title>
    <link rel="icon" type="image/png" sizes="512x512" href="<?= h(asset('images/leaf.png')) ?>">
    <link rel="apple-touch-icon" href="<?= h(asset('images/leaf.png')) ?>">
    <link rel="preload" href="<?= h(url('assets/fonts/Manrope-Variable.ttf')) ?>" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="<?= h(asset('styles.css')) ?>">
    <script src="<?= h(asset('script.js')) ?>" defer></script>
</head>
<body class="page-<?= h($active) ?>">
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header"><div class="container header-inner">
    <?= brand() ?>
    <button class="icon-button menu-toggle" type="button" data-menu-toggle aria-controls="site-navigation" aria-expanded="false" aria-label="Open navigation"><?= icon('menu') ?></button>
    <nav id="site-navigation" class="site-navigation" aria-label="Main navigation">
        <?php foreach (['home'=>['home.php','Home'],'services'=>['package.php','Services'],'about'=>['about.php','About us']] as $key=>$item): ?>
        <a href="<?= h(url($item[0])) ?>" <?= $active===$key?'aria-current="page"':'' ?>><?= h($item[1]) ?></a>
        <?php endforeach ?>
        <?php if ($user): ?>
        <a href="<?= h(url('my_orders.php')) ?>" <?= $active==='orders'?'aria-current="page"':'' ?>>My orders</a>
        <?php if ($user['role']==='admin'): ?><a href="<?= h(url('admin/adminDash.php')) ?>" <?= $active==='admin'?'aria-current="page"':'' ?>>Dashboard</a><?php endif ?>
        <a class="account-link" href="<?= h(url('profile.php')) ?>" <?= $active==='profile'?'aria-current="page"':'' ?>><?= icon('user',18) ?><span><?= h($user['username']) ?></span></a>
        <form action="<?= h(url('db/db_logout.php')) ?>" method="post"><?= csrf_field() ?><button class="text-button" type="submit">Sign out</button></form>
        <?php else: ?><a href="<?= h(url('login.php')) ?>" <?= $active==='login'?'aria-current="page"':'' ?>>Sign in</a><?php endif ?>
        <a class="button button-small" href="<?= h(url('booking.php')) ?>">Book a pickup <?= icon('arrow',17) ?></a>
    </nav>
</div></header>
<?php
    $messages = $_SESSION['flash'] ?? []; unset($_SESSION['flash']);
    if ($messages): ?><div class="container flash-stack"><?php foreach ($messages as $message):
        $type = in_array($message['type'],['error','success','info'],true)?$message['type']:'info'; ?>
        <div class="notice notice-<?= h($type) ?>" role="<?= $type==='error'?'alert':'status' ?>"><?= h($message['message']) ?></div>
    <?php endforeach ?></div><?php endif;
}
function page_end(): void { ?>
<footer class="site-footer"><div class="container footer-top"><div><?= brand() ?><p>Care for your clothes.<br>Time for everything else.</p></div>
<nav aria-label="Footer navigation"><a href="<?= h(url('package.php')) ?>">Our services</a><a href="<?= h(url('about.php#how-it-works')) ?>">How it works</a><a href="<?= h(url('about.php#faq')) ?>">Questions & answers</a></nav>
<nav aria-label="Account and information"><a href="<?= h(url('my_orders.php')) ?>">Your orders</a><a href="<?= h(url('profile.php')) ?>">Your account</a><a href="<?= h(url('privacy.php')) ?>">Privacy & demo information</a></nav>
</div><div class="container footer-bottom"><span>&copy; <?= date('Y') ?> EcoLeaf</span><span>Addis Ababa, Ethiopia</span><span>A demonstration website. Please use test details.</span></div></footer>
</body></html><?php }
function service_photo(array $service): ?array {
    $photographs = [
        'dry cleaning' => ['dry-cleaning.jpg', 'Pressed shirts and a blazer on wooden hangers'],
        'wash and fold' => ['wash-fold.jpg', 'Freshly folded cotton shirts and towels'],
        'alterations and repairs' => ['alterations.jpg', 'A sewing machine stitching a seam in green fabric'],
        'special care washing' => ['delicate-care.jpg', 'An ivory silk blouse beside a fine-knit wool sweater'],
        'ironing and pressing' => ['ironing.jpg', 'A steam iron pressing a pale-blue shirt'],
    ];
    return $photographs[strtolower(trim($service['service_name']))] ?? null;
}
function service_rows(array $services, int $limit = 0, bool $photographs = false): void {
    $services = array_values(array_filter($services,fn($service)=>count($service['options'])>0));
    if ($limit) $services = array_slice($services,0,$limit);
    $icons = ['wash','shirt','spark','leaf','shirt'];
    foreach ($services as $i=>$service): $photo = $photographs ? service_photo($service) : null; ?>
    <article class="service-row<?= $photo ? ' service-row-photo' : '' ?>"><?php if ($photo): ?><span class="service-photograph"><img src="<?= h(asset('assets/photos/'.$photo[0])) ?>" alt="<?= h($photo[1]) ?>" width="640" height="427" loading="lazy" decoding="async"></span><?php else: ?><span class="service-symbol"><?= icon($icons[$i%count($icons)],28) ?></span><?php endif ?><div class="service-copy"><h3><?= h($service['service_name']) ?></h3><p><?= h($service['description']) ?></p></div><div class="service-price"><span>From</span><strong><?= money($service['option_min']) ?></strong></div><a class="icon-button service-link" href="<?= h(url('booking.php?service='.(int)$service['service_id'])) ?>" aria-label="Book <?= h($service['service_name']) ?>"><?= icon('arrow') ?></a></article>
    <?php endforeach;
}
function field(string $name, string $label, string $type = 'text', string $value = '', array $errors = [], string $attributes = ''): void {
    $invalid = isset($errors[$name]); ?>
    <div class="field"><label for="<?= h($name) ?>"><?= h($label) ?></label><input id="<?= h($name) ?>" name="<?= h($name) ?>" type="<?= h($type) ?>" value="<?= h($value) ?>" <?= $invalid?'aria-invalid="true" aria-describedby="error-'.h($name).'"':'' ?> <?= $attributes ?>><?= error_text($errors,$name) ?></div>
<?php }
