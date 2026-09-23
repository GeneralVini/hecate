<?php

declare(strict_types=1);

namespace App\Web\Shared\Layout\Main;

use App\Web\Shared\Layout\Branding\BrandingAsset;
use Yiisoft\Assets\AssetBundle;

final class MainAsset extends AssetBundle
{
    public ?string $basePath = '@assets/main';
    public ?string $baseUrl = '@assetsUrl/main';
    public ?string $sourcePath = '@assetsSource/main';

    /** @var list<class-string<AssetBundle>> */
    public array $depends = [BrandingAsset::class];

    /** @var list<string> */
    public array $css = [
        'site.css',
        'sidebar.css',
        'forms-compact.css',
        'data-grid.css',
        'grid-enhancements.css',
        'login.css',
    ];

    /** @var list<string> */
    public array $js = ['site.js', 'crud-modal.js'];
}
