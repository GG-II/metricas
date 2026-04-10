<?php
/**
 * Modelo Area
 */

require_once __DIR__ . '/Model.php';

class Area extends Model {
    protected $table = 'areas';
    
    /**
     * Obtener áreas activas ordenadas
     */
    public function getActivas() {
        return $this->getAll(['activo' => 1], 'orden ASC');
    }
}