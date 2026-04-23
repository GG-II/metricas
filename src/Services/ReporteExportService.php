<?php

namespace App\Services;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use TCPDF;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Utils\Parsedown;

class ReporteExportService
{
    private $reporte;

    public function __construct($reporte)
    {
        $this->reporte = $reporte;
    }

    /**
     * Convierte contenido Markdown a HTML
     */
    private function markdownToHTML($markdown)
    {
        $parsedown = new Parsedown();
        return $parsedown->text($markdown);
    }

    /**
     * Exportar a DOCX (Microsoft Word)
     */
    public function exportToDocx()
    {
        $phpWord = new PhpWord();

        // Configurar propiedades del documento
        $properties = $phpWord->getDocInfo();
        $properties->setCreator($this->reporte['autor_nombre'] ?? 'Sistema de Métricas');
        $properties->setTitle($this->reporte['titulo']);
        $properties->setDescription($this->reporte['descripcion'] ?? '');
        $properties->setCategory('Reporte de Métricas');
        $properties->setCreated(strtotime($this->reporte['created_at']));
        $properties->setModified(strtotime($this->reporte['updated_at']));

        // Configurar sección
        $section = $phpWord->addSection([
            'marginTop' => 1440,    // 1 inch = 1440 twips
            'marginBottom' => 1440,
            'marginLeft' => 1440,
            'marginRight' => 1440,
        ]);

        // Agregar encabezado
        $header = $section->addHeader();
        $header->addText(
            $this->reporte['area_nombre'] . ' - ' . $this->reporte['departamento_nombre'],
            ['size' => 9, 'color' => '666666']
        );

        // Título principal
        $section->addText(
            $this->reporte['titulo'],
            ['size' => 20, 'bold' => true],
            ['alignment' => 'center', 'spaceAfter' => 240]
        );

        // Metadata
        $metaText = '';
        if ($this->reporte['periodo_nombre']) {
            $metaText .= 'Período: ' . $this->reporte['periodo_nombre'] . ' | ';
        }
        $metaText .= 'Año: ' . $this->reporte['anio'];
        if ($this->reporte['autor_nombre']) {
            $metaText .= ' | Autor: ' . $this->reporte['autor_nombre'];
        }

        $section->addText(
            $metaText,
            ['size' => 10, 'color' => '666666'],
            ['alignment' => 'center', 'spaceAfter' => 480]
        );

        // Línea separadora
        $section->addLine([
            'weight' => 1,
            'width' => 450,
            'height' => 0,
            'color' => 'CCCCCC'
        ]);

        $section->addTextBreak(1);

        // Contenido HTML convertido
        try {
            Html::addHtml($section, $this->reporte['contenido'], false, false);
        } catch (\Exception $e) {
            // Si falla la conversión HTML, agregar como texto plano
            $section->addText(strip_tags($this->reporte['contenido']));
        }

        // Pie de página
        $footer = $section->addFooter();
        $footer->addPreserveText(
            'Página {PAGE} de {NUMPAGES}',
            ['size' => 9, 'color' => '666666'],
            ['alignment' => 'center']
        );

        // Generar archivo
        $filename = $this->generateFilename('docx');
        $filepath = sys_get_temp_dir() . '/' . $filename;

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($filepath);

        return [
            'filepath' => $filepath,
            'filename' => $filename,
            'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
    }

    /**
     * Exportar a DOC (Microsoft Word 97-2003)
     */
    public function exportToDoc()
    {
        // Usar el mismo proceso que DOCX pero con formato RTF
        $phpWord = new PhpWord();

        $section = $phpWord->addSection();

        // Título
        $section->addText(
            $this->reporte['titulo'],
            ['size' => 20, 'bold' => true]
        );

        $section->addTextBreak(1);

        // Contenido
        try {
            Html::addHtml($section, $this->reporte['contenido'], false, false);
        } catch (\Exception $e) {
            $section->addText(strip_tags($this->reporte['contenido']));
        }

        // Generar archivo RTF
        $filename = $this->generateFilename('rtf');
        $filepath = sys_get_temp_dir() . '/' . $filename;

        $writer = IOFactory::createWriter($phpWord, 'RTF');
        $writer->save($filepath);

        return [
            'filepath' => $filepath,
            'filename' => str_replace('.rtf', '.doc', $filename),
            'mime' => 'application/msword'
        ];
    }

    /**
     * Exportar a PDF usando TCPDF
     */
    public function exportToPDF()
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Configurar propiedades del documento
        $pdf->SetCreator($this->reporte['autor_nombre'] ?? 'Sistema de Métricas');
        $pdf->SetAuthor($this->reporte['autor_nombre'] ?? 'Sistema');
        $pdf->SetTitle($this->reporte['titulo']);
        $pdf->SetSubject($this->reporte['descripcion'] ?? '');

        // Configurar encabezado y pie de página
        $pdf->SetHeaderData('', 0,
            $this->reporte['titulo'],
            $this->reporte['area_nombre'] . ' - ' . $this->reporte['departamento_nombre']
        );

        $pdf->setHeaderFont(['helvetica', '', 10]);
        $pdf->setFooterFont(['helvetica', '', 8]);

        $pdf->SetMargins(20, 30, 20);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(15);

        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Agregar página
        $pdf->AddPage();

        // Título principal
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 10, $this->reporte['titulo'], 0, 1, 'C');
        $pdf->Ln(5);

