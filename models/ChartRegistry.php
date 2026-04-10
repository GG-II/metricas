<?php
/**
 * Registro de Gráficos - Sistema de Auto-descubrimiento
 * Lee automáticamente todos los gráficos de /components/charts/
 */

class ChartRegistry {
    private static $charts = [];
    private static $loaded = false;
    
    /**
     * Cargar todos los gráficos disponibles
     */
    public static function load() {
        if (self::$loaded) return;
        
        $charts_dir = BASE_PATH . '/components/charts';
        
        if (!is_dir($charts_dir)) {
            mkdir($charts_dir, 0755, true);
        }
        
        // Escanear archivos PHP en la carpeta
        $files = glob($charts_dir . '/*.php');
        
        foreach ($files as $file) {
            $chart_config = include $file;
            
            // Validar estructura
            if (is_array($chart_config) && isset($chart_config['meta'])) {
                $id = $chart_config['meta']['id'];
                self::$charts[$id] = $chart_config;
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Obtener todos los tipos disponibles
     */
    public static function getAll() {
        self::load();
        return self::$charts;
    }
    
    /**
     * Obtener metadata de todos los gráficos (para dropdown)
     */
    public static function getMetadata() {
        self::load();
        
        $metadata = [];
        foreach (self::$charts as $id => $chart) {
            $metadata[$id] = $chart['meta'];
        }
        
        return $metadata;
    }
    
    /**
     * Obtener un gráfico específico
     */
    public static function get($id) {
        self::load();
        return self::$charts[$id] ?? null;
    }
    
    /**
     * Renderizar formulario de configuración
     */
    public static function renderForm($id) {
        $chart = self::get($id);
        
        if (!$chart || !isset($chart['form'])) {
            return '<div class="alert alert-warning">Formulario no disponible</div>';
        }
        
        return is_callable($chart['form']) ? $chart['form']() : $chart['form'];
    }
    
    /**
     * Procesar datos del formulario
     */
    public static function processForm($id, $post) {
        $chart = self::get($id);
        
        if (!$chart || !isset($chart['process'])) {
            return [];
        }
        
        return is_callable($chart['process']) ? $chart['process']($post) : [];
    }
    
    /**
     * Renderizar widget
     */
    public static function render($id, $config, $metrica_data, $area_color) {
        $chart = self::get($id);
        
        if (!$chart || !isset($chart['render'])) {
            return '<div class="alert alert-danger">Gráfico no encontrado</div>';
        }
        
        return is_callable($chart['render']) 
            ? $chart['render']($config, $metrica_data, $area_color)
            : '';
    }
}