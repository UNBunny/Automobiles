<?php
require_once 'config.php';
require_once 'templates/header.php';

$pageTitle = "Производители";
?>

<div class="section">
    <div class="container py-8">
        <h2 class="text-2xl font-bold mb-6">Список производителей</h2>
    </div>
    <div class="container">
        <ul class="brand-list">
            <?php
            $stmt = $pdo->query("SELECT * FROM manufacturers ORDER BY name");
            while ($manufacturer = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<li>
                    <a href="cars.php?manufacturer='.htmlspecialchars($manufacturer['slug']).'">
                        <img src="'.htmlspecialchars($manufacturer['logo_url']).'" 
                             alt="'.htmlspecialchars($manufacturer['name']).'" 
                             class="brand">
                        <span>'.htmlspecialchars($manufacturer['name']).'</span>
                    </a>
                </li>';
            }
            ?>
        </ul>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>