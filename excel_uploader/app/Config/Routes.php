<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Excel uploader routes
$routes->get('/excel', 'ExcelUploadController::index');
$routes->post('/excel/process', 'ExcelUploadController::processUpload');

// Stone Details Excel uploader routes
$routes->get('/excel/stone', 'ExcelUploadController::stoneIndex');
$routes->post('/excel/stone/process', 'ExcelUploadController::processStoneUpload');

// Dynamic Mapping routes
$routes->get('/excel/map', 'ExcelUploadController::displayMappingForm');
$routes->post('/excel/confirm_mapping', 'ExcelUploadController::confirmMapping');
