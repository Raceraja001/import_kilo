<?php

namespace App\Models;

use CodeIgniter\Model;

class TagDetailModel extends Model
{
    protected $table            = 'tag_details';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // Or 'object' or your custom class
    protected $useSoftDeletes   = false; // Set to true if you use deleted_at column

    // Fields that are allowed to be C.R.U.D (Create, Read, Update, Delete)
    protected $allowedFields    = [
        'PRODUCTID', 'Category', 'Product Name', 'Design Name', 'Sub Design Name',
        'Sub Design ID', 'Purity', 'Pieces', 'Gross Wt', 'Net Wt', 'Less Wt',
        'sales_type', 'calculation_based_on', 'Wastage Per', 'Wastage Wt',
        'MC Type', 'MC Value', 'Sales Value', 'RATEPERGRAM', 'Branch',
        'MFR Code', 'Style Code', 'Tag Number', 'Certification No', 'Purchase Cost',
        'Tag Date', 'HUID 1', 'HUID 2', 'K ID', 'KARIGAR', 'Counter',
        'TRANSIT NO', 'IMAGE FILE', 'size', 'SALEMODE', 'TAGTYPE', 'METALID',
        'MCPERGRAM', 'FMC'
    ];

    // Dates
    // protected $useTimestamps = false; // Set to true if you have created_at, updated_at
    // protected $dateFormat    = 'datetime';
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    // Validation
    // protected $validationRules      = [];
    // protected $validationMessages   = [];
    // protected $skipValidation       = false;
    // protected $cleanValidationRules = true;

    // Callbacks
    // protected $allowCallbacks = true;
    // protected $beforeInsert   = [];
    // protected $afterInsert    = [];
    // protected $beforeUpdate   = [];
    // protected $afterUpdate    = [];
    // protected $beforeFind     = [];
    // protected $afterFind      = [];
    // protected $beforeDelete   = [];
    // protected $afterDelete    = [];

    /**
     * Inserts a batch of data into the table.
     *
     * @param array $data Array of associative arrays representing rows to insert.
     * @return bool|int Returns the number of inserted rows on success, or false on failure.
     */
    public function insertBatchData(array $data)
    {
        if (empty($data)) {
            return 0;
        }
        
        // CodeIgniter's insertBatch() returns the number of rows inserted or false on failure.
        return $this->insertBatch($data);
    }
}