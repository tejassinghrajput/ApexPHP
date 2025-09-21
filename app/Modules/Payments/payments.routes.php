<?php
use App\Modules\Payments\PaymentsController;

$router->get('/api/payments', [PaymentsController::class, 'index']);
$router->get('/api/payments/{id}', [PaymentsController::class, 'show']);