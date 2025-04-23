<?php
// Lokasi: public/index.php

// --- Definisikan Konstanta Path ---
// FCPATH: Front Controller Path (Folder public)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
// APPROOT: Didefinisikan di config.php, JANGAN definisikan lagi di sini
// define('APPROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app'); // <-- HAPUS ATAU KOMENTARI BARIS INI
define('ROOTPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR); // Path ke root proyek

// --- Muat Konfigurasi (APPROOT & APP_ENV sudah didefinisikan di sini) ---
require_once dirname(__DIR__) . '/config/config.php';

// --- Deteksi Environment (Sekarang APP_ENV sudah ada) ---
define('ENVIRONMENT', APP_ENV ?? 'development'); // Fallback jika APP_ENV tidak terdefinisi di config

// --- Konfigurasi Error Reporting Berdasarkan Environment ---
switch (ENVIRONMENT) {
	case 'development':
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		break;
	case 'testing':
	case 'production':
		error_reporting(0);
		ini_set('display_errors', 0);
		ini_set('log_errors', 1);
		$logPath = ROOTPATH . 'logs/php_error.log';
		if (!is_dir(dirname($logPath))) { @mkdir(dirname($logPath), 0775, true); } // Gunakan @ untuk menekan warning jika folder sudah ada
		ini_set('error_log', $logPath);
		break;
	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Environment aplikasi tidak valid.';
		exit(1);
}


// --- Muat Autoloader Composer ---
require_once dirname(__DIR__) . '/vendor/autoload.php';

// --- Muat Helper Functions ---
require_once APPROOT . '/helpers/functions.php'; // APPROOT diambil dari config.php


// --- Custom Error & Exception Handling (Placeholder - sama seperti sebelumnya) ---
/* ... (Kode error handler Whoops atau kustom Anda) ... */


// --- Inisialisasi Aplikasi ---
try {
    // Buat instance Request dan Response
    $request = new \Core\Request();
    $response = new \Core\Response();

    // Buat instance App dan jalankan
    $app = new \Core\App($request, $response);

    // Jalankan Middleware Global (jika ada) - Konsep Placeholder
    // $app->runMiddleware('before');

    $app->run();

    // Jalankan Middleware Global (jika ada) - Konsep Placeholder
    // $app->runMiddleware('after', $response);

} catch (\Throwable $e) {
     error_log("Application Initialization Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
     if (ENVIRONMENT === 'development') {
          echo "<h1>Application Error</h1><pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
     } else {
          if (!headers_sent()) {
              header('HTTP/1.1 500 Internal Server Error.', TRUE, 500);
          }
           echo 'Terjadi kesalahan pada server.';
     }
     exit(1);
}