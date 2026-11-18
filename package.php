<?php
require_once __DIR__.'/components/layout.php';$services=service_catalog();page_start('Laundry services & prices','services');
?>
<main id="main"><section class="container page-heading"><h1>Good care starts<br>with the right service.</h1><p>Explore our laundry services and item prices. Your booking total is based on the options and quantities you select.</p></section>
<section class="container section section-tight"><div class="service-list"><?php service_rows($services,0,true) ?></div></section>
<section class="container section"><div class="section-heading"><h2>A clear price<br>for every item.</h2><p>Open a service to see its options. No checkout surprises: your selected items determine the total.</p></div><div class="price-details"><?php foreach($services as $service): if(!$service['options']) continue; ?><details><summary><?= h($service['service_name']) ?><span>From <?= money($service['option_min']) ?> <?= icon('chevron',18) ?></span></summary><ul><?php foreach($service['options'] as $option): ?><li><span><?= h($option['option_name']) ?></span><strong><?= money($option['price']) ?></strong></li><?php endforeach ?></ul><a class="text-link" href="<?= h(url('booking.php?service='.(int)$service['service_id'])) ?>">Choose these services <?= icon('arrow',18) ?></a></details><?php endforeach ?></div></section>
<section class="container closing-section"><h2>Your next fresh start.</h2><a class="button button-light" href="<?= h(url('booking.php')) ?>">Book a pickup <?= icon('arrow') ?></a></section></main>
<?php page_end() ?>
