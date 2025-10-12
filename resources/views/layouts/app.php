<?php
/** @var string $title */
/** @var string $description */
/** @var string $bodyClass */
/** @var string $content */
/** @var array $meta */
$app = lm_config();
$title = $title ?? $app['name'];
$description = $description ?? $app['tagline'];
$bodyClass = $bodyClass ?? '';
$meta = $meta ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>">
    <?php foreach ($meta as $name => $content): ?>
        <meta name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" content="<?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endforeach; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= lm_asset('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?= lm_asset('assets/css/custom.css'); ?>">
    <link rel="stylesheet" href="<?= lm_asset('assets/css/site.css'); ?>">
</head>
<body class="<?= htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8'); ?>">
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<main class="lm-main" id="main-content">
    <?= $content ?? ''; ?>
</main>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="<?= lm_asset('assets/js/site.js'); ?>"></script>
</body>
</html>