        // Metadata
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(100, 100, 100);
        $metaText = '';
        if ($this->reporte['periodo_nombre']) {
            $metaText .= 'Período: ' . $this->reporte['periodo_nombre'] . ' | ';
        }
        $metaText .= 'Año: ' . $this->reporte['anio'];
        $pdf->Cell(0, 6, $metaText, 0, 1, 'C');
        $pdf->Ln(8);

        // Línea separadora
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(8);

        // Contenido
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(0, 0, 0);

        // Convertir Markdown a HTML y escribir
        $htmlContent = $this->markdownToHTML($this->reporte['contenido']);
        $pdf->writeHTML($htmlContent, true, false, true, false, '');

        // Generar archivo
        $filename = $this->generateFilename('pdf');
        $filepath = sys_get_temp_dir() . '/' . $filename;

        $pdf->Output($filepath, 'F');

        return [
            'filepath' => $filepath,
            'filename' => $filename,
            'mime' => 'application/pdf'
        ];
    }

    /**
     * Exportar a PDF usando Dompdf (alternativa más simple)
     */
    public function exportToPDFSimple()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        // Preparar HTML completo
        $html = $this->buildFullHTML();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Generar archivo
        $filename = $this->generateFilename('pdf');
        $filepath = sys_get_temp_dir() . '/' . $filename;

        file_put_contents($filepath, $dompdf->output());

        return [
            'filepath' => $filepath,
            'filename' => $filename,
            'mime' => 'application/pdf'
        ];
    }

    /**
     * Exportar a HTML puro (para imprimir o ver en navegador)
     */
    public function exportToHTML()
    {
        $html = $this->buildFullHTML();

        $filename = $this->generateFilename('html');
        $filepath = sys_get_temp_dir() . '/' . $filename;

        file_put_contents($filepath, $html);

        return [
            'filepath' => $filepath,
            'filename' => $filename,
            'mime' => 'text/html'
        ];
    }

    /**
     * Exportar a texto plano
     */
    public function exportToTXT()
    {
        $content = "====================================\n";
        $content .= strtoupper($this->reporte['titulo']) . "\n";
        $content .= "====================================\n\n";

        $content .= "Área: " . $this->reporte['area_nombre'] . "\n";
        $content .= "Departamento: " . $this->reporte['departamento_nombre'] . "\n";
        if ($this->reporte['periodo_nombre']) {
            $content .= "Período: " . $this->reporte['periodo_nombre'] . "\n";
        }
        $content .= "Año: " . $this->reporte['anio'] . "\n";
        $content .= "Autor: " . ($this->reporte['autor_nombre'] ?? 'N/A') . "\n";
        $content .= "\n------------------------------------\n\n";

        // Convertir HTML a texto plano
        $textContent = strip_tags($this->reporte['contenido']);
        $textContent = html_entity_decode($textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $textContent = preg_replace('/\n\s*\n/', "\n\n", $textContent);

        $content .= $textContent;

        $filename = $this->generateFilename('txt');
        $filepath = sys_get_temp_dir() . '/' . $filename;

        file_put_contents($filepath, $content);

        return [
            'filepath' => $filepath,
            'filename' => $filename,
            'mime' => 'text/plain'
        ];
    }

    /**
     * Construir HTML completo con estilos para PDF
     */
    private function buildFullHTML()
    {
        $html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($this->reporte['titulo']) . '</title>
    <style>
        @page {
            margin: 2.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #000000;
            background-color: #ffffff;
        }
        h1 {
            text-align: center;
            font-size: 20pt;
            margin-bottom: 10px;
            color: #1a1a1a;
        }
        .metadata {
            text-align: center;
            color: #666666;
            font-size: 10pt;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #cccccc;
        }
        .content {
            text-align: justify;
        }
        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 15px auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 15px 0;
        }
        table th, table td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9pt;
            color: #666666;
            border-top: 1px solid #cccccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($this->reporte['titulo']) . '</h1>

    <div class="metadata">
        <strong>' . htmlspecialchars($this->reporte['area_nombre']) . '</strong> - ' .
        htmlspecialchars($this->reporte['departamento_nombre']) . '<br>';

        if ($this->reporte['periodo_nombre']) {
            $html .= 'Período: ' . htmlspecialchars($this->reporte['periodo_nombre']) . ' | ';
        }

        $html .= 'Año: ' . $this->reporte['anio'];

        if ($this->reporte['autor_nombre']) {
            $html .= '<br>Autor: ' . htmlspecialchars($this->reporte['autor_nombre']);
        }

        $html .= '<br><small>Generado: ' . date('d/m/Y H:i') . '</small>
    </div>

    <div class="content">
        ' . $this->markdownToHTML($this->reporte['contenido']) . '
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Generar nombre de archivo único
     */
    private function generateFilename($extension)
    {
        $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->reporte['titulo']);
        $slug = strtolower(trim($slug, '-'));
        $slug = substr($slug, 0, 50);

        return $slug . '_' . $this->reporte['anio'] . '_' . time() . '.' . $extension;
    }

    /**
     * Descargar archivo (enviar headers y contenido)
     */
    public static function download($filepath, $filename, $mime)
    {
        if (!file_exists($filepath)) {
            throw new \Exception('Archivo no encontrado');
        }

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');

        readfile($filepath);

        // Eliminar archivo temporal
        unlink($filepath);

        exit;
    }
}
