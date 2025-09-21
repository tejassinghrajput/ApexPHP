<?php
namespace App\Modules\Payments;
use App\Core\ApexORM\Model;
class PaymentsModel extends Model { protected static array $schema = ['id' => 'INTEGER PRIMARY KEY AUTOINCREMENT', 'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP']; }