<?php

declare(strict_types=1);

namespace App\Web\Shared\Layout\Branding;

use Yiisoft\Assets\AssetBundle;

/**
 * Centralizes deployment-independent URLs for HECATE institutional branding.
 *
 * Branding files are served directly from public/branding. The Yii asset manager
 * resolves @baseUrl, so callers must request assets through AssetManager::getUrl()
 * instead of hardcoding host names, subdirectories, document roots or OS paths.
 */
final class BrandingAsset extends AssetBundle
{
    public ?string $basePath = '@public/branding';
    public ?string $baseUrl = '@baseUrl/branding';
}
