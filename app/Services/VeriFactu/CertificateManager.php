<?php

namespace App\Services\VeriFactu;

use App\Models\Certificate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CertificateManager
{
    /**
     * Upload and store a .p12 certificate.
     * Extracts metadata and stores the file encrypted.
     */
    public function upload(UploadedFile $file, string $password, string $name): Certificate
    {
        $pfxContent = file_get_contents($file->getRealPath());

        // Verify the certificate can be read with the provided password
        $certInfo = $this->extractCertInfo($pfxContent, $password);

        // Store the .p12 file in private storage
        $storagePath = config('verifactu.cert_storage_path');
        $filename = 'cert_' . now()->format('YmdHis') . '_' . uniqid() . '.p12';
        $fullPath = $storagePath . '/' . $filename;

        Storage::put($fullPath, $pfxContent);

        return Certificate::create([
            'name' => $name,
            'pfx_path' => $fullPath,
            'pfx_password' => $password,
            'subject_cn' => $certInfo['subject_cn'],
            'serial_number' => $certInfo['serial_number'],
            'valid_from' => $certInfo['valid_from'],
            'valid_until' => $certInfo['valid_until'],
            'is_active' => true,
        ]);
    }

    /**
     * Extract PEM files (cert + key) from a stored certificate.
     * Returns array with paths to temp PEM files.
     */
    public function extractPem(Certificate $certificate): array
    {
        $pfxContent = Storage::get($certificate->pfx_path);
        $password = $certificate->pfx_password;

        if (! $pfxContent) {
            throw new RuntimeException('Certificate file not found: ' . $certificate->pfx_path);
        }

        $certs = [];
        $parsed = openssl_pkcs12_read($pfxContent, $certs, $password);

        if (! $parsed) {
            throw new RuntimeException('Failed to read PKCS#12 certificate: ' . openssl_error_string());
        }

        // Write temp PEM files
        $certPath = tempnam(sys_get_temp_dir(), 'cert_');
        $keyPath = tempnam(sys_get_temp_dir(), 'key_');

        file_put_contents($certPath, $certs['cert']);
        file_put_contents($keyPath, $certs['pkey']);

        return [
            'cert_path' => $certPath,
            'key_path' => $keyPath,
            'cert_pem' => $certs['cert'],
            'key_pem' => $certs['pkey'],
        ];
    }

    /**
     * Get the active certificate for the current tenant.
     */
    public function getActiveCertificate(): ?Certificate
    {
        return Certificate::active()
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Extract certificate information from PFX content.
     */
    private function extractCertInfo(string $pfxContent, string $password): array
    {
        $certs = [];
        $parsed = openssl_pkcs12_read($pfxContent, $certs, $password);

        if (! $parsed) {
            throw new RuntimeException('Invalid certificate or incorrect password: ' . openssl_error_string());
        }

        $certData = openssl_x509_parse($certs['cert']);

        if (! $certData) {
            throw new RuntimeException('Failed to parse certificate data.');
        }

        return [
            'subject_cn' => $certData['subject']['CN'] ?? $certData['subject']['O'] ?? 'Unknown',
            'serial_number' => $certData['serialNumberHex'] ?? $certData['serialNumber'] ?? null,
            'valid_from' => isset($certData['validFrom_time_t'])
                ? \Carbon\Carbon::createFromTimestamp($certData['validFrom_time_t'])
                : null,
            'valid_until' => isset($certData['validTo_time_t'])
                ? \Carbon\Carbon::createFromTimestamp($certData['validTo_time_t'])
                : null,
        ];
    }

    /**
     * Clean up temporary PEM files.
     */
    public function cleanupPem(array $pemPaths): void
    {
        foreach (['cert_path', 'key_path'] as $key) {
            if (isset($pemPaths[$key]) && file_exists($pemPaths[$key])) {
                unlink($pemPaths[$key]);
            }
        }
    }
}
