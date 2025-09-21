<?php

namespace App\Modules\Payments;

use App\Core\Repository;

class PaymentsRepository extends Repository implements PaymentsRepositoryInterface
{
    public function model(): string
    {
        return PaymentsModel::class;
    }
}
