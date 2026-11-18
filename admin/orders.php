<?php
require_once __DIR__.'/../db/app.php';
require_admin();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { require __DIR__.'/actions.php'; exit; }
redirect('admin/adminDash.php?tab=orders');
