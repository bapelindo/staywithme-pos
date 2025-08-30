<?php
// File: app/Controllers/Admin/SettingsController.php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\AuthHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper;
use App\Helpers\SanitizeHelper;
use App\Models\Settings;

class SettingsController extends Controller
{
    private $settingsModel;

    public function __construct()
    {
        $this->settingsModel = $this->model('Settings');
        AuthHelper::requireAdmin(); // Hanya admin yang boleh akses
    }

    /**
     * Menampilkan halaman pengaturan.
     */
    public function index()
    {
        $settings = $this->settingsModel->getAllSettings();
        
        // Atur nilai default jika belum ada di database
        $defaults = [
            'cogs_percentage' => '40',
            'tax_percentage' => '11',
            'service_charge_percentage' => '5',
            'default_promo_percentage' => '0',
            'default_admin_fee' => '0',
            'default_mdr_fee_percentage' => '0',
            'default_commission_percentage' => '0',
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }

        $this->view('admin.settings.index', [
            'pageTitle' => 'Pengaturan Laporan',
            'settings' => $settings
        ], 'admin_layout');
    }

    /**
     * Menyimpan perubahan pengaturan.
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            UrlHelper::redirect('/admin/settings');
            return;
        }

        $settingsToUpdate = [
            'cogs_percentage' => SanitizeHelper::float($_POST['cogs_percentage'] ?? 0),
            'tax_percentage' => SanitizeHelper::float($_POST['tax_percentage'] ?? 0),
            'service_charge_percentage' => SanitizeHelper::float($_POST['service_charge_percentage'] ?? 0),
            'default_promo_percentage' => SanitizeHelper::float($_POST['default_promo_percentage'] ?? 0),
            'default_admin_fee' => SanitizeHelper::float($_POST['default_admin_fee'] ?? 0),
            'default_mdr_fee_percentage' => SanitizeHelper::float($_POST['default_mdr_fee_percentage'] ?? 0),
            'default_commission_percentage' => SanitizeHelper::float($_POST['default_commission_percentage'] ?? 0),
        ];

        $success = $this->settingsModel->updateSettings($settingsToUpdate);

        if ($success) {
            SessionHelper::setFlash('success', 'Pengaturan berhasil diperbarui.');
        } else {
            SessionHelper::setFlash('error', 'Gagal memperbarui pengaturan.');
        }

        UrlHelper::redirect('/admin/settings');
    }
}