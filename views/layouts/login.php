<?php
use yii\helpers\Html;

$companyLogo = Yii::$app->params['companyLogo'] ?? '/uploads/logo.png';

$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css');
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title ?: 'Login') ?></title>

    <?php $this->head() ?>

    <style>
        body {
            background: linear-gradient(135deg, #f4f6f9 0%, #e9eef5 100%);
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
        }

        .login-area {
            width: 100%;
            max-width: 430px;
        }

        .brand-card {
            background: #fff;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            border-radius: 16px 16px 0 0;
            box-shadow: 0 6px 20px rgba(0,0,0,0.05);
            border-bottom: 1px solid #edf2f7;
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 8px;
        }

        .brand-title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #1f2d3d;
        }

        .brand-subtitle {
            margin: 2px 0 0;
            font-size: 13px;
            color: #6c757d;
        }

        .login-card {
            background: #fff;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        @media (max-width: 576px) {
            .login-wrapper {
                padding: 25px 12px;
            }
        }
    </style>
</head>

<body>
<?php $this->beginBody() ?>

<div class="login-wrapper">
    <div class="login-area">

        <!-- BRAND CARD -->
        <div class="brand-card d-flex align-items-center">
            <div style="flex:0 0 auto;">
                <img src="<?= Html::encode($companyLogo) ?>" alt="<?= Html::encode(Yii::$app->name) ?> logo" class="brand-logo" onerror="this.style.display='none'">
            </div>
            <div style="margin-left:15px;">
                <h1 class="brand-title"><?= Html::encode(Yii::$app->name) ?></h1>
                <div class="brand-subtitle">Enterprise Access Portal</div>
            </div>
        </div>

        <!-- LOGIN FORM -->
        <div class="login-card">
            <?= $content ?>
        </div>

    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
