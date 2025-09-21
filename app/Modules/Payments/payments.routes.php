<?php
use App\Modules\Payments\PaymentsController;
$router->get('/api/payments', [PaymentsController::class, 'index']);
$router->get('/api/payments/{id}', [PaymentsController::class, 'show']);
$router->post('/api/payments', [PaymentsController::class, 'store']);
$router->put('/api/payments/{id}', [PaymentsController::class, 'update']);
$router->delete('/api/payments/{id}', [PaymentsController::class, 'destroy']);