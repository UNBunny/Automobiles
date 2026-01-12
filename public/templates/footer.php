</div>

<!-- Footer -->
<footer class="footer">
    <div class="footer-line"></div>
    <div class="container py-6">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            <!-- О проекте -->
            <div>
                <h3 style="font-weight: 600; margin-bottom: 1rem; color: #374151;">Automobiles</h3>
                <p style="color: #6b7280; font-size: 0.875rem; line-height: 1.6;">
                    Каталог современных автомобилей с подробными характеристиками и описаниями.
                    Помогаем выбрать идеальный автомобиль для вас.
                </p>
            </div>
            
            <!-- Навигация -->
            <nav aria-label="Навигация по сайту">
                <h3 style="font-weight: 600; margin-bottom: 1rem; color: #374151;">Разделы</h3>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 0.5rem;"><a href="/" class="text-gray-700 hover:text-blue-500" title="Главная страница каталога">Главная</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="/cars.php" class="text-gray-700 hover:text-blue-500" title="Полный каталог автомобилей">Все автомобили</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="/manufacturers.php" class="text-gray-700 hover:text-blue-500" title="Список производителей автомобилей">Производители</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="/faq.php" class="text-gray-700 hover:text-blue-500" title="Часто задаваемые вопросы">FAQ</a></li>
                </ul>
            </nav>
            
            <!-- Контакты -->
            <div>
                <h3 style="font-weight: 600; margin-bottom: 1rem; color: #374151;">Контакты</h3>
                <ul style="list-style: none; padding: 0; margin: 0; color: #6b7280; font-size: 0.875rem;">
                    <li style="margin-bottom: 0.5rem;">
                        📧 <a href="mailto:support@automobiles.com" class="text-gray-700 hover:text-blue-500">support@automobiles.com</a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        📞 <a href="tel:+78001234567" class="text-gray-700 hover:text-blue-500">+7 (800) 123-45-67</a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        🕒 Пн-Пт: 9:00 - 18:00 (UTC+6)
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="text-center" style="border-top: 1px solid #e5e7eb; padding-top: 1.5rem;">
            <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">
                &copy; <?php echo date('Y'); ?> Kuharchuk. Все права защищены.
            </p>
            <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; font-size: 0.875rem;">
                <a href="/faq.php" class="text-gray-700 hover:text-blue-500" title="Часто задаваемые вопросы и ответы">Справка</a>
                <span style="color: #d1d5db;">|</span>
                <a href="/privacy.php" class="text-gray-700 hover:text-blue-500" title="Политика обработки персональных данных">Политика конфиденциальности</a>
                <span style="color: #d1d5db;">|</span>
                <a href="/terms.php" class="text-gray-700 hover:text-blue-500" title="Правила использования сайта">Условия использования</a>
            </div>
        </div>
    </div>
</footer>
<script src="<?php echo ASSETS_PATH; ?>/js/script.js"></script>
<script>
// Устанавливаем дату для версии печати
document.body.setAttribute('data-print-date', new Date().toLocaleDateString('ru-RU'));
</script>
</body>
</html>