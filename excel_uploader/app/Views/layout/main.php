<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> | Excel Uploader Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 56px; /* Space for fixed navbar */
            background-color: #f8f9fa;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .container-fluid {
            padding-left: 30px;
            padding-right: 30px;
        }
        .content-section {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .nav-link {
            font-weight: 500;
        }
        .nav-link.active {
            color: #007bff !important;
            border-bottom: 2px solid #007bff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="<?= site_url('/') ?>">Excel Uploader</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link <?= url_is('excel') ? 'active' : '' ?>" href="<?= site_url('excel') ?>">Upload Tag Details</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= url_is('excel/stone') ? 'active' : '' ?>" href="<?= site_url('excel/stone') ?>">Upload Stone Details</a>
                </li>
                <!-- Add more navigation items here if needed -->
            </ul>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="content-section">
            <?= $this->renderSection('content') ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>