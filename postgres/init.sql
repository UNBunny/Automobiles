-- Таблица пользователей для админ-панели
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    full_name VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    yandex_id VARCHAR(100) UNIQUE,
    oauth_provider VARCHAR(20)
);

CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE
);

-- Таблица типов кузова
CREATE TABLE body_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE
);

-- Таблица типов двигателей
CREATE TABLE engine_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE
);

-- Таблица производителей (брендов)
CREATE TABLE manufacturers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    logo_url VARCHAR(255),
    description TEXT,
    founded_year INT,
    country VARCHAR(50),
    slug VARCHAR(100) NOT NULL UNIQUE
);

-- Таблица автомобилей
CREATE TABLE cars (
    id SERIAL PRIMARY KEY,
    manufacturer_id INTEGER REFERENCES manufacturers(id),
    model VARCHAR(100) NOT NULL,
    year INTEGER NOT NULL,
    body_type_id INTEGER REFERENCES body_types(id),
    engine_type_id INTEGER REFERENCES engine_types(id),
    power_hp INTEGER,
    battery_capacity_kwh DECIMAL(5,1),
    range_km INTEGER,
    acceleration_0_100 DECIMAL(4,1),
    top_speed_kmh INTEGER,
    price DECIMAL(10, 2),
    main_image_url VARCHAR(255) NOT NULL, -- Добавлено поле для основного изображения
    description TEXT,
    views INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    slug VARCHAR(150) NOT NULL UNIQUE
);

-- Таблица дополнительных изображений автомобилей
CREATE TABLE car_images (
    id SERIAL PRIMARY KEY,
    car_id INTEGER REFERENCES cars(id),
    image_url VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    alt_text VARCHAR(100)
);

-- Таблица характеристик автомобилей
CREATE TABLE car_features (
    id SERIAL PRIMARY KEY,
    car_id INTEGER REFERENCES cars(id),
    feature_name VARCHAR(100) NOT NULL,
    feature_value VARCHAR(255) NOT NULL
);

-- Связь многие-ко-многим между автомобилями и категориями
CREATE TABLE car_categories (
    car_id INTEGER REFERENCES cars(id),
    category_id INTEGER REFERENCES categories(id),
    PRIMARY KEY (car_id, category_id)
);

-- Создаем администратора по умолчанию (admin/admin123)
-- Пароль хешируется с помощью password_hash() в PHP
INSERT INTO users (username, password_hash, email, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'Administrator');

-- Заполняем категории (как в HTML)
INSERT INTO categories (name, slug) VALUES
('Electric', 'electric'),
('SUV', 'suv'),
('Sedan', 'sedan'),
('Pickup Truck', 'pickup-truck'),
('Luxury', 'luxury'),
('Crossover', 'crossover'),
('Hybrid', 'hybrid'),
('Diesel', 'diesel'),
('Coupe', 'coupe'),
('Hatchback', 'hatchback'),
('Wagon', 'wagon'),
('Convertible', 'convertible');

-- Заполняем типы кузова
INSERT INTO body_types (name, slug) VALUES
('Седан', 'sedan'),
('Хэтчбек', 'hatchback'),
('Универсал', 'wagon'),
('Купе', 'coupe'),
('Кабриолет', 'convertible'),
('Внедорожник', 'suv'),
('Кроссовер', 'crossover'),
('Пикап', 'pickup'),
('Минивэн', 'minivan'),
('Лифтбэк', 'liftback');

-- Заполняем типы двигателей
INSERT INTO engine_types (name, slug) VALUES
('Бензин', 'petrol'),
('Дизель', 'diesel'),
('Гибрид', 'hybrid'),
('Электрический', 'electric'),
('Газ', 'lpg'),
('Газ/бензин', 'lpg-petrol'),
('Водород', 'hydrogen');

