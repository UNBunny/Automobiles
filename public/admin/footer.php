        </main>
    </div>

    <script>
        // Подтверждение удаления
        function confirmDelete(message = 'Вы уверены, что хотите удалить этот элемент?') {
            return confirm(message);
        }

        // Автоматическое создание slug из названия
        function generateSlug(name, slugField) {
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9а-я\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            document.getElementById(slugField).value = slug;
        }

        // Предварительный просмотр изображения
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById(previewId);
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Показать/скрыть пароль
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Автосохранение формы
        function autoSave() {
            // Можно добавить функционал автосохранения
        }

        // Уведомления
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.textContent = message;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.style.minWidth = '300px';
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        // Инициализация при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            // Бургер-меню для админ-панели
            const burgerMenu = document.getElementById('burger-menu-admin');
            const sidebar = document.querySelector('.sidebar');
            
            if (burgerMenu && sidebar) {
                burgerMenu.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });

                // Закрытие меню при клике вне его
                document.addEventListener('click', function(e) {
                    if (!sidebar.contains(e.target) && !burgerMenu.contains(e.target)) {
                        sidebar.classList.remove('active');
                    }
                });
            }

            // Автофокус на первое поле формы
            const firstInput = document.querySelector('input[type="text"], input[type="email"], textarea');
            if (firstInput) {
                firstInput.focus();
            }

            // Скрытие уведомлений через 5 секунд
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>