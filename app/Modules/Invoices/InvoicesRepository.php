<?php

namespace App\Modules\Invoices;

use App\Core\Repository;

class InvoicesRepository extends Repository implements InvoicesRepositoryInterface
{
    public function model(): string
    {
        return InvoicesModel::class;
    }
}
