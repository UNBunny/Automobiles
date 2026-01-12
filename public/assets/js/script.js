// Проверка и инициализация прокрутки категорий
const categoryWrapper = document.getElementById('category-wrapper');
const leftButton = document.querySelector('.scroll-button.left');

if (categoryWrapper && leftButton) {
    function checkScroll() {
        if (categoryWrapper.scrollLeft > 0) {
            leftButton.style.display = 'flex';
        } else {
            leftButton.style.display = 'none'; 
        }
    }

    categoryWrapper.addEventListener('scroll', checkScroll);

    function scrollCategories(direction) {
        const scrollAmount = 100; 
        const newScrollLeft = categoryWrapper.scrollLeft + direction * scrollAmount;

        if (newScrollLeft < 0) {
            categoryWrapper.scrollTo({ left: 0, behavior: 'smooth' });
        } else if (newScrollLeft > categoryWrapper.scrollWidth - categoryWrapper.clientWidth) {
            categoryWrapper.scrollTo({ left: categoryWrapper.scrollWidth - categoryWrapper.clientWidth, behavior: 'smooth' });
        } else {
            categoryWrapper.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
        }

        setTimeout(checkScroll, 300);
    }
    
    checkScroll();
    
    // Делаем функцию глобальной
    window.scrollCategories = scrollCategories;
}

// Функция для выбора категории
const categoryButtons = document.querySelectorAll('.category-button');
if (categoryButtons.length > 0) {
    categoryButtons.forEach(button => {
        button.addEventListener('click', () => {
            categoryButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');
        });
    });
}

// Бургер меню для обычных страниц
document.addEventListener('DOMContentLoaded', function () {
    const burgerMenu = document.getElementById('burger-menu');
    const nav = document.getElementById('nav');

    if (burgerMenu && nav) {
        burgerMenu.addEventListener('click', function () {
            console.log('Бургер-меню нажато'); 
            nav.classList.toggle('active');
        });
        
        // Закрытие меню при клике вне его
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.burger-menu') && !event.target.closest('.nav')) {
                nav.classList.remove('active');
            }
        });
    } else {
        if (!burgerMenu) console.log('Элемент burger-menu не найден');
        if (!nav) console.log('Элемент nav не найден');
    }
    
    // Бургер меню для админ-панели
    const burgerMenuAdmin = document.getElementById('burger-menu-admin');
    const sidebar = document.querySelector('.sidebar');
    
    if (burgerMenuAdmin && sidebar) {
        burgerMenuAdmin.addEventListener('click', function () {
            console.log('Админ бургер-меню нажато');
            sidebar.classList.toggle('active');
        });
        
        // Закрытие меню при клике вне его
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.burger-menu-admin') && !event.target.closest('.sidebar')) {
                sidebar.classList.remove('active');
            }
        });
    }
});


function toggleFavorite(event) {
    event.stopPropagation(); 
    const button = event.target;
    button.classList.toggle("active"); 
    if (button.classList.contains("active")) {
        button.textContent = "❤️"; // Красное сердечко
    } else {
        button.textContent = "♡"; // Пустое сердечко
    }
}

const applyButton = document.querySelector('.apply-button');
if (applyButton) {
    applyButton.addEventListener('click', function(e) {
        const yearFromInput = document.getElementById('year-from');
        const yearToInput = document.getElementById('year-to');
        
        if (!yearFromInput || !yearToInput) return;
        
        const yearFrom = parseInt(yearFromInput.value);
        const yearTo = parseInt(yearToInput.value);
        
        // Очистка предыдущих ошибок
        yearFromInput.classList.remove('input-error');
        yearToInput.classList.remove('input-error');
        const existingErrors = document.querySelectorAll('.error-message');
        existingErrors.forEach(err => err.remove());
        
        // Валидация диапазона
        if (yearFrom && yearTo && yearFrom > yearTo) {
            e.preventDefault();
            yearToInput.classList.add('input-error');
            const errorMsg = document.createElement('div');
            errorMsg.className = 'error-message';
            errorMsg.textContent = 'Год "до" не может быть меньше года "от"';
            yearToInput.parentElement.appendChild(errorMsg);
            showToast('Проверьте диапазон годов', 'error');
            return false;
        }
    });
}

// Показ лоадера при навигации
function showLoader() {
    const loader = document.getElementById('page-loader');
    if (loader) loader.classList.add('active');
}

function hideLoader() {
    const loader = document.getElementById('page-loader');
    if (loader) loader.classList.remove('active');
}

// Показ лоадера при переходах
document.addEventListener('DOMContentLoaded', function() {
    // Скрываем лоадер при загрузке страницы
    hideLoader();
    
    // Показываем лоадер при клике на ссылки
    const links = document.querySelectorAll('a:not([target="_blank"])');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.href && !this.href.includes('#')) {
                showLoader();
            }
        });
    });
    
    // Показываем лоадер при отправке форм
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            showLoader();
        });
    });
});

// Toast уведомления
function showToast(message, type = 'success') {
    const existingToast = document.querySelector('.toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Активная страница в навигации
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav a');
    
    navLinks.forEach(link => {
        const linkPath = new URL(link.href).pathname;
        if (currentPath === linkPath || (currentPath === '/' && linkPath === '/')) {
            link.classList.add('active');
        }
    });
});

// FAQ аккордеон
document.addEventListener('DOMContentLoaded', function() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    console.log('FAQ вопросов найдено:', faqQuestions.length);
    
    faqQuestions.forEach((question, index) => {
        question.addEventListener('click', function(e) {
            e.preventDefault();
            const faqItem = this.closest('.faq-item');
            console.log('Клик на FAQ вопрос #' + index);
            
            // Переключаем класс active
            faqItem.classList.toggle('active');
            console.log('FAQ item active:', faqItem.classList.contains('active'));
        });
    });
});

// Переключение отображения характеристик
function toggleSpecs() {
    const table = document.getElementById('specs-table');
    const btn = document.getElementById('toggle-specs');
    
    if (table && btn) {
        table.classList.toggle('expanded');
        
        if (table.classList.contains('expanded')) {
            btn.textContent = 'Скрыть характеристики';
        } else {
            btn.textContent = 'Показать все характеристики';
        }
    }
}

// Удаление отдельного фильтра
function removeFilter(param) {
    const url = new URL(window.location);
    url.searchParams.delete(param);
    showLoader();
    window.location.href = url.toString();
}

// Очистка всех фильтров
function clearAllFilters() {
    const url = new URL(window.location);
    const basePath = url.pathname;
    showLoader();
    window.location.href = basePath;
}

// Обработка ошибок загрузки изображений - показываем заглушку
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img');
    const placeholderPath = '/assets/images/placeholder.svg';
    
    images.forEach(function(img) {
        // Пропускаем favicon и уже загруженные заглушки
        if (img.src.includes('favicon') || img.src.includes('placeholder.svg')) {
            return;
        }
        
        img.addEventListener('error', function() {
            // Проверяем, что еще не заменили на заглушку
            if (!this.src.includes('placeholder.svg')) {
                // Для логотипов брендов показываем заглушку с инициалами
                if (this.classList.contains('brand')) {
                    this.style.display = 'none';
                    const placeholder = this.nextElementSibling;
                    if (placeholder && placeholder.classList.contains('brand-placeholder')) {
                        placeholder.style.display = 'flex';
                    }
                } else {
                    // Для обычных изображений показываем SVG заглушку
                    this.src = placeholderPath;
                    this.alt = 'Изображение недоступно';
                }
            }
        });
    });
});