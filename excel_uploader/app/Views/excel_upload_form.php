<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Upload Excel File<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <h2>Upload Excel File for Import</h2>

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
            <label for="excel_file">Select Excel File (.xlsx or .xls) to Upload:</label>
            <input type="file" name="excel_file" id="excel_file" class="form-control-file" required accept=".xlsx, .xls, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
        </div>

        <button type="submit" class="btn btn-primary">Upload File and Proceed to Mapping</button>
    </form>
<?= $this->endSection() ?>