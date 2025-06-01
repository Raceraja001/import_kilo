<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Excel uploader routes
$routes->get('/excel', 'ExcelUploadController::index');
$routes->post('/excel/process', 'ExcelUploadController::processUpload');
