<?php

namespace App\Models;

use CodeIgniter\Model;

class StoneDetailModel extends Model
{
    protected $table            = 'stone_details';
    protected $primaryKey       = 'sid';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    // Fields that are allowed to be C.R.U.D (Create, Read, Update, Delete)
    // These match the database columns, including those with spaces.
    protected $allowedFields    = [
        'Tag Number', 'Less Wt', 'StoneName', 'Type', 'Stone ID', 
        'Pieces', 'Weight', 'Unit', 'CAL TYPE', 'Rate/Gram', 'Amount'
    ];

    // You can add validation rules here if needed
    // protected $validationRules = [];
    // protected $validationMessages = [];

    // No custom insertBatchData method needed as we'll use manual SQL in controller
}