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

document.querySelector('.apply-button').addEventListener('click', function() {
    const yearFrom = document.querySelector('.year-input:first-of-type').value;
    const yearTo = document.querySelector('.year-input:last-of-type').value;

    if (yearFrom && yearTo) {
        console.log(`Фильтр по годам: от ${yearFrom} до ${yearTo}`);
    
    } else {
        alert('Пожалуйста, заполните оба поля.');
    }
});