<?php

namespace App\Modules\Invoices;

use App\Core\ApexORM\Model;

class InvoicesModel extends Model
{
    /**
     * The attributes that are mass assignable for security.
     * @var array
     */
    protected array $fillable = ['amount', 'description'];

    /**
     * The database schema for the model.
     * @var array
     */
    protected static array $schema = [
        'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
        'amount' => 'DECIMAL(10, 2) NOT NULL',
        'description' => 'TEXT',
        'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
    ];
}