-- Заполняем производителей (как в HTML)
INSERT INTO manufacturers (name, logo_url, founded_year, country, slug) VALUES
('Tesla', 'https://c.rdrom.ru/js/bundles/media/tesla.e3a30993f0667ab60843.png', 2003, 'USA', 'tesla'),
('Audi', 'https://c.rdrom.ru/js/bundles/media/audi-light.f63a0b76e7c32516a588.png', 1909, 'Germany', 'audi'),
('BMW', 'https://c.rdrom.ru/js/bundles/media/aito-light.28f0e2bbf4a858c448e2.png', 1916, 'Germany', 'bmw'),
('Mercedes-Benz', 'https://c.rdrom.ru/js/bundles/media/mercedes-benz.161a2db1148a29b16bbe.png', 1926, 'Germany', 'mercedes'),
('Ford', 'https://c.rdrom.ru/js/bundles/media/ford.6ca6f3853b7c8486f718.png', 1903, 'USA', 'ford'),
('Volkswagen', 'https://c.rdrom.ru/js/bundles/media/volkswagen-light.683715306f4767406339.png', 1937, 'Germany', 'volkswagen'),
('Toyota', 'https://c.rdrom.ru/js/bundles/media/toyota-light.01487b2434ca52ac0a0d.png', 1937, 'Japan', 'toyota'),
('Nissan', 'https://c.rdrom.ru/js/bundles/media/nissan-light.83683a20c9a7a830499a.png', 1933, 'Japan', 'nissan'),
('Porsche', 'https://c.rdrom.ru/js/bundles/media/porsche.fcaf7e4b42ff916bac16.png', 1931, 'Germany', 'porsche'),
('Lexus', 'https://c.rdrom.ru/js/bundles/media/lexus.ca87eb225d1a2d10c11b.png', 1989, 'Japan', 'lexus'),
('Volvo', 'https://c.rdrom.ru/js/bundles/media/volvo-light.758300acd946598937be.png', 1927, 'Sweden', 'volvo'),
('Jaguar', 'https://c.rdrom.ru/js/bundles/media/jaguar.98934d52e2cca7dedd02.png', 1922, 'UK', 'jaguar'),
('Land Rover', 'https://c.rdrom.ru/js/bundles/media/land-rover.c84f0c01c46b14c3cdaa.png', 1948, 'UK', 'land-rover'),
('Jeep', 'https://c.rdrom.ru/js/bundles/media/jeep.9faf870a3f1808739947.png', 1941, 'USA', 'jeep'),
('Chevrolet', 'https://c.rdrom.ru/js/bundles/media/chevrolet.397c10d43b92108a382f.png', 1911, 'USA', 'chevrolet'),
('Kia', 'https://c.rdrom.ru/js/bundles/media/kia-light.18ad29dd4f2def4cdb5d.png', 1944, 'South Korea', 'kia'),
('Hyundai', 'https://c.rdrom.ru/js/bundles/media/hyundai-light.803d46b9f376408863d4.png', 1967, 'South Korea', 'hyundai'),
('Mazda', 'https://c.rdrom.ru/js/bundles/media/mazda.256e9aa8a3b50e86f74d.png', 1920, 'Japan', 'mazda'),
('Subaru', 'https://c.rdrom.ru/js/bundles/media/subaru.ff3e5721fb46b71c1aa9.png', 1953, 'Japan', 'subaru'),
('Honda', 'https://c.rdrom.ru/js/bundles/media/honda.08f97e6833009f51c8ef.png', 1948, 'Japan', 'honda');

-- Заполняем автомобили Tesla (пример)

-- Обновленные примеры автомобилей с основными изображениями
INSERT INTO cars (manufacturer_id, model, year, body_type_id, engine_type_id, power_hp, battery_capacity_kwh, range_km, acceleration_0_100, top_speed_kmh, price, main_image_url, description, slug) VALUES
-- Tesla
(1, 'Model S', 2023, 1, 4, 670, 100, 637, 3.1, 250, 94990.00, 'https://platform.cstatic-images.com/medium/in/v2/stock_photos/cd74e70d-68ee-44dc-85a8-3edcf3ffe348/acd2cd85-209c-45f2-9033-8c1033ea8745.png', 'Флагманский электрический седан Tesla', 'tesla-model-s-2023'),
(1, 'Model 3', 2023, 1, 4, 480, 82, 568, 3.3, 233, 42990.00, 'https://platform.cstatic-images.com/medium/in/v2/stock_photos/ecbc4cd8-5926-4f8e-b320-56d6e4e14665/ef196f60-908e-47e3-a94a-8ee9e06064eb.png', 'Компактный электрический седан Tesla', 'tesla-model-3-2023'),
(1, 'Model X', 2023, 6, 4, 670, 100, 560, 3.9, 250, 109990.00, 'https://platform.cstatic-images.com/medium/in/v2/stock_photos/77aeca88-d0af-4824-bbfd-d84ce2538524/1af0ca3f-5236-47a5-a487-32b49ec94f27.png', 'Электрический кроссовер Tesla с дверями "крылья сокола"', 'tesla-model-x-2023'),
(1, 'Model Y', 2023, 7, 4, 450, 75, 531, 4.8, 217, 54990.00, 'https://platform.cstatic-images.com/medium/in/v2/stock_photos/77aeca88-d0af-4824-bbfd-d84ce2538524/1af0ca3f-5236-47a5-a487-32b49ec94f27.png', 'Компактный электрический кроссовер Tesla', 'tesla-model-y-2023'),

-- Audi
(2, 'A6 e-tron', 2023, 1, 4, 469, 100, 600, 4.5, 240, 79900.00, 'https://platform.cstatic-images.com/medium/in/v2/stock_photos/bc5d4734-5bf0-4fda-acf3-265023f70310/a417d9ea-b2bf-40b1-80cc-24c0e84ea083.png', 'Электрическая версия легендарного бизнес-седана', 'audi-a6-etron-2023'),
(2, 'Q8 e-tron', 2023, 6, 4, 402, 95, 400, 5.6, 200, 87900.00, '/public/assets/images/audiq8.jfif', 'Флагманский электрический кроссовер Audi', 'audi-q8-etron-2023'),

-- BMW
(3, 'i3', 2023, 2, 4, 170, 42, 310, 7.3, 150, 44400.00, 'https://platform.cstatic-images.com/medium/in/v2/stock_photos/bc5d4734-5bf0-4fda-acf3-265023f70310/a417d9ea-b2bf-40b1-80cc-24c0e84ea083.png', 'Компактный городской электромобиль', 'bmw-i3-2023'),
(3, 'iX', 2023, 6, 4, 523, 111, 630, 4.6, 250, 84900.00, 'https://platform.cstatic-images.com/medium/in/v2/stock_photos/b89d2a6c-2900-4cb1-bf19-cb841e79def6/5d4eb686-487f-4322-88ca-d015d5bb0df8.png', 'Флагманский электрический кроссовер BMW', 'bmw-ix-2023'),

-- Nissan
(8, 'Leaf', 2023, 2, 4, 147, 40, 270, 7.9, 144, 31900.00, 'https://platform.cstatic-images.com/medium/in/v2/stock_photos/07827872-c533-4f5f-8931-3a911250aaf0/96548bd5-fb31-4570-b31a-517e2ebe5408.png', 'Самый продаваемый электромобиль в мире', 'nissan-leaf-2023'),

-- Ford
(5, 'Mustang Mach-E', 2023, 6, 4, 480, 88, 490, 3.8, 200, 62900.00, 'https://platform.cstatic-images.com/medium/in/v2/stock_photos/0b86a23a-a3e2-476d-b459-4fa8ac478fa1/72670da9-7623-457c-8be7-e7c0254487d9.png', 'Электрический Mustang', 'ford-mustang-mach-e-2023'),
(5, 'F-150 Lightning', 2023, 8, 4, 580, 131, 515, 4.5, 180, 69900.00, 'https://platform.cstatic-images.com/medium/in/v2/stock_photos/607e07e8-c829-4ec3-ba7a-20771f79bd64/e86ac423-47a9-4dd6-a83f-888431ed12aa.png', 'Электрический пикап', 'ford-f150-lightning-2023'),

-- Volkswagen
(6, 'ID.4', 2023, 6, 4, 295, 82, 450, 6.3, 180, 44900.00, 'https://platform.cstatic-images.com/medium/in/v2/stock_photos/b89d2a6c-2900-4cb1-bf19-cb841e79def6/5d4eb686-487f-4322-88ca-d015d5bb0df8.png', 'Электрический кроссовер Volkswagen', 'volkswagen-id4-2023'),

-- Zeekr
(20, 'X', 2024, 6, 4, 536, 86, 560, 3.8, 200, 59900.00, '/public/assets/images/zeekr.png', 'Премиальный китайский электрокроссовер', 'zeekr-x-2024');

-- Связываем автомобили с категориями
INSERT INTO car_categories (car_id, category_id) VALUES
-- Tesla Model S
(1, 1), (1, 3), (1, 5),
-- Tesla Model 3
(2, 1), (2, 3),
-- Tesla Model X
(3, 1), (3, 2), (3, 5),
-- Tesla Model Y
(4, 1), (4, 2), (4, 6),
-- Tesla Cybertruck
(5, 1), (5, 4),
-- Audi A6 e-tron
(6, 1), (6, 3), (6, 5),
-- Audi Q8 e-tron
(7, 1), (7, 2), (7, 5),
-- Audi e-tron GT
(8, 1), (8, 4), (8, 5);

-- Добавляем изображения для Tesla Model S
INSERT INTO car_images (car_id, image_url, is_main, alt_text) VALUES
(1, 'https://s.auto.drom.ru/i24222/r/photos/867729/gen448_1358223.jpg', TRUE, 'Tesla Model S 2023 фронтальный вид'),
(1, 'https://s.auto.drom.ru/i24222/r/photos/867729/gen107_1358226.jpg', FALSE, 'Tesla Model S 2023 интерьер'),
(1, 'https://s.auto.drom.ru/i24222/r/photos/867729/gen107_1358221.jpg', FALSE, 'Tesla Model S 2023 вид сбоку');

-- Добавляем характеристики для Tesla Model S
INSERT INTO car_features (car_id, feature_name, feature_value) VALUES
(1, 'Привод', 'Полный'),
(1, 'Количество мест', '5'),
(1, 'Вес', '2 162 кг'),
(1, 'Гарантия на батарею', '8 лет или 240 000 км'),
(1, 'Объем багажника', '793 л'),
(1, 'Клиренс', '138 мм'),
(1, 'Тип подвески', 'Пневматическая'),
(1, 'Режимы вождения', 'Стандарт, Спорт, Комфорт');