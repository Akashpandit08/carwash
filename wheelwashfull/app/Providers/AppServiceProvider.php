<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Http\UploadedFile;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        UploadedFile::macro('storeOnCloudinary', function ($folder = null) {
            $response = app(\Cloudinary\Cloudinary::class)->uploadApi()->upload($this->getRealPath(), [
                'folder' => $folder,
            ]);

            return new class($response['secure_url']) {
                private $secureUrl;
                
                public function __construct($secureUrl) {
                    $this->secureUrl = $secureUrl;
                }
                
                public function getSecurePath() {
                    return $this->secureUrl;
                }
            };
        });
    }
}
