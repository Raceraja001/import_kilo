<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Excel File</title>
    <!-- Optional: Add some basic styling (e.g., Bootstrap) -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
        .container {
            max-width: 600px;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Upload Excel to Import Tag Details</h2>

        <?php $session = session(); ?>

        <?php if ($session->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert">
                <?= $session->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if ($session->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert">
                <?= $session->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (is_string($session->getFlashdata('errors_list'))): // For validation errors string ?>
            <div class="alert alert-danger" role="alert">
                <?= $session->getFlashdata('errors_list') ?>
            </div>
        <?php elseif (is_array($session->getFlashdata('errors_list'))): // For validation errors array ?>
            <div class="alert alert-danger" role="alert">
                <h4>Validation Errors:</h4>
                <ul>
                    <?php foreach ($session->getFlashdata('errors_list') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>


        <form action="<?= site_url('excel/process') ?>" method="post" enctype="multipart/form-data" class="mt-4">
            <?= csrf_field() ?> <!-- Add CSRF protection -->
            
            <div class="form-group">
                <label for="excel_file">Select Excel File (.xlsx or .xls):</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control-file" required accept=".xlsx, .xls, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
            </div>

            <button type="submit" class="btn btn-primary">Upload and Process</button>
        </form>
    </div>

    <!-- Optional: Add Bootstrap JS and dependencies if needed for other components -->
    <!--
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    -->
</body>
</html>