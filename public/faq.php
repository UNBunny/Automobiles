<?php
require_once 'bootstrap.php';

$pageTitle = "Часто задаваемые вопросы - FAQ";
require_once 'templates/header.php';
?>

<div class="breadcrumbs">
    <a href="/">Главная</a>
    <span class="breadcrumbs-separator">/</span>
    <span class="breadcrumbs-current">FAQ</span>
</div>

<div class="faq-container">
    <h1 class="text-2xl font-bold mb-6">Часто задаваемые вопросы</h1>
    
    <div class="faq-item">
        <button class="faq-question">
            Как найти нужный автомобиль?
            <span class="faq-icon">▼</span>
        </button>
        <div class="faq-answer">
            Вы можете использовать поиск в верхней части страницы, ввести название модели или производителя. Также доступны фильтры по годам выпуска и сортировка по различным параметрам на странице "Автомобили".
        </div>
    </div>

    <div class="faq-item">
        <button class="faq-question">
            Как работают фильтры по годам?
            <span class="faq-icon">▼</span>
        </button>
        <div class="faq-answer">
            На странице "Все автомобили" вы можете указать диапазон годов выпуска в фильтре. Укажите год "от" и год "до", затем нажмите кнопку "Применить". Система автоматически проверит корректность диапазона.
        </div>
    </div>

    <div class="faq-item">
        <button class="faq-question">
            Что означают характеристики электромобилей?
            <span class="faq-icon">▼</span>
        </button>
        <div class="faq-answer">
            <strong>Емкость батареи (кВт·ч)</strong> - количество энергии, которое может хранить батарея.<br>
            <strong>Запас хода (км)</strong> - расстояние, которое автомобиль может проехать на одной зарядке.<br>
            <strong>Мощность (л.с.)</strong> - мощность электродвигателя в лошадиных силах.<br>
            <strong>Разгон 0-100 км/ч</strong> - время разгона до 100 км/ч в секундах.
        </div>
    </div>

    <div class="faq-item">
        <button class="faq-question">
            Как сортировать результаты?
            <span class="faq-icon">▼</span>
        </button>
        <div class="faq-answer">
            На странице каталога автомобилей доступен выпадающий список "Сортировать по". Вы можете сортировать по году выпуска (новее/старше), цене (по возрастанию/убыванию) и популярности.
        </div>
    </div>

    <div class="faq-item">
        <button class="faq-question">
            Можно ли сравнить несколько автомобилей?
            <span class="faq-icon">▼</span>
        </button>
        <div class="faq-answer">
            В текущей версии сайта функция сравнения автомобилей находится в разработке. Вы можете открыть несколько вкладок браузера с детальными страницами разных автомобилей для ручного сравнения характеристик.
        </div>
    </div>

    <div class="faq-item">
        <button class="faq-question">
            Как связаться с поддержкой?
            <span class="faq-icon">▼</span>
        </button>
        <div class="faq-answer">
            Вы можете связаться с нами по электронной почте: <strong>support@automobiles.com</strong><br>
            Или позвонить по телефону: <strong>+7 (800) 123-45-67</strong><br>
            Время работы: Пн-Пт с 9:00 до 18:00 (UTC+6)
        </div>
    </div>

    <div class="faq-item">
        <button class="faq-question">
            Откуда берутся данные об автомобилях?
            <span class="faq-icon">▼</span>
        </button>
        <div class="faq-answer">
            Все данные об автомобилях получены из официальных источников производителей и проверенных автомобильных изданий. Мы регулярно обновляем информацию для обеспечения её актуальности.
        </div>
    </div>

    <div class="faq-item">
        <button class="faq-question">
            Что делать, если я нашел ошибку на сайте?
            <span class="faq-icon">▼</span>
        </button>
        <div class="faq-answer">
            Если вы обнаружили ошибку или неточность в информации, пожалуйста, свяжитесь с нами через форму обратной связи или напишите на <strong>support@automobiles.com</strong>. Мы ценим ваш вклад в улучшение качества информации!
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
