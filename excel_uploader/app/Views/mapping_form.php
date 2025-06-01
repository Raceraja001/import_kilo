<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map Excel Columns to Database Fields</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .container { max-width: 960px; } /* Increased max-width for more page usage */
        .alert { margin-top: 20px; }
        .mapping-table th, .mapping-table td { padding: 8px; border: 1px solid #dee2e6; }
        .mapping-table th { background-color: #f8f9fa; }
        .mapping-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Map Excel Columns to Database Fields</h2>

        <?php $session = session(); ?>
        <?php if ($session->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert">
                <?= $session->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <p>Please map each Excel column to its corresponding database field in the '<strong><?= esc($tableName) ?></strong>' table. Select 'Do Not Import' for columns you wish to skip.</p>

        <form action="<?= site_url('excel/confirm_mapping') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="target_table" value="<?= esc($tableName) ?>">
            <input type="hidden" name="uploaded_file_path" value="<?= esc($uploadedFilePath) ?>">
            <input type="hidden" name="original_excel_headers" value="<?= esc(json_encode($excelHeaders)) ?>">

            <table class="table table-bordered mapping-table">
                <thead>
                    <tr>
                        <th>Excel Column Header</th>
                        <th>Database Field</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($excelHeaders as $index => $excelHeader): ?>
                        <tr>
                            <td><?= esc($excelHeader) ?></td>
                            <td>
                                <select name="mapping[<?= esc($excelHeader) ?>]" class="form-control">
                                    <option value="">-- Do Not Import --</option>
                                    <?php foreach ($dbColumns as $dbColumn): ?>
                                        <option value="<?= esc($dbColumn) ?>"
                                            <?php if (strtolower(str_replace(' ', '', $excelHeader)) === strtolower(str_replace(' ', '', $dbColumn))): ?>
                                                selected
                                            <?php endif; ?>
                                        ><?= esc($dbColumn) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary mt-3" id="confirmMappingBtn">Import Data with Mapped Fields</button>
            <div id="loadingIndicator" style="display:none; margin-top: 15px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <span class="ml-2">Processing your data, please wait... This may take a few moments for large files.</span>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('confirmMappingBtn').addEventListener('click', function() {
            // Show loading indicator and disable button on form submission
            document.getElementById('loadingIndicator').style.display = 'block';
            this.disabled = true;
            this.form.submit(); // Manually submit the form
        });
    </script>
    </div>
</body>
</html>