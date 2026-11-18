<?php require_once __DIR__.'/components/layout.php';http_response_code(404);page_start('Page not found'); ?>
<main id="main" class="container empty-state"><h1>This page took<br>a wrong turn.</h1><p>The page you are looking for could not be found.</p><a class="button" href="<?= h(url('home.php')) ?>">Back to home <?= icon('arrow') ?></a></main>
<?php page_end() ?>
