<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->call('config:clear');
$app->make(Illuminate\Contracts\Console\Kernel::class)->call('cache:clear');

echo "Cleared";
