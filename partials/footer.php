<?php
/**
 * Footer reutilizable
 */
?>
<!-- Tabler Core -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
    
    <!-- GridStack (solo cargar en index.php) -->
    <?php if (basename($_SERVER['PHP_SELF']) === 'index.php'): ?>
        <script src="<?php echo BASE_URL; ?>/assets/js/gridstack.min.js"></script>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/gridstack.min.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/gridstack-extra.min.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard-grid.css">
    <?php endif; ?>
    
    <!-- ApexCharts -->
    <script src="<?php echo BASE_URL; ?>/assets/js/apexcharts.min.js"></script>
    
    <!-- App JS (si existe) -->
    <?php if (file_exists(BASE_PATH . '/assets/js/app.js')): ?>
        <script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
    <?php endif; ?>
</body>
</html>