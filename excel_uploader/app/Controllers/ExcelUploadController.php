<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TagDetailModel; // Will be created later
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

        // 3. Database Insertion (Manual SQL Row by Row)
        log_message('info', 'Excel parsing complete. Found ' . count($dataToInsert) . ' data rows to process for database insertion.');
        $totalRowsToInsert = count($dataToInsert);
        $successfullyInsertedCount = 0;
        $failedRows = 0;
        $rowErrors = []; // Store errors for specific rows

        if ($totalRowsToInsert > 0) {
            $db = \Config\Database::connect(); // Get default database connection
            $tableName = 'tag_details'; // Define table name

            log_message('info', 'Starting database insertion loop for ' . $totalRowsToInsert . ' rows.');
            foreach ($dataToInsert as $index => $rowData) {
                log_message('debug', 'Processing Excel row ' . ($index + 2) . ' for database insertion.'); // +2 because $index is 0-based and 1st Excel row is header
                $insertData = $rowData; // Make a copy

                // Exclude 'id' field if present, as it's auto-increment
                // Check if 'id' key exists and if its value suggests it's a placeholder from Excel (e.g., numeric)
                // For robust check, ensure it's not a meaningful ID if updates were ever intended.
                // For this import, we assume new records, so any 'id' from Excel is ignored.
                if (array_key_exists('id', $insertData)) {
                    unset($insertData['id']);
                }

                if (empty($insertData)) {
                    log_message('info', 'Skipping empty row data at Excel row ' . ($index + 2) . ' after potential ID removal.');
                    // $failedRows++; // Optionally count this as a failed/skipped row
                    // $rowErrors[] = 'Row ' . ($index + 2) . ' (Excel row) was empty after ID removal.';
                    continue;
                }

                $fields = [];
                $values = [];
                foreach ($insertData as $key => $value) {
                    // Ensure correct quoting for field names (keys)
                    $fields[] = "`" . str_replace("`", "``", trim((string)$key)) . "`";
                    // Ensure correct escaping for values
                    $values[] = $db->escape($value);
                }

                $sql = "INSERT INTO `" . $tableName . "` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
                log_message('debug', 'Manual SQL for Excel row ' . ($index + 2) . ': ' . $sql);
                
                try {
                    if ($db->query($sql)) {
                        if ($db->affectedRows() > 0) {
                            $successfullyInsertedCount++;
                            log_message('info', 'Successfully inserted Excel row ' . ($index + 2) . '.');
                        } else {
                            // Query executed but no rows affected - could be an issue or an empty valid insert
                            $failedRows++;
                            $errorMsg = 'Row ' . ($index + 2) . ' (Excel row) executed but no rows affected.';
                            $rowErrors[] = $errorMsg;
                            log_message('warning', $errorMsg . ' SQL: ' . $sql . ' Data: ' . print_r($rowData, true));
                        }
                    } else {
                        $failedRows++;
                        $dbError = $db->error(); // Get error info from the connection
                        $errorMsg = 'Row ' . ($index + 2) . ' (Excel row) failed: ' . ($dbError['message'] ?? 'Unknown database error');
                        $rowErrors[] = $errorMsg;
                        log_message('error', $errorMsg . ' SQL: ' . $sql . ' Data: ' . print_r($rowData, true) . ' DB Error: ' . print_r($dbError, true));
                    }
                } catch (\Throwable $e) {
                    $failedRows++;
                    $errorMsg = 'Row ' . ($index + 2) . ' (Excel row) failed with exception: ' . $e->getMessage();
                    $rowErrors[] = $errorMsg;
                    log_message('error', $errorMsg . ' SQL: ' . $sql . ' Data: ' . print_r($rowData, true) . ' Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
                }
            }
            log_message('info', 'Database insertion loop finished. Successfully inserted: ' . $successfullyInsertedCount . ', Failed: ' . $failedRows . ' out of ' . $totalRowsToInsert . ' attempted.');

            if ($successfullyInsertedCount > 0) {
                $session->setFlashdata('success', $successfullyInsertedCount . ' out of ' . $totalRowsToInsert . ' data rows successfully imported.');
            }
            
            if ($failedRows > 0) {
                $existingError = $session->getFlashdata('error') ?? ''; // Preserve any previous general error
                $newErrorSummary = $failedRows . ' data rows failed to import or were not affected.';
                // If there was a success message, clear it if there were also failures, or append.
                if($successfullyInsertedCount > 0 && !empty($existingError)) {
                     $session->setFlashdata('error', $existingError . '<br/>Additionally, ' . $newErrorSummary);
                } elseif ($successfullyInsertedCount > 0 && empty($existingError)) {
                    // If only success was set, and now we have errors, overwrite success with error summary or append to it.
                    // For simplicity, let error summary take precedence or be appended.
                    $currentSuccess = $session->getFlashdata('success');
                    $session->setFlashdata('success', $currentSuccess . ($currentSuccess ? '<br/>' : '') . 'However, ' . $newErrorSummary);

                } else {
                     $session->setFlashdata('error', ($existingError ? $existingError . '<br/>' : '') . $newErrorSummary);
                }
                $session->setFlashdata('errors_list', $rowErrors);
                log_message('error', $newErrorSummary . ' See previous log entries for details on each failed row.');
            } elseif ($successfullyInsertedCount === 0 && $totalRowsToInsert > 0) {
                if(empty($rowErrors)){
                    $session->setFlashdata('info', 'No data rows were imported. All rows might have failed or were empty. Check logs.');
                } else {
                    // errors_list will be set if rowErrors had content, and error summary should also be set
                     $session->setFlashdata('error', ($session->getFlashdata('error') ?? '') . ($failedRows > 0 ? '' : ' All rows failed. Check detailed errors.'));
                }
            }

        } else {
            // This case means $dataToInsert was empty after parsing.
            $session->setFlashdata('info', 'No processable data was extracted from the Excel file.');
        }

        return redirect()->to('/excel');
    }
}