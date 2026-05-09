<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Models\Reporte;
use App\Models\Periodo;

AuthMiddleware::handle();

$user = getCurrentUser();
$reporteModel = new Reporte();

$reporte_id = $_GET['id'] ?? null;

if (!$reporte_id) {
    die('ID de reporte no especificado');
}

$reporte = $reporteModel->findWithFullDetails($reporte_id);

if (!$reporte) {
    die('Reporte no encontrado');
}

// Verificar permisos
if ($user['rol'] !== 'super_admin' &&
    !($user['rol'] === 'dept_admin' && $user['departamento_id'] == $reporte['departamento_id']) &&
    !($user['rol'] === 'dept_viewer' && $user['departamento_id'] == $reporte['departamento_id'])) {
    die('No tienes permiso para ver este reporte');
}

$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
         'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

// Buscar el período correspondiente
$periodoModel = new Periodo();
$periodo = $periodoModel->findByEjercicioAndPeriodo($reporte['anio'], $reporte['mes']);

// Crear PDF personalizado con footer
class ReportePDF extends TCPDF {
    private $reporte_data;
    private $meses_array;

    public function setReporteData($data, $meses) {
        $this->reporte_data = $data;
        $this->meses_array = $meses;
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(100, 116, 139);

        $footer_text = 'Generado por: ' . ($this->reporte_data['autor_nombre'] ?? 'N/A') . ' | ';
        $footer_text .= 'Fecha: ' . date('d/m/Y H:i', strtotime($this->reporte_data['created_at'])) . ' | ';
        $footer_text .= 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages();

        $this->Cell(0, 10, $footer_text, 0, 0, 'C');
    }
}

// Crear instancia de PDF
$pdf = new ReportePDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->setReporteData($reporte, $meses);

// Configurar información del documento
$pdf->SetCreator('Sistema de Métricas');
$pdf->SetAuthor($reporte['autor_nombre'] ?? 'Sistema');
$pdf->SetTitle($reporte['titulo']);
$pdf->SetSubject($reporte['descripcion'] ?? '');

// Remover header por defecto
$pdf->setPrintHeader(false);

// Configurar márgenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 20);

// Configurar fuente
$pdf->SetFont('helvetica', '', 10);

// ==================== PÁGINA 1: PORTADA ====================
$pdf->AddPage();

// Fondo azul para portada
$pdf->SetFillColor(30, 64, 175);
$pdf->Rect(0, 0, 216, 100, 'F');

// Icono (círculo decorativo)
$pdf->SetFillColor(255, 255, 255, 30);
$pdf->Circle(108, 30, 15, 0, 360, 'F');
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetXY(15, 24);
$pdf->Cell(0, 10, 'REPORTE', 0, 1, 'C');

// Título principal
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetXY(15, 45);
$pdf->MultiCell(0, 8, $reporte['titulo'], 0, 'C', 0, 1, '', '', true, 0, false, true, 0, 'M');

// Departamento
$pdf->SetFont('helvetica', '', 14);
$pdf->SetXY(15, $pdf->GetY() + 2);
$pdf->Cell(0, 8, $reporte['departamento_nombre'], 0, 1, 'C');

// Mes y Año
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, $meses[$reporte['mes']] . ' ' . $reporte['anio'], 0, 1, 'C');

// Descripción
if (!empty($reporte['descripcion'])) {
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetXY(25, $pdf->GetY() + 5);
    $pdf->MultiCell(166, 5, $reporte['descripcion'], 0, 'C', 0, 1, '', '', true, 0, false, true, 0, 'M');
}

// Badge de estado
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor(255, 255, 255, 60);
$yPos = 92;
$pdf->SetXY(80, $yPos);
$pdf->Cell(40, 6, strtoupper($reporte['estado']), 0, 0, 'C', true);

// Resetear color
$pdf->SetTextColor(0, 0, 0);

// ==================== RESUMEN EJECUTIVO ====================
if (!empty($reporte['resumen_ejecutivo'])) {
    $pdf->AddPage();

    // Título
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(30, 64, 175);
    $pdf->Cell(0, 8, 'Resumen Ejecutivo', 0, 1, 'L');
    $pdf->SetDrawColor(30, 64, 175);
    $pdf->Line(15, $pdf->GetY(), 201, $pdf->GetY());
    $pdf->Ln(5);

    // Contenido
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(248, 250, 252);

    $texto = $reporte['resumen_ejecutivo'];
    $texto = preg_replace('/\n{3,}/', "\n\n", $texto);

    $pdf->MultiCell(0, 5, $texto, 0, 'L', true, 1, '', '', true, 0, false, true, 0, 'T');
    $pdf->Ln(5);
}

// ==================== DETALLE POR ÁREA ====================
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(30, 64, 175);
$pdf->Cell(0, 8, 'Detalle por Área', 0, 1, 'L');
$pdf->SetDrawColor(30, 64, 175);
$pdf->Line(15, $pdf->GetY(), 201, $pdf->GetY());
$pdf->Ln(3);

// Info del período
$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetTextColor(100, 116, 139);
$pdf->Cell(0, 5, 'Datos al cierre de ' . $meses[$reporte['mes']] . ' ' . $reporte['anio'], 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetTextColor(0, 0, 0);

if (!empty($reporte['areas'])) {
    foreach ($reporte['areas'] as $area) {
        // Verificar espacio
        if ($pdf->GetY() > 240) {
            $pdf->AddPage();
        }

        $y_start = $pdf->GetY();

        // Nombre del área
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(0, 7, $area['nombre'], 0, 1, 'L');

        // Descripción
        if (!empty($area['descripcion'])) {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->MultiCell(0, 4, $area['descripcion'], 0, 'L', false, 1);
        }

        $pdf->Ln(2);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->Line(15, $pdf->GetY(), 201, $pdf->GetY());
        $pdf->Ln(3);

        $pdf->SetTextColor(0, 0, 0);

        // Gráficos
        if (!empty($area['graficos'])) {
            foreach ($area['graficos'] as $grafico) {
                if ($pdf->GetY() > 250) {
                    $pdf->AddPage();
                }

                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetX(20);
                $pdf->Cell(0, 6, $grafico['titulo'], 0, 1, 'L');

                $pdf->SetFont('helvetica', 'I', 9);
                $pdf->SetTextColor(100, 116, 139);
                $pdf->SetX(20);
                $pdf->MultiCell(0, 5, '[Gráfico interactivo - Use "Imprimir" desde la web para visualización completa]', 0, 'C', false, 1);
                $pdf->Ln(2);
                $pdf->SetTextColor(0, 0, 0);
            }
        } else {
            $pdf->SetFont('helvetica', 'I', 9);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->SetX(20);
            $pdf->Cell(0, 5, 'No hay gráficos configurados', 0, 1, 'L');
        }

        // Borde del área
        $y_end = $pdf->GetY();
        $pdf->SetDrawColor(59, 130, 246);
        $pdf->Rect(15, $y_start, 186, $y_end - $y_start);

        $pdf->Ln(8);
        $pdf->SetTextColor(0, 0, 0);
    }
} else {
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->Cell(0, 10, 'No hay áreas configuradas', 0, 1, 'C');
}

// Generar nombre del archivo
$filename = preg_replace('/[^A-Za-z0-9-]+/', '-', $reporte['titulo']);
$filename = strtolower(trim($filename, '-'));
$filename = substr($filename, 0, 50);
$filename = $filename . '_' . $reporte['anio'] . '_' . $reporte['mes'] . '.pdf';

// Salida
$pdf->Output($filename, 'D');

exit;
?>
