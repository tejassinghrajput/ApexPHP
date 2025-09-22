<?php

use App\Modules\Invoices\InvoicesController;

// The $router variable is provided by the application kernel.
$router->get('/api/invoices', [InvoicesController::class, 'index']);
$router->get('/api/invoices/{id}', [InvoicesController::class, 'show']);
$router->post('/api/invoices', [InvoicesController::class, 'store']);
$router->put('/api/invoices/{id}', [InvoicesController::class, 'update']);
$router->delete('/api/invoices/{id}', [InvoicesController::class, 'destroy']);
