const categoryWrapper = document.getElementById('category-wrapper');
const leftButton = document.querySelector('.scroll-button.left');

function checkScroll() {
    if (categoryWrapper.scrollLeft > 0) {
        leftButton.style.display = 'flex'; // Показываем левую стрелочку
    } else {
        leftButton.style.display = 'none'; // Скрываем левую стрелочку
    }
}

categoryWrapper.addEventListener('scroll', checkScroll);

function scrollCategories(direction) {
    const scrollAmount = 100; // Уменьшаем шаг прокрутки
    const container = document.getElementById('category-wrapper');

    // Вычисляем новое положение прокрутки
    const newScrollLeft = container.scrollLeft + direction * scrollAmount;

    // Ограничиваем прокрутку, чтобы не выйти за пределы
    if (newScrollLeft < 0) {
        container.scrollTo({ left: 0, behavior: 'smooth' });
    } else if (newScrollLeft > container.scrollWidth - container.clientWidth) {
        container.scrollTo({ left: container.scrollWidth - container.clientWidth, behavior: 'smooth' });
    } else {
        container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
    }

    // Проверяем положение прокрутки после завершения анимации
    setTimeout(checkScroll, 300);
}
// Инициализация при загрузке страницы
checkScroll();

// Функция для прокрутки категорий
function scrollCategories(direction) {
    const container = document.getElementById('category-wrapper');
    const scrollAmount = 500; // Шаг прокрутки
    container.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}

// Функция для выбора категории
document.querySelectorAll('.category-button').forEach(button => {
    button.addEventListener('click', () => {
        // Убираем активный класс у всех кнопок
        document.querySelectorAll('.category-button').forEach(btn => {
            btn.classList.remove('active');
        });
        // Добавляем активный класс к нажатой кнопке
        button.classList.add('active');
    });
});

// script.js
document.addEventListener('DOMContentLoaded', function () {
    const burgerMenu = document.getElementById('burger-menu');
    const nav = document.getElementById('nav');

    burgerMenu.addEventListener('click', function () {
        nav.classList.toggle('active');
    });
});