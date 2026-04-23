<?php

namespace App\Services;

use PDO;

class ExportService
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Exporta datos de métricas a CSV
     */
    public function exportToCSV(array $metrica_ids, int $periodos = 12, ?int $area_id = null)
    {
        $data = $this->getExportData($metrica_ids, $periodos, $area_id);

        $filename = 'metricas_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM para UTF-8 en Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Encabezados
        $headers = ['Métrica', 'Área', 'Unidad'];
        foreach ($data['periodos'] as $periodo) {
            $headers[] = $periodo['nombre'];
        }
        $headers[] = 'Meta';
        fputcsv($output, $headers);

        // Datos
        foreach ($data['metricas'] as $metrica) {
            $row = [
                $metrica['nombre'],
                $metrica['area_nombre'],
                $metrica['unidad']
            ];

            foreach ($data['periodos'] as $periodo) {
                $valor = $metrica['valores'][$periodo['id']] ?? '-';
                $row[] = $valor;
            }

            $row[] = $metrica['meta_valor'] ?? '-';

            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /**
     * Genera HTML optimizado para impresión/PDF
     */
    public function exportToPrintableHTML(array $metrica_ids, int $periodos = 12, ?int $area_id = null)
    {
        $data = $this->getExportData($metrica_ids, $periodos, $area_id);

        $area_nombre = $area_id ? $this->getAreaName($area_id) : 'Todas las áreas';
        $fecha = date('d/m/Y H:i');

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Reporte de Métricas - <?php echo htmlspecialchars($area_nombre); ?></title>
            <style>
                @page { margin: 2cm; }
                body {
                    font-family: Arial, sans-serif;
                    font-size: 11pt;
                    line-height: 1.4;
                    color: #333;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #3b82f6;
                }
                h1 {
                    font-size: 20pt;
                    margin: 0 0 10px 0;
                    color: #1e40af;
                }
                .meta-info {
                    font-size: 9pt;
                    color: #666;
                    margin-top: 5px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                    page-break-inside: avoid;
                }
                th {
                    background-color: #f1f5f9;
                    border: 1px solid #cbd5e1;
                    padding: 8px 6px;
                    font-size: 9pt;
                    text-align: left;
                    font-weight: 600;
                }
                td {
                    border: 1px solid #e2e8f0;
                    padding: 6px;
                    font-size: 9pt;
                }
                tr:nth-child(even) {
                    background-color: #f8fafc;
                }
                .numero {
                    text-align: right;
                    font-family: 'Courier New', monospace;
                }
                .footer {
                    margin-top: 30px;
                    padding-top: 10px;
                    border-top: 1px solid #e2e8f0;
                    font-size: 8pt;
                    color: #64748b;
                    text-align: center;
                }
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                    thead { display: table-header-group; }
                }
                @media screen {
                    body {
                        max-width: 1200px;
                        margin: 20px auto;
                        padding: 20px;
                    }
                    .no-print {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        z-index: 1000;
                    }
                    .btn {
                        background: #3b82f6;
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 6px;
                        cursor: pointer;
                        font-size: 14px;
                        margin-left: 10px;
                    }
                    .btn:hover {
                        background: #2563eb;
                    }
                }
            </style>
        </head>
        <body>
            <div class="no-print">
                <button class="btn" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
                <button class="btn" onclick="window.close()">✕ Cerrar</button>
            </div>

            <div class="header">
                <h1>Reporte de Métricas</h1>
                <div class="meta-info">
                    <strong>Área:</strong> <?php echo htmlspecialchars($area_nombre); ?> |
                    <strong>Generado:</strong> <?php echo $fecha; ?> |
                    <strong>Períodos:</strong> <?php echo count($data['periodos']); ?>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 25%">Métrica</th>
                        <th style="width: 15%">Área</th>
                        <th style="width: 10%">Unidad</th>
                        <?php foreach ($data['periodos'] as $periodo): ?>
                        <th class="numero" style="width: <?php echo floor(40 / count($data['periodos'])); ?>%">
                            <?php echo htmlspecialchars($periodo['nombre']); ?>
                        </th>
                        <?php endforeach; ?>
                        <th class="numero" style="width: 10%">Meta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['metricas'] as $metrica): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($metrica['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($metrica['area_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($metrica['unidad']); ?></td>
                        <?php foreach ($data['periodos'] as $periodo): ?>
                        <td class="numero">
                            <?php
                            $valor = $metrica['valores'][$periodo['id']] ?? null;
                            echo $valor !== null ? number_format($valor, $metrica['tipo_valor'] === 'decimal' ? 2 : 0) : '-';
                            ?>
                        </td>
                        <?php endforeach; ?>
                        <td class="numero">
                            <?php
                            echo $metrica['meta_valor'] ? number_format($metrica['meta_valor'], $metrica['tipo_valor'] === 'decimal' ? 2 : 0) : '-';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="footer">
                Sistema de Métricas | Generado automáticamente
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Obtiene datos para exportación
     */
    private function getExportData(array $metrica_ids, int $periodos, ?int $area_id)
    {
        // Obtener períodos
        $stmt = $this->db->prepare("
            SELECT * FROM periodos
            WHERE activo = 1
            ORDER BY ejercicio DESC, periodo DESC
            LIMIT ?
        ");
        $stmt->execute([$periodos]);
        $periodos_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener métricas
        $placeholders = implode(',', array_fill(0, count($metrica_ids), '?'));
        $area_filter = $area_id ? "AND m.area_id = ?" : "";

        $params = $metrica_ids;
        if ($area_id) {
            $params[] = $area_id;
        }

        $stmt = $this->db->prepare("
            SELECT
                m.*,
                a.nombre as area_nombre
            FROM metricas m
            JOIN areas a ON m.area_id = a.id
            WHERE m.id IN ($placeholders)
            $area_filter
            ORDER BY a.nombre, m.nombre
        ");
        $stmt->execute($params);
        $metricas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener valores
        foreach ($metricas as &$metrica) {
            $metrica['valores'] = [];

            foreach ($periodos_data as $periodo) {
                $stmt = $this->db->prepare("
                    SELECT valor_numero, valor_decimal
                    FROM valores_metricas
                    WHERE metrica_id = ? AND periodo_id = ?
                ");
                $stmt->execute([$metrica['id'], $periodo['id']]);
                $valor_row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($valor_row) {
                    $metrica['valores'][$periodo['id']] =
                        $metrica['tipo_valor'] === 'decimal'
                            ? $valor_row['valor_decimal']
                            : $valor_row['valor_numero'];
                }
            }

            // Obtener meta actual
            $stmt = $this->db->prepare("
                SELECT valor_objetivo
                FROM metas_metricas
                WHERE metrica_id = ?
                AND periodo_inicio_id <= ?
                AND (periodo_fin_id >= ? OR periodo_fin_id IS NULL)
                AND activo = 1
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $periodo_actual_id = $periodos_data[0]['id'];
            $stmt->execute([$metrica['id'], $periodo_actual_id, $periodo_actual_id]);
            $meta = $stmt->fetch(PDO::FETCH_ASSOC);
            $metrica['meta_valor'] = $meta ? $meta['valor_objetivo'] : null;
        }

        return [
            'metricas' => $metricas,
            'periodos' => $periodos_data
        ];
    }

    private function getAreaName(int $area_id): string
    {
        $stmt = $this->db->prepare("SELECT nombre FROM areas WHERE id = ?");
        $stmt->execute([$area_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['nombre'] : 'Área desconocida';
    }
}
