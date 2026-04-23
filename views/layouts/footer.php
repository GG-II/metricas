    <!-- Tabler Core -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>

    <!-- Toast Notifications -->
    <script src="<?php echo baseUrl('/public/assets/js/toast-notifications.js'); ?>"></script>

    <!-- Custom JS -->
    <script>
        // Flash message auto-hide
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            // Theme toggle
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');
            const html = document.documentElement;

            if (themeToggle) {
                // Actualizar ícono inicial
                updateThemeIcon();

                themeToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const currentTheme = html.getAttribute('data-bs-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                    // Cambiar tema
                    html.setAttribute('data-bs-theme', newTheme);
                    updateThemeIcon();

                    // Guardar preferencia en BD
                    fetch('<?php echo baseUrl("/public/api/cambiar-tema.php"); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'tema=' + newTheme
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            console.error('Error al guardar tema:', data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            }

            function updateThemeIcon() {
                const currentTheme = html.getAttribute('data-bs-theme');
                if (themeIcon) {
                    if (currentTheme === 'dark') {
                        themeIcon.classList.remove('ti-moon');
                        themeIcon.classList.add('ti-sun');
                    } else {
                        themeIcon.classList.remove('ti-sun');
                        themeIcon.classList.add('ti-moon');
                    }
                }
            }
        });
    </script>

    <?php if (isset($customScripts)): ?>
        <?php echo $customScripts; ?>
    <?php endif; ?>
</body>
</html>
