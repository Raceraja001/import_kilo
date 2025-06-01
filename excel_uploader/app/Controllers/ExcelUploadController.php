<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TagDetailModel;
use App\Models\StoneDetailModel; // Added for stone details
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelUploadController extends BaseController
{
    public function index()
    {
        return view('excel_upload_form'); // View will be created later
    }

    public function processUpload()
    {
        // Increase execution time and memory limit for potentially large files/operations
        ini_set('max_execution_time', 300); // 300 seconds = 5 minutes
        ini_set('memory_limit', '1024M'); // Increased memory limit as per request

        $file = $this->request->getFile('excel_file'); // Define $file first
        log_message('info', 'ExcelUploadController::processUpload() started. File: ' . ($file ? $file->getName() : 'No file object'));
        $session = session();

        // 1. File Upload Validation
        if (!$file || !$file->isValid()) {
            if ($file && $file->getError() === UPLOAD_ERR_NO_FILE) {
                $session->setFlashdata('error', 'Please select an Excel file to upload.');
            } else {
                $session->setFlashdata('error', 'Invalid file upload. Error: ' . ($file ? $file->getErrorString() : 'Unknown error') . ' (' . ($file ? $file->getError() : '') . ')');
            }
            return redirect()->to('/excel');
        }

        if ($file->hasMoved()) {
            $session->setFlashdata('error', 'File has already been moved. Cannot process.');
            return redirect()->to('/excel');
        }
        
        $fileMimeType = $file->getMimeType();
        $fileExtension = $file->getClientExtension();
        $allowedMimeTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-excel', // .xls
            'application/octet-stream' // Sometimes .xls or .xlsx might be served as octet-stream
        ];
        $allowedExtensions = ['xlsx', 'xls'];

        if (!in_array(strtolower($fileExtension), $allowedExtensions) || !in_array($fileMimeType, $allowedMimeTypes, true)) {
             // Fallback for octet-stream if extension is correct
            if ($fileMimeType === 'application/octet-stream' && in_array(strtolower($fileExtension), $allowedExtensions)) {
                // Potentially allow, but log it
                log_message('info', 'File uploaded with octet-stream type but correct extension: ' . $file->getName());
            } else {
                $session->setFlashdata('error', 'Invalid file type. Only .xlsx or .xls files are allowed. Uploaded type: '.$fileMimeType.' Ext: '.$fileExtension);
                return redirect()->to('/excel');
            }
        }

        // 2. Excel File Parsing
        $dataToInsert = [];
        try {
            log_message('info', 'Attempting to parse Excel file: ' . $file->getName() . ' (Temp path: ' . $file->getTempName() . ')');
            $spreadsheet = IOFactory::load($file->getTempName());
            $worksheet = $spreadsheet->getActiveSheet();
            
            $headerRow = [];
            $firstRow = $worksheet->getRowIterator()->current();
            foreach ($firstRow->getCellIterator() as $cell) {
                $headerValue = trim((string)$cell->getValue());
                if (!empty($headerValue)) { // Only add non-empty headers
                    $headerRow[] = $headerValue;
                }
            }

            if (empty($headerRow)) {
                $session->setFlashdata('error', 'Excel file is empty or header row could not be read.');
                return redirect()->to('/excel');
            }

            $rows = $worksheet->getRowIterator();
            $isFirstRow = true; // To skip header row during data extraction

            foreach ($rows as $row) {
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue;
                }

                $rowData = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // Iterate all cells in header range

                $colIndex = 0;
                $emptyRow = true;
                foreach ($cellIterator as $cell) {
                    if ($colIndex < count($headerRow)) {
                        $cellValue = $cell->getValue();
                        $currentHeader = $headerRow[$colIndex]; // Header already trimmed when $headerRow was populated
                        $rowData[$currentHeader] = $cellValue;
                        if (!empty($cellValue)) {
                            $emptyRow = false;
                        }
                    }
                    $colIndex++;
                    if ($colIndex >= count($headerRow)) break; // Only read as many cells as there are headers
                }
                
                if (!$emptyRow) { // Add row only if it's not completely empty
                    $dataToInsert[] = $rowData;
                }
            }

            if (empty($dataToInsert)) {
                $session->setFlashdata('error', 'No data found in the Excel file (after headers).');
                return redirect()->to('/excel');
            }

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            log_message('error', 'Error reading Excel file: ' . $e->getMessage());
            $session->setFlashdata('error', 'Could not read the Excel file. It might be corrupted or not a valid Excel format.');
            return redirect()->to('/excel');
        } catch (\Exception $e) {
            log_message('error', 'An unexpected error occurred during Excel processing: ' . $e->getMessage());
            $session->setFlashdata('error', 'An unexpected error occurred. Please try again.');
            return redirect()->to('/excel');
        }

        // --- Start: Added for Dynamic Mapping (Step 1) ---
        $dbForMapping = \Config\Database::connect();
        $currentTableName = 'tag_details'; // Target table for this method
        
        if ($dbForMapping->tableExists($currentTableName)) {
            $dbFields = $dbForMapping->getFieldNames($currentTableName);
            log_message('info', '[PROCESS_UPLOAD] Database columns for table "' . $currentTableName . '": ' . implode(', ', $dbFields));
        } else {
            log_message('error', '[PROCESS_UPLOAD] Target table for mapping "' . $currentTableName . '" does not exist.');
            // Consider setting a flash error and redirecting if table non-existence is critical here
        }
        log_message('info', '[PROCESS_UPLOAD] Excel headers found: ' . implode(', ', $headerRow));
        // --- End: Added for Dynamic Mapping (Step 1) ---

        // Store file temporarily and redirect to mapping form
        $tempFilePath = WRITEPATH . 'uploads/' . $file->getName();
        $file->move(WRITEPATH . 'uploads', $file->getName()); // Move the uploaded file to writable/uploads

        $session->setFlashdata('excelHeaders', $headerRow);
        $session->setFlashdata('uploadedFilePath', $tempFilePath);
        $session->setFlashdata('targetTable', 'tag_details'); // Indicate target table

        return redirect()->to('/excel/map'); // Redirect to the new mapping route
    }

    public function displayMappingForm()
    {
        $session = session();
        $excelHeaders = $session->getFlashdata('excelHeaders');
        $uploadedFilePath = $session->getFlashdata('uploadedFilePath');
        $tableName = $session->getFlashdata('targetTable');

        if (!$excelHeaders || !$uploadedFilePath || !$tableName) {
            $session->setFlashdata('error', 'No Excel file or mapping data found. Please upload a file first.');
            return redirect()->to('/excel');
        }

        $db = \Config\Database::connect();
        $dbColumns = [];
        if ($db->tableExists($tableName)) {
            $dbColumns = $db->getFieldNames($tableName);
        } else {
            $session->setFlashdata('error', 'Target database table "' . esc($tableName) . '" does not exist.');
            return redirect()->to('/excel');
        }

        $data = [
            'excelHeaders' => $excelHeaders,
            'dbColumns' => $dbColumns,
            'tableName' => $tableName,
            'uploadedFilePath' => $uploadedFilePath,
        ];

        return view('mapping_form', $data);
    }

    public function confirmMapping()
    {
        // Increase execution time and memory limit for potentially large files/operations
        ini_set('max_execution_time', 300); // 300 seconds = 5 minutes
        ini_set('memory_limit', '1024M'); // Increased memory limit as per request

        $session = session();
        $request = $this->request;

        $mapping = $request->getPost('mapping');
        $targetTable = $request->getPost('target_table');
        $uploadedFilePath = $request->getPost('uploaded_file_path');
        $originalExcelHeadersJson = $request->getPost('original_excel_headers');
        $originalExcelHeaders = json_decode($originalExcelHeadersJson, true);

        if (!$mapping || !$targetTable || !$uploadedFilePath || !file_exists($uploadedFilePath) || !$originalExcelHeaders) {
            $session->setFlashdata('error', 'Invalid mapping submission or file not found. Please try again.');
            return redirect()->to('/excel');
        }

        $dataToInsert = [];
        try {
            $spreadsheet = IOFactory::load($uploadedFilePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $rows = $worksheet->getRowIterator();
            $isFirstRow = true;

            foreach ($rows as $row) {
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue; // Skip header row
                }

                $rowData = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $colIndex = 0;
                $emptyRow = true;
                foreach ($cellIterator as $cell) {
                    if ($colIndex < count($originalExcelHeaders)) {
                        $excelHeader = $originalExcelHeaders[$colIndex];
                        $dbColumn = $mapping[$excelHeader] ?? null; // Get mapped DB column
                        
                        if (!empty($dbColumn)) { // Only include if mapped and not 'Do Not Import'
                            $cellValue = $cell->getValue();
                            $rowData[$dbColumn] = $cellValue;
                            if (!empty($cellValue)) {
                                $emptyRow = false;
                            }
                        }
                    }
                    $colIndex++;
                    if ($colIndex >= count($originalExcelHeaders)) break;
                }
                
                if (!$emptyRow) {
                    $dataToInsert[] = $rowData;
                }
            }

            // Clean up the temporary file after processing
            unlink($uploadedFilePath);

            if (empty($dataToInsert)) {
                $session->setFlashdata('error', 'No data found in the Excel file after applying mapping.');
                return redirect()->to('/excel');
            }

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            log_message('error', 'Error re-reading Excel file for mapping confirmation: ' . $e->getMessage());
            $session->setFlashdata('error', 'Could not re-read the Excel file for import. It might be corrupted or not a valid Excel format.');
            return redirect()->to('/excel');
        } catch (\Exception $e) {
            log_message('error', 'An unexpected error occurred during mapping confirmation processing: ' . $e->getMessage());
            $session->setFlashdata('error', 'An unexpected error occurred during mapping confirmation. Please try again.');
            return redirect()->to('/excel');
        }

        // Perform batch insertion based on target table
        $successfullyInsertedCount = 0;
        $totalRowsToInsert = count($dataToInsert);
        $failedRows = 0;

        try {
            if ($targetTable === 'tag_details') {
                $model = new TagDetailModel();
                // Filter out 'id' for tag_details
                $filteredDataToInsert = array_map(function($row) {
                    if (array_key_exists('id', $row)) {
                        unset($row['id']);
                    }
                    return $row;
                }, $dataToInsert);
                $filteredDataToInsert = array_filter($filteredDataToInsert, function($row) { return !empty($row); });
                $successfullyInsertedCount = $model->insertBatchData($filteredDataToInsert);

            } elseif ($targetTable === 'stone_details') {
                $model = new StoneDetailModel();
                // Filter out 'sid' and validate 'Tag Number' for stone_details
                $filteredDataToInsert = array_map(function($row) {
                    if (array_key_exists('sid', $row)) {
                        unset($row['sid']);
                    }
                    if (!isset($row['Tag Number']) || empty($row['Tag Number'])) {
                        log_message('warning', '[StoneUpload] Skipping row due to missing or empty Tag Number during mapped import.');
                        return null;
                    }
                    return $row;
                }, $dataToInsert);
                $filteredDataToInsert = array_filter($filteredDataToInsert);
                $successfullyInsertedCount = $model->insertBatchData($filteredDataToInsert);
            } else {
                $session->setFlashdata('error', 'Invalid target table specified for import.');
                return redirect()->to('/excel');
            }

            if ($successfullyInsertedCount === false) {
                $db = \Config\Database::connect();
                $dbError = $db->error();
                $errorMsg = 'Database batch insertion failed for ' . $targetTable . ': ' . ($dbError['message'] ?? 'Unknown database error');
                log_message('error', $errorMsg . ' DB Error: ' . print_r($dbError, true));
                $session->setFlashdata('error', 'Failed to import data due to a database error. Please check logs for details.');
                return redirect()->to('/excel');
            }

            $failedRows = $totalRowsToInsert - $successfullyInsertedCount;

            if ($successfullyInsertedCount > 0) {
                $session->setFlashdata('success', $successfullyInsertedCount . ' out of ' . $totalRowsToInsert . ' data rows successfully imported into ' . $targetTable . '.');
            }
            
            if ($failedRows > 0) {
                $existingError = $session->getFlashdata('error') ?? '';
                $newErrorSummary = $failedRows . ' data rows failed to import or were not affected for ' . $targetTable . '.';
                if($successfullyInsertedCount > 0 && !empty($existingError)) {
                     $session->setFlashdata('error', $existingError . '<br/>Additionally, ' . $newErrorSummary);
                } elseif ($successfullyInsertedCount > 0 && empty($existingError)) {
                    $currentSuccess = $session->getFlashdata('success');
                    $session->setFlashdata('success', $currentSuccess . ($currentSuccess ? '<br/>' : '') . 'However, ' . $newErrorSummary);
                } else {
                     $session->setFlashdata('error', ($existingError ? $existingError . '<br/>' : '') . $newErrorSummary);
                }
                log_message('error', $newErrorSummary . ' (Batch insert). Check database logs for more specific errors if available.');
            } elseif ($successfullyInsertedCount === 0 && $totalRowsToInsert > 0) {
                $session->setFlashdata('info', 'No data rows were imported into ' . $targetTable . '. All rows might have failed or were empty. Check logs.');
            }

        } catch (\Throwable $e) {
            log_message('error', 'Exception during batch insertion for ' . $targetTable . ': ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            $session->setFlashdata('error', 'An unexpected error occurred during database insertion for ' . $targetTable . '. Please try again.');
            return redirect()->to('/excel');
        }

        return redirect()->to('/excel');
    }

    // Methods for Stone Details Upload
    public function stoneIndex()
    {
        // Log access to this new method
        log_message('info', 'ExcelUploadController::stoneIndex() called.');
        return view('stone_upload_form'); // New view for stone details
    }

    public function processStoneUpload()
    {
        // Increase execution time and memory limit
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '1024M'); // As per user request

        $file = $this->request->getFile('excel_file'); // Assuming same file input name
        log_message('info', 'ExcelUploadController::processStoneUpload() started. File: ' . ($file ? $file->getName() : 'No file object'));
        $session = session();

        // 1. File Upload Validation (same as processUpload)
        if (!$file || !$file->isValid()) {
            if ($file && $file->getError() === UPLOAD_ERR_NO_FILE) {
                $session->setFlashdata('error', 'Please select an Excel file for stone details to upload.');
            } else {
                $session->setFlashdata('error', 'Invalid file upload for stone details. Error: ' . ($file ? $file->getErrorString() : 'Unknown error') . ' (' . ($file ? $file->getError() : '') . ')');
            }
            return redirect()->to('/excel/stone'); // Redirect to stone upload form
        }

        if ($file->hasMoved()) {
            $session->setFlashdata('error', 'File for stone details has already been moved. Cannot process.');
            return redirect()->to('/excel/stone');
        }
        
        $fileMimeType = $file->getMimeType();
        $fileExtension = $file->getClientExtension();
        $allowedMimeTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-excel', // .xls
            'application/octet-stream'
        ];
        $allowedExtensions = ['xlsx', 'xls'];

        if (!in_array(strtolower($fileExtension), $allowedExtensions) || !in_array($fileMimeType, $allowedMimeTypes, true)) {
            if ($fileMimeType === 'application/octet-stream' && in_array(strtolower($fileExtension), $allowedExtensions)) {
                log_message('info', 'Stone details file uploaded with octet-stream type but correct extension: ' . $file->getName());
            } else {
                $session->setFlashdata('error', 'Invalid file type for stone details. Only .xlsx or .xls files are allowed. Uploaded type: '.$fileMimeType.' Ext: '.$fileExtension);
                return redirect()->to('/excel/stone');
            }
        }

        // 2. Excel File Parsing (same logic as processUpload)
        $dataToInsert = [];
        try {
            log_message('info', 'Attempting to parse stone details Excel file: ' . $file->getName() . ' (Temp path: ' . $file->getTempName() . ')');
            $spreadsheet = IOFactory::load($file->getTempName());
            $worksheet = $spreadsheet->getActiveSheet();
            
            $headerRow = [];
            $firstRow = $worksheet->getRowIterator()->current();
            foreach ($firstRow->getCellIterator() as $cell) {
                $headerValue = trim((string)$cell->getValue());
                if (!empty($headerValue)) {
                    $headerRow[] = $headerValue;
                }
            }

            if (empty($headerRow)) {
                $session->setFlashdata('error', 'Stone details Excel file is empty or header row could not be read.');
                return redirect()->to('/excel/stone');
            }

            $rows = $worksheet->getRowIterator();
            $isFirstRow = true;

            foreach ($rows as $row) {
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue;
                }
                $rowData = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $colIndex = 0;
                $emptyRow = true;
                foreach ($cellIterator as $cell) {
                    if ($colIndex < count($headerRow)) {
                        $cellValue = $cell->getValue();
                        $currentHeader = $headerRow[$colIndex];
                        $rowData[$currentHeader] = $cellValue;
                        if (!empty($cellValue)) {
                            $emptyRow = false;
                        }
                    }
                    $colIndex++;
                    if ($colIndex >= count($headerRow)) break;
                }
                if (!$emptyRow) {
                    $dataToInsert[] = $rowData;
                }
            }

            if (empty($dataToInsert)) {
                $session->setFlashdata('error', 'No data found in the stone details Excel file (after headers).');
                return redirect()->to('/excel/stone');
            }

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            log_message('error', '[StoneUpload] Error reading Excel file: ' . $e->getMessage());
            $session->setFlashdata('error', 'Could not read the stone details Excel file. It might be corrupted or not a valid Excel format.');
            return redirect()->to('/excel/stone');
        } catch (\Exception $e) {
            log_message('error', '[StoneUpload] An unexpected error occurred during Excel processing: ' . $e->getMessage());
            $session->setFlashdata('error', 'An unexpected error occurred while processing stone details. Please try again.');
            return redirect()->to('/excel/stone');
        }

        // Store file temporarily and redirect to mapping form for stone details
        $tempFilePath = WRITEPATH . 'uploads/' . $file->getName();
        $file->move(WRITEPATH . 'uploads', $file->getName());

        $session->setFlashdata('excelHeaders', $headerRow);
        $session->setFlashdata('uploadedFilePath', $tempFilePath);
        $session->setFlashdata('targetTable', 'stone_details'); // Indicate target table

        return redirect()->to('/excel/map'); // Redirect to the new mapping route
    }
}