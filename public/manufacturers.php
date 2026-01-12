<?php
require_once 'bootstrap.php';

// Отправляем Last-Modified заголовок
sendLastModified();

$pageTitle = "Производители";
$pageDescription = "Каталог ведущих производителей автомобилей мира. Информация о брендах, истории компаний и их модельном ряде.";

$manufacturerModel = new ManufacturerModel();
$manufacturers = $manufacturerModel->getAll();

require_once 'templates/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <a href="/" title="Главная страница">Главная</a>
    <span class="breadcrumbs-separator">/</span>
    <span class="breadcrumbs-current">Производители</span>
</div>

<!-- Schema.org для Breadcrumbs -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Главная",
      "item": "<?= 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] ?>/"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Производители"
    }
  ]
}
</script>

<div class="section">
    <div class="container py-8">
        <h2 class="text-2xl font-bold mb-6">Список производителей (<?= count($manufacturers) ?>)</h2>
    </div>
    <div class="container">
        <ul class="brand-list">
            <?php foreach ($manufacturers as $manufacturer): ?>
                <li>
                    <a href="cars.php?manufacturer=<?= Utils::escape($manufacturer['slug']) ?>" 
                       title="Смотреть автомобили <?= Utils::escape($manufacturer['name']) ?>">
                        <?php if (!empty($manufacturer['logo_url'])): ?>
                            <img src="<?= Utils::escape($manufacturer['logo_url']) ?>" 
                                 alt="Логотип <?= Utils::escape($manufacturer['name']) ?>" 
                                 class="brand"
                                 loading="lazy">
                            <div class="brand-placeholder" style="display: none;">
                                <?= Utils::escape(substr($manufacturer['name'], 0, 2)) ?>
                            </div>
                        <?php else: ?>
                            <div class="brand-placeholder">
                                <?= Utils::escape(substr($manufacturer['name'], 0, 2)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="brand-text">
                            <span class="brand-name"><?= Utils::escape($manufacturer['name']) ?></span>
                            <?php if (isset($manufacturer['car_count']) && $manufacturer['car_count'] > 0): ?>
                                <small class="brand-count"><?= $manufacturer['car_count'] ?> моделей</small>
                            <?php endif; ?>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Schema.org микроразметка для списка производителей -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Производители автомобилей",
  "itemListElement": [
    <?php 
    $itemPosition = 1;
    foreach ($manufacturers as $manufacturer): 
    ?>
    <?= $itemPosition > 1 ? ',' : '' ?>
    {
      "@type": "ListItem",
      "position": <?= $itemPosition ?>,
      "item": {
        "@type": "Brand",
        "name": "<?= Utils::escape($manufacturer['name']) ?>",
        "url": "<?= 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] ?>/cars.php?manufacturer=<?= Utils::escape($manufacturer['slug']) ?>"
      }
    }
    <?php 
    $itemPosition++;
    endforeach; 
    ?>
  ]
}
</script>

<?php require_once 'templates/footer.php'; ?>