<?php

declare(strict_types=1);

use App\Web\Shared\Layout\Main\MainAsset;
use Yiisoft\Html\Html;

/**
 * @var App\Shared\ApplicationParams $applicationParams
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $content
 * @var Yiisoft\View\WebView $this
 */

$assetManager->register(MainAsset::class);
$this->addCssFiles($assetManager->getCssFiles());
$this->addCssStrings($assetManager->getCssStrings());
$this->addJsFiles($assetManager->getJsFiles());
$this->addJsStrings($assetManager->getJsStrings());
$this->addJsVars($assetManager->getJsVars());

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Html::encode($applicationParams->locale) ?>">
<head>
    <meta charset="<?= Html::encode($applicationParams->charset) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="/branding/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/branding/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="48x48" href="/branding/favicon-48x48.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/branding/favicon-180x180.png">
    <title><?= Html::encode($this->getTitle()) ?></title>
    <?php $this->head() ?>
</head>
<body class="login-page">
<?php $this->beginBody() ?>
<main class="login-shell" data-hecate-discovery="idle">
    <div class="login-art" aria-hidden="true"></div>

    <div class="hecate-discovery" aria-label="Descobertas HECATE">
        <button class="hecate-spark hecate-spark--identity" type="button" data-hecate-hotspot="identity" aria-label="Descobrir a origem do nome HECATE">✦</button>
        <button class="hecate-spark hecate-spark--purpose" type="button" data-hecate-hotspot="purpose" aria-label="Descobrir a função da plataforma">✦</button>
        <div class="hecate-torch-flame" aria-hidden="true"><span></span><i></i><b></b></div>
    </div>

    <section id="hecate-discovery-panel" class="hecate-discovery-panel" data-hecate-panel aria-live="polite">
        <button class="hecate-discovery-panel__close" type="button" data-hecate-close aria-label="Fechar">×</button>
        <div class="hecate-discovery-panel__content" data-hecate-content="identity">
            <p class="hecate-discovery-panel__eyebrow">A guardiã dos limiares</p>
            <h2>Quem é HECATE?</h2>
            <p>Hécate representa limiar, decisão, vigilância e passagem controlada. O nome foi escolhido por traduzir a ideia de governar quem acessa, quem autoriza e o que precisa permanecer rastreável.</p>
        </div>
        <div class="hecate-discovery-panel__content" data-hecate-content="purpose">
            <p class="hecate-discovery-panel__eyebrow">Identidade · Controle · Dados</p>
            <h2>Qual a função da plataforma?</h2>
            <p>Centralizar e governar o fluxo de impressão das OM, com autenticação, controle, cotas, auditoria e rastreabilidade, integrando identidade, controle e dados.</p>
            <div class="hecate-discovery-panel__flow" aria-hidden="true"><span>Identidade</span><b>→</b><span>Controle</span><b>→</b><span>Dados</span></div>
        </div>
    </section>

    <div class="login-form-area">
        <?= $content ?>
    </div>
    <?php require dirname(__DIR__, 2) . '/Partial/institutional-footer.php'; ?>
</main>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
