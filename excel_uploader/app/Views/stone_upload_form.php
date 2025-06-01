<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Stone Details Excel</title>
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
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="<?= site_url('/excel') ?>">Upload Tag Details</a>
            <a href="<?= site_url('/excel/stone') ?>">Upload Stone Details</a>
        </div>

        <h2>Upload Stone Details Excel to Import</h2>

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

        <?php if (is_string($session->getFlashdata('errors_list'))): ?>
            <div class="alert alert-danger" role="alert">
                <?= $session->getFlashdata('errors_list') ?>
            </div>
        <?php elseif (is_array($session->getFlashdata('errors_list'))): ?>
            <div class="alert alert-danger" role="alert">
                <h4>Specific Row Errors:</h4>
                <ul>
                    <?php foreach ($session->getFlashdata('errors_list') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($session->getFlashdata('info')): ?>
            <div class="alert alert-info" role="alert">
                <?= $session->getFlashdata('info') ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('excel/stone/process') ?>" method="post" enctype="multipart/form-data" class="mt-4">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="excel_file">Select Stone Details Excel File (.xlsx or .xls):</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control-file" required accept=".xlsx, .xls, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
            </div>

            <button type="submit" class="btn btn-primary">Upload and Process Stone Details</button>
        </form>
    </div>
</body>
</html>