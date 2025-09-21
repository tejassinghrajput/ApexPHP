<?php
namespace App\Modules\Payments;
use App\Core\ApexORM\Model;
class PaymentsModel extends Model {
    protected array $fillable = ['amount', 'description'];
    protected static array $schema = ['id' => 'INTEGER PRIMARY KEY AUTOINCREMENT', 'amount' => 'DECIMAL(10, 2) NOT NULL', 'description' => 'TEXT', 'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'];
}