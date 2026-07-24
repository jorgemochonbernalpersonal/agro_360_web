<?php

namespace Tests\Feature\Winery\Verifactu;

use App\Models\User;
use App\Services\VerifactuCertificateService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\WineryTestCase;

/**
 * Tests del certificado VeriFactu POR USUARIO (no de plataforma). Cada bodega
 * sube y firma con su propio .p12 — ver VerifactuCertificateService.
 */
class VerifactuCertificateServiceTest extends WineryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_store_saves_encrypted_cert_and_extracts_expiry(): void
    {
        $winery = $this->makeWineryWithNif('12345678Z');
        $file = $this->makeP12Upload('12345678Z');

        $this->service()->store($winery, $file, 'secret123');

        $winery->refresh();
        $this->assertNotEmpty($winery->sif_cert_path);
        $this->assertNotEmpty($winery->sif_cert_password);
        $this->assertNotNull($winery->sif_cert_uploaded_at);
        $this->assertNotNull($winery->sif_cert_expires_at);
        $this->assertTrue($winery->sif_cert_expires_at->isFuture());

        // El fichero en disco está cifrado: no debe contener el PEM en claro.
        $stored = Storage::disk('local')->get($winery->sif_cert_path);
        $this->assertStringNotContainsString('-----BEGIN', $stored);

        $this->assertTrue($this->service()->hasCertificate($winery));
    }

    public function test_store_rejects_wrong_password(): void
    {
        $winery = $this->makeWineryWithNif('12345678Z');
        $file = $this->makeP12Upload('12345678Z', 'secret123');

        $this->expectException(\RuntimeException::class);

        $this->service()->store($winery, $file, 'wrong-password');
    }

    public function test_store_rejects_certificate_of_a_different_nif(): void
    {
        $winery = $this->makeWineryWithNif('12345678Z');
        $file = $this->makeP12Upload('99999999R'); // NIF distinto al del usuario

        $this->expectException(\RuntimeException::class);

        $this->service()->store($winery, $file, 'secret123');
    }

    public function test_delete_removes_file_and_clears_columns(): void
    {
        $winery = $this->makeWineryWithNif('12345678Z');
        $file = $this->makeP12Upload('12345678Z');
        $this->service()->store($winery, $file, 'secret123');
        $winery->refresh();
        $path = $winery->sif_cert_path;

        $this->service()->delete($winery);

        $winery->refresh();
        $this->assertNull($winery->sif_cert_path);
        $this->assertNull($winery->sif_cert_password);
        $this->assertNull($winery->sif_cert_expires_at);
        $this->assertFalse(Storage::disk('local')->exists($path));
        $this->assertFalse($this->service()->hasCertificate($winery));
    }

    public function test_uploading_a_new_cert_for_same_user_replaces_the_previous_one(): void
    {
        $winery = $this->makeWineryWithNif('12345678Z');
        $this->service()->store($winery, $this->makeP12Upload('12345678Z'), 'secret123');
        $winery->refresh();
        $firstPath = $winery->sif_cert_path;

        $this->service()->store($winery, $this->makeP12Upload('12345678Z', 'secret456'), 'secret456');
        $winery->refresh();

        $this->assertSame($firstPath, $winery->sif_cert_path);
        $this->assertTrue($this->service()->hasCertificate($winery));
    }

    private function service(): VerifactuCertificateService
    {
        return app(VerifactuCertificateService::class);
    }

    private function makeWineryWithNif(string $dni): User
    {
        return User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => now(),
            'dni' => $dni,
        ]);
    }

    /**
     * Genera un .p12 autofirmado de prueba con el NIF dado incrustado en el
     * Subject (commonName), para poder probar tanto el caso feliz como el
     * rechazo por NIF distinto.
     */
    private function makeP12Upload(string $dni, string $password = 'secret123'): UploadedFile
    {
        // openssl_csr_new()/openssl_csr_sign() necesitan un openssl.cnf válido.
        // En Windows, la ruta por defecto de PHP a menudo no existe; se busca
        // una alternativa (p. ej. la que trae Git for Windows) solo para tests.
        $opensslConfig = [];
        foreach ([
            'C:\\Program Files\\Common Files\\SSL\\openssl.cnf',
            'C:\\Program Files\\Git\\mingw64\\etc\\ssl\\openssl.cnf',
            'C:\\Program Files\\Git\\usr\\ssl\\openssl.cnf',
            '/etc/ssl/openssl.cnf',
        ] as $candidate) {
            if (file_exists($candidate)) {
                $opensslConfig = ['config' => $candidate];
                break;
            }
        }

        $pkey = openssl_pkey_new($opensslConfig + ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $csr = openssl_csr_new(['commonName' => "TEST {$dni}", 'countryName' => 'ES'], $pkey, $opensslConfig);
        $cert = openssl_csr_sign($csr, null, $pkey, 365, $opensslConfig);

        openssl_x509_export($cert, $certPem);
        openssl_pkcs12_export($cert, $p12, $pkey, $password);

        $path = tempnam(sys_get_temp_dir(), 'verifactu_test_').'.p12';
        file_put_contents($path, $p12);

        return new UploadedFile($path, 'certificado.p12', 'application/x-pkcs12', null, true);
    }
}
