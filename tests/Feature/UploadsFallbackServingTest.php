<?php

namespace Tests\Feature;

use App\Support\Uploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadsFallbackServingTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploads_url_uses_the_disks_native_url_by_default(): void
    {
        config(['filesystems.uploads_disk' => 'public']);

        $this->assertSame('/storage/products/foo.jpg', Uploads::url('products/foo.jpg'));
    }

    public function test_uploads_url_routes_through_the_app_on_public_direct(): void
    {
        config(['filesystems.uploads_disk' => 'public_direct']);

        $this->assertSame(route('uploads.show', ['path' => 'products/foo.jpg']), Uploads::url('products/foo.jpg'));
    }

    public function test_uploads_url_returns_null_for_no_path(): void
    {
        $this->assertNull(Uploads::url(null));
    }

    public function test_the_fallback_route_streams_an_existing_file(): void
    {
        config(['filesystems.uploads_disk' => 'public_direct']);
        Storage::fake('public_direct');
        Storage::disk('public_direct')->put('products/foo.jpg', 'fake-image-bytes');

        $response = $this->get(route('uploads.show', ['path' => 'products/foo.jpg']));

        $response->assertOk();
        $this->assertSame('fake-image-bytes', $response->streamedContent());
    }

    public function test_the_fallback_route_404s_for_a_missing_file(): void
    {
        config(['filesystems.uploads_disk' => 'public_direct']);
        Storage::fake('public_direct');

        $this->get(route('uploads.show', ['path' => 'products/missing.jpg']))->assertNotFound();
    }
}
