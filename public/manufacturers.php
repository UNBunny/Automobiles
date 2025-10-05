<?php
require_once 'bootstrap.php';

$pageTitle = "Производители";

$manufacturerModel = new ManufacturerModel();
$manufacturers = $manufacturerModel->getAll();

require_once 'templates/header.php';
?>

<div class="section">
    <div class="container py-8">
        <h2 class="text-2xl font-bold mb-6">Список производителей (<?= count($manufacturers) ?>)</h2>
    </div>
    <div class="container">
        <ul class="brand-list">
            <?php foreach ($manufacturers as $manufacturer): ?>
                <li>
                    <a href="cars.php?manufacturer=<?= Utils::escape($manufacturer['slug']) ?>">
                        <?php if (!empty($manufacturer['logo_url'])): ?>
                            <img src="<?= Utils::escape($manufacturer['logo_url']) ?>" 
                                 alt="<?= Utils::escape($manufacturer['name']) ?>" 
                                 class="brand"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
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

<?php require_once 'templates/footer.php'; ?>