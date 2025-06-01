# CodeIgniter 4.5 Excel to MySQL Upload Application Plan

**Project Goal:** Develop a CodeIgniter 4.5 web application that allows users to upload an Excel file (`tag_details_rtm_final.xlsx`). The application will then read the data from this Excel file and insert it into an existing MySQL database table named `tag_details` within the `amman_new` database.

**Database Details:**
*   Host: `localhost`
*   User: `root`
*   Password: `root@123`
*   Database: `amman_new`
*   Table: `tag_details`

**Assumptions:**
*   MySQL server is running and accessible.
*   The database `amman_new` and the table `tag_details` (as per `tag_details.sql` provided by user) exist.
*   The first row of the Excel file (`tag_details_rtm_final.xlsx`) contains headers that exactly match the column names from `tag_details` (including spaces).
*   PHP and Composer are installed.

**Plan Details (Mermaid Diagram):**

```mermaid
graph TD
    A[Start: New CI 4.5 Project] --> B{Setup CodeIgniter 4.5};
    B --> C{Configure .env File};
    C --> D{Database Configuration};
    D --> E{Install Excel Library (PhpSpreadsheet via Composer)};
    E --> F{Create Controller: ExcelUploadController};
    F --> G[Define Method: index() for Upload Form View];
    F --> H[Define Method: processUpload() for File Handling];
    H --> I{Create Model: TagDetailModel};
    I --> J[Define Method: insertBatchData() for DB Insertion];
    G --> K{Create View: upload_form.php};
    K -- Displays Form --> L[User Uploads Excel File];
    L -- Submits Form --> H;
    H -- Reads Excel --> M[Parse Excel Data using Library];
    M -- Validates Data & File --> N{Data & File Valid?};
    N -- Yes --> O[Prepare Data for Batch Insert (map Excel headers to DB columns)];
    O -- Uses Model --> J;
    J -- Inserts Data --> P[MySQL Table: tag_details];
    P --> Q[Display Success Message in View];
    N -- No (File/Parse/Data Error) --> R[Display Error Message in View & Log Error];
    S[Define Routes in app/Config/Routes.php] --> G;
    S --> H;
    Q --> T[End];
    R --> T;
```

**Phase 1: Project Setup & Configuration**
1.  **Initialize CodeIgniter 4.5 Project:**
    *   Run `composer create-project codeigniter4/appstarter excel_uploader` in the terminal.
    *   Navigate into the project directory: `cd excel_uploader`.
2.  **Environment Configuration (`.env`):**
    *   Copy `env` to `.env`.
    *   In `.env`, set `CI_ENVIRONMENT = development`.
    *   Set `app.baseURL = http://localhost:8080/` (or your preferred local development URL).
3.  **Database Configuration (`.env`):**
    *   In `.env`, configure the database connection:
        ```
        database.default.hostname = localhost
        database.default.database = amman_new
        database.default.username = root
        database.default.password = root@123
        database.default.DBDriver = MySQLi
        database.default.DBPrefix =
        ```

**Phase 2: Install Dependencies**
1.  **Install PhpSpreadsheet:**
    *   Run `composer require phpoffice/phpspreadsheet` in the terminal within the `excel_uploader` project directory.

**Phase 3: Core Application Logic - MVC (Model-View-Controller)**
1.  **Create Controller (`app/Controllers/ExcelUploadController.php`):**
    *   This controller will manage the file upload form and processing logic.
    *   **`index()` method:** Loads and displays the `excel_upload_form` view.
    *   **`processUpload()` method:**
        *   Handles the POST request from the upload form.
        *   Validates the uploaded file (e.g., ensure it's an Excel file - `.xlsx`, `.xls`, check for presence, handle PHP upload errors).
        *   Uses the PhpSpreadsheet library to open and read data from the Excel file.
        *   Reads headers from the first row of the Excel sheet.
        *   Extracts data rows (starting from the second row).
        *   Prepares data for batch insertion (mapping Excel headers to database column names).
        *   Calls the `TagDetailModel` to insert the data into the database.
        *   Sets appropriate success or error messages (using session flash data) to be displayed in the view.
        *   Redirects back to the upload form or a success page.
        *   Implements logging for any errors encountered.
2.  **Create Model (`app/Models/TagDetailModel.php`):**
    *   This model will interact with the `tag_details` database table.
    *   `protected $table = 'tag_details';`
    *   `protected $primaryKey = 'id';` (Database will auto-increment this)
    *   `protected $useAutoIncrement = true;`
    *   `protected $allowedFields = [ 'PRODUCTID', 'Category', 'Product Name', 'Design Name', 'Sub Design Name', 'Sub Design ID', 'Purity', 'Pieces', 'Gross Wt', 'Net Wt', 'Less Wt', 'sales_type', 'calculation_based_on', 'Wastage Per', 'Wastage Wt', 'MC Type', 'MC Value', 'Sales Value', 'RATEPERGRAM', 'Branch', 'MFR Code', 'Style Code', 'Tag Number', 'Certification No', 'Purchase Cost', 'Tag Date', 'HUID 1', 'HUID 2', 'K ID', 'KARIGAR', 'Counter', 'TRANSIT NO', 'IMAGE FILE', 'size', 'SALEMODE', 'TAGTYPE', 'METALID', 'MCPERGRAM', 'FMC' ];` (These must match Excel headers and DB columns).
    *   **`insertBatchData(array $data)` method:** Uses CodeIgniter's `$this->insertBatch($data)` for efficient bulk insertion. It should return a status indicating success or failure.
3.  **Create View (`app/Views/excel_upload_form.php`):**
    *   An HTML form for users to upload the Excel file.
    *   The form tag should be: `<form action="<?= site_url('excel/process') ?>" method="post" enctype="multipart/form-data">`.
    *   Include a file input: `<input type="file" name="excel_file" required>`.
    *   Include a submit button.
    *   Display any session flash messages (success/error) passed from the controller.

**Phase 4: Routing (`app/Config/Routes.php`)**
1.  **Define Routes:**
    *   Add a route for displaying the upload form: `$routes->get('/excel', 'ExcelUploadController::index');`
    *   Add a route for processing the uploaded file: `$routes->post('/excel/process', 'ExcelUploadController::processUpload');`

**Phase 5: Refined Error Handling Strategy**
*   **File Upload Validation (Controller):**
    *   Check for no file selected.
    *   Validate file type (allow only `.xlsx`, `.xls`).
    *   Check against file size limits (PHP's `upload_max_filesize` and `post_max_size`).
    *   Handle other standard PHP upload errors (log specific error code, show generic user message).
*   **Excel File Parsing (Controller):**
    *   Handle cases where PhpSpreadsheet cannot load the file (e.g., corrupted file, invalid format). Log specific exceptions.
    *   Check for empty Excel files (no data rows beyond the header).
*   **Data Validation (Controller - Basic):**
    *   Primarily relies on Excel headers matching database columns. More complex row/cell level validation can be added if needed.
*   **Database Insertion (Model & Controller):**
    *   Handle failures from the `insertBatch()` operation.
    *   Log actual database error messages and codes.
*   **Logging:**
    *   Use CodeIgniter's `log_message('error', 'Descriptive error message with context');` for all significant errors.
*   **User Feedback (View):**
    *   Use CodeIgniter's Session library for flash messages (`session()->setFlashdata('error', 'Error message');` or `session()->setFlashdata('success', 'Success message');`).
    *   Display these messages clearly in the `excel_upload_form.php` view.

This plan provides a structured approach to developing the Excel upload functionality.