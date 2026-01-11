const categoryWrapper = document.getElementById('category-wrapper');
const leftButton = document.querySelector('.scroll-button.left');

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
    const container = document.getElementById('category-wrapper');


    const newScrollLeft = container.scrollLeft + direction * scrollAmount;

    if (newScrollLeft < 0) {
        container.scrollTo({ left: 0, behavior: 'smooth' });
    } else if (newScrollLeft > container.scrollWidth - container.clientWidth) {
        container.scrollTo({ left: container.scrollWidth - container.clientWidth, behavior: 'smooth' });
    } else {
        container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
    }

    setTimeout(checkScroll, 300);
}
checkScroll();

// Функция для прокрутки категорий
function scrollCategories(direction) {
    const container = document.getElementById('category-wrapper');
    const scrollAmount = 500; 
    container.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}

// Функция для выбора категории
document.querySelectorAll('.category-button').forEach(button => {
    button.addEventListener('click', () => {
    
        document.querySelectorAll('.category-button').forEach(btn => {
            btn.classList.remove('active');
        });
        //
        button.classList.add('active');
    });
});

// Бургер меню
document.addEventListener('DOMContentLoaded', function () {
    const burgerMenu = document.getElementById('burger-menu');
    const nav = document.getElementById('nav');

    if (burgerMenu && nav) {
        burgerMenu.addEventListener('click', function () {
            console.log('Бургер-меню нажато'); 
            nav.classList.toggle('active');
        });
    } else {
        console.error('Элементы бургер-меню или навигации не найдены!');
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
    
    faqQuestions.forEach(question => {
        question.addEventListener('click', function() {
            const answer = question.nextElementSibling;
            const isOpen = answer.style.maxHeight;
            
            // Закрываем все остальные
            document.querySelectorAll('.faq-answer').forEach(ans => {
                ans.style.maxHeight = null;
            });
            
            // Открываем/закрываем текущий
            if (!isOpen) {
                answer.style.maxHeight = answer.scrollHeight + 'px';
            }
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