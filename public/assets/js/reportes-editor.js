/**
 * ================================================
 * EDITOR DE REPORTES CON EASYMDE
 * ================================================
 */

let easyMDE = null;
let autoSaveInterval = null;
let unsavedChanges = false;
let currentReporteId = typeof REPORTE_ID !== 'undefined' ? REPORTE_ID : null;
let currentAreaId = typeof AREA_ID !== 'undefined' ? AREA_ID : null;

// ========================================
// INICIALIZACIÓN
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    initMarkdownEditor();
    initAutoSave();
    initThemeToggle();
    initSaveButtons();
    initMetadataHidden();
    preventAccidentalExit();
});

// ========================================
// EASYMDE MARKDOWN EDITOR - CONFIGURACIÓN
// ========================================
function initMarkdownEditor() {
    // Inicializar EasyMDE
    easyMDE = new EasyMDE({
        element: document.getElementById('markdown-editor'),
        spellChecker: false,
        autosave: {
            enabled: true,
            uniqueId: "reporte-" + (currentReporteId || 'nuevo'),
            delay: 3000,
        },
        placeholder: "Escribe tu reporte en Markdown...\n\n## Ejemplo de encabezado\n\nTexto normal con **negritas** y *cursivas*.\n\n- Lista\n- De\n- Items\n\n{{grafico:1}} <- Insertar gráfico con ID",
        toolbar: [
            "bold", "italic", "heading", "|",
            "quote", "unordered-list", "ordered-list", "|",
            "link", "image", "|",
            "preview", "side-by-side", "fullscreen", "|",
            "guide"
        ],
        renderingConfig: {
            singleLineBreaks: false,
        },
        status: ["lines", "words", "cursor"]
    });

    // Mover toolbar al header sticky
    setTimeout(() => {
        const toolbar = document.querySelector('.EasyMDEContainer .editor-toolbar');
        const toolbarHeader = document.querySelector('.toolbar-header');

        if (toolbar && toolbarHeader) {
            toolbarHeader.appendChild(toolbar);
        }
    }, 100);

    // Actualizar contador de palabras
    easyMDE.codemirror.on("change", function(){
        unsavedChanges = true;
        updateWordCount();
    });

    console.log('EasyMDE Markdown Editor inicializado');
    updateWordCount();
}

// ========================================
// METADATA OCULTA (guardado en background)
// ========================================
function initMetadataHidden() {
    // Si viene área por URL, crear inputs hidden
    if (currentAreaId) {
        createHiddenInputs();

        // Guardar metadata inicial
        saveInitialMetadata();
    }
}

function createHiddenInputs() {
    const form = document.createElement('form');
    form.id = 'hidden-metadata-form';
    form.style.display = 'none';

    // Obtener parámetros de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const tipoReporte = urlParams.get('tipo_reporte') || 'mensual';
    const periodoId = urlParams.get('periodo_id') || '';
    const anio = urlParams.get('anio') || new Date().getFullYear();

    // Título automático
    const titulo = document.createElement('input');
    titulo.type = 'hidden';
    titulo.id = 'titulo';
    titulo.value = 'Nuevo Reporte - ' + new Date().toLocaleDateString('es-GT');

    // Área
    const area = document.createElement('input');
    area.type = 'hidden';
    area.id = 'area_id';
    area.value = currentAreaId;

    // Tipo
    const tipo = document.createElement('input');
    tipo.type = 'hidden';
    tipo.id = 'tipo_reporte';
    tipo.value = tipoReporte;

    // Año
    const anioInput = document.createElement('input');
    anioInput.type = 'hidden';
    anioInput.id = 'anio';
    anioInput.value = anio;

    // Período
    const periodo = document.createElement('input');
    periodo.type = 'hidden';
    periodo.id = 'periodo_id';
    periodo.value = periodoId;

    // Descripción
    const descripcion = document.createElement('textarea');
    descripcion.style.display = 'none';
    descripcion.id = 'descripcion';
    descripcion.value = '';

    form.appendChild(titulo);
    form.appendChild(area);
    form.appendChild(tipo);
    form.appendChild(anioInput);
    form.appendChild(periodo);
    form.appendChild(descripcion);

    document.body.appendChild(form);
}

function saveInitialMetadata() {
    // Esperar 1 segundo y hacer el primer guardado
    setTimeout(() => {
        if (!currentReporteId) {
            saveReporte(true);
        }
    }, 1000);
}

// ========================================
// AUTOGUARDADO
// ========================================
function initAutoSave() {
    autoSaveInterval = setInterval(() => {
        if (unsavedChanges && editor) {
            saveReporte(true);
        }
    }, 15000); // Cada 15 segundos

    console.log('Autoguardado activado (cada 15 segundos)');
}

function saveReporte(isAutoSave = false) {
    if (!easyMDE) return;

    showSaveIndicator('saving');

    const formData = new FormData();

    if (currentReporteId) {
        formData.append('id', currentReporteId);
    }

    // Metadata
    const titulo = document.getElementById('titulo')?.value || 'Nuevo Reporte';
    const areaId = document.getElementById('area_id')?.value || currentAreaId;
    const tipoReporte = document.getElementById('tipo_reporte')?.value || 'mensual';
    const anio = document.getElementById('anio')?.value || new Date().getFullYear();
    const periodoId = document.getElementById('periodo_id')?.value || '';
    const descripcion = document.getElementById('descripcion')?.value || '';

    formData.append('titulo', titulo);
    formData.append('area_id', areaId);
    formData.append('tipo_reporte', tipoReporte);
    formData.append('anio', anio);
    formData.append('periodo_id', periodoId);
    formData.append('descripcion', descripcion);

    // Contenido Markdown del editor
    const contenido = easyMDE.value();
    formData.append('contenido', contenido);
    formData.append('auto_save', isAutoSave ? '1' : '0');

    fetch('reportes-save.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Verificar que la respuesta sea JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // Obtener el texto de la respuesta para ver el error PHP real
            return response.text().then(text => {
                console.error('Respuesta del servidor (NO es JSON):', text);
                throw new Error('La respuesta no es JSON. Ver consola para detalles.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            unsavedChanges = false;

            if (!currentReporteId && data.reporte_id) {
                currentReporteId = data.reporte_id;
                history.replaceState(null, '', `reportes-editor.php?id=${currentReporteId}`);
            }

            showSaveIndicator('saved');

            if (!isAutoSave) {
                Swal.fire({
                    icon: 'success',
                    title: 'Guardado',
                    text: 'Reporte guardado correctamente',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        } else {
            showSaveIndicator('error');
            console.error('Error del servidor:', data.error);
            if (!isAutoSave) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.error || 'Error al guardar',
                    confirmButtonColor: '#3b82f6'
                });
            }
        }
    })
    .catch(error => {
        console.error('Error al guardar:', error);
        showSaveIndicator('error');

        // Solo mostrar alert si es guardado manual
        if (!isAutoSave) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: error.message,
                footer: 'Revisa la consola para más detalles',
                confirmButtonColor: '#3b82f6'
            });
        }
    });
}

function showSaveIndicator(state) {
    const indicator = document.getElementById('save-indicator');
    const spinner = document.getElementById('save-spinner');
    const text = document.getElementById('save-text');

    if (!indicator) return;

    indicator.className = 'save-indicator';

    switch(state) {
        case 'saving':
            indicator.classList.add('saving');
            spinner?.classList.remove('d-none');
            if (text) text.textContent = 'Guardando...';
            break;
        case 'saved':
            indicator.classList.add('saved');
            spinner?.classList.add('d-none');
            if (text) text.textContent = 'Guardado';
            break;
        case 'error':
            indicator.classList.add('error');
            spinner?.classList.add('d-none');
            if (text) text.textContent = 'Error';
            break;
    }
}

// ========================================
// TOGGLE TEMA
// ========================================
function initThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');

    if (!themeToggle) return;

    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        document.documentElement.setAttribute('data-bs-theme', newTheme);

        if (newTheme === 'dark') {
            themeIcon.classList.remove('ti-moon');
            themeIcon.classList.add('ti-sun');
        } else {
            themeIcon.classList.remove('ti-sun');
            themeIcon.classList.add('ti-moon');
        }

        fetch('../api/cambiar-tema.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tema: newTheme })
        });
    });
}

// ========================================
// BOTONES
// ========================================
function initSaveButtons() {
    // Los botones ahora están en el menú Archivo y se manejan en el HTML inline
    // Esta función se mantiene por compatibilidad pero no hace nada
}

function publishReporte() {
    if (!currentReporteId) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Primero guarda el reporte',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }

    Swal.fire({
        title: '¿Publicar este reporte?',
        text: "Una vez publicado, estará visible para todos los usuarios con acceso",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, publicar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Publicando...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('reportes-publish.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: currentReporteId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Publicado!',
                        text: 'El reporte ha sido publicado correctamente',
                        confirmButtonColor: '#3b82f6'
                    }).then(() => {
                        window.location.href = 'reportes.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'Error al publicar',
                        confirmButtonColor: '#3b82f6'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: error.message,
                    confirmButtonColor: '#3b82f6'
                });
            });
        }
    });
}

// ========================================
// CONTADOR DE PALABRAS
// ========================================
function updateWordCount() {
    if (!easyMDE) return;

    const text = easyMDE.value();
    const words = text.trim().split(/\s+/).filter(word => word.length > 0).length;

    const wordCountEl = document.getElementById('word-count');
    if (wordCountEl) {
        wordCountEl.textContent = `${words} palabra${words !== 1 ? 's' : ''}`;
    }
}

// ========================================
// PREVENIR SALIDA ACCIDENTAL
// ========================================
function preventAccidentalExit() {
    window.addEventListener('beforeunload', function (e) {
        if (unsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
}

// ========================================
// INSERTAR GRÁFICAS
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const btnInsertChart = document.getElementById('btn-insert-chart');
    if (btnInsertChart) {
        btnInsertChart.addEventListener('click', function(e) {
            e.preventDefault();
            openChartGallery();
        });
    }
});

function openChartGallery() {
    const modal = new bootstrap.Modal(document.getElementById('modal-insert-chart'));
    modal.show();

    // Cargar gráficas del área
    loadChartsFromArea();
}

function loadChartsFromArea() {
    const areaId = document.getElementById('area_id')?.value || currentAreaId;

    if (!areaId) {
        alert('No se puede determinar el área del reporte');
        return;
    }

    const gallery = document.getElementById('chart-gallery');
    gallery.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"></div><p class="mt-2">Cargando gráficas...</p></div>';

    fetch(`../../api/get-graficos-area.php?area_id=${areaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.graficos.length > 0) {
                renderChartGallery(data.graficos);
            } else {
                gallery.innerHTML = '<div class="alert alert-info">No hay gráficas disponibles en esta área</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            gallery.innerHTML = '<div class="alert alert-danger">Error al cargar las gráficas</div>';
        });
}

function renderChartGallery(graficos) {
    const gallery = document.getElementById('chart-gallery');
    gallery.innerHTML = '';

    graficos.forEach(grafico => {
        const card = document.createElement('div');
        card.className = 'chart-thumbnail';
        card.innerHTML = `
            <div class="chart-preview">
                <i class="ti ti-chart-bar" style="font-size: 48px; color: #3b82f6;"></i>
            </div>
            <div class="chart-info">
                <strong>${grafico.nombre}</strong>
                <small class="d-block text-muted">${grafico.tipo_grafico}</small>
            </div>
        `;

        card.addEventListener('click', function() {
            insertChartIntoEditor(grafico);
        });

        gallery.appendChild(card);
    });
}

async function insertChartIntoEditor(grafico) {
    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modal-insert-chart'));
    modal.hide();

    // Si no existe reporte, guardarlo primero
    if (!currentReporteId) {
        try {
            await saveReporte();
            if (!currentReporteId) {
                alert('Error: No se pudo guardar el reporte. Intenta guardarlo manualmente primero.');
                return;
            }
        } catch (error) {
            console.error('Error guardando reporte:', error);
            alert('Debes guardar el reporte antes de insertar gráficas');
            return;
        }
    }

    // Obtener la imagen de la gráfica renderizada
    captureChartAsImage(grafico.id)
        .then(imageDataUrl => {
            // Insertar imagen en Markdown
            const cm = easyMDE.codemirror;
            const doc = cm.getDoc();
            const cursor = doc.getCursor();

            // Insertar sintaxis Markdown para imagen
            const markdownImage = `\n![${grafico.nombre}](${imageDataUrl})\n`;
            doc.replaceRange(markdownImage, cursor);

            // Mover cursor después de la imagen
            cm.focus();
            unsavedChanges = true;

            // Guardar relación en BD
            saveChartRelation(grafico.id);
        })
        .catch(error => {
            console.error('Error capturando gráfica:', error);
            alert('Error al insertar la gráfica: ' + error.message);
        });
}

function captureChartAsImage(graficoId) {
    return fetch(`../../api/capture-grafico.php?grafico_id=${graficoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Renderizar gráfica en canvas temporal y capturar como imagen
                return renderChartToImage(data.chartConfig);
            } else {
                throw new Error(data.error || 'Error al obtener gráfica');
            }
        });
}

function renderChartToImage(chartConfig) {
    return new Promise((resolve, reject) => {
        // Crear contenedor temporal
        const tempDiv = document.createElement('div');
        tempDiv.style.width = '800px';
        tempDiv.style.height = '400px';
        tempDiv.style.position = 'absolute';
        tempDiv.style.left = '-9999px';
        document.body.appendChild(tempDiv);

        // Configurar opciones de ApexCharts
        const options = {
            series: chartConfig.series,
            chart: {
                type: chartConfig.type,
                height: 400,
                width: 800,
                animations: {
                    enabled: false
                },
                toolbar: {
                    show: false
                }
            },
            title: {
                text: chartConfig.title,
                align: 'center'
            },
            xaxis: {
                categories: chartConfig.categories
            },
            dataLabels: {
                enabled: true
            }
        };

        // Renderizar gráfica
        const chart = new ApexCharts(tempDiv, options);

        chart.render().then(() => {
            // Esperar a que se complete el renderizado
            setTimeout(() => {
                // Capturar como imagen
                chart.dataURI().then(({ imgURI }) => {
                    // Limpiar
                    chart.destroy();
                    document.body.removeChild(tempDiv);

                    resolve(imgURI);
                }).catch(error => {
                    document.body.removeChild(tempDiv);
                    reject(error);
                });
            }, 500);
        }).catch(error => {
            document.body.removeChild(tempDiv);
            reject(error);
        });
    });
}

function saveChartRelation(graficoId) {
    fetch('../../api/save-chart-relation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            reporte_id: currentReporteId,
            grafico_id: graficoId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Relación gráfica-reporte guardada');
        } else {
            console.error('Error guardando relación:', data.error);
        }
    })
    .catch(error => {
        console.error('Error guardando relación:', error);
    });
}

// ========================================
// REDIMENSIONAR IMÁGENES (DESHABILITADO PARA MARKDOWN)
// ========================================
// Nota: EasyMDE maneja las imágenes de forma diferente en Markdown.
// El redimensionamiento se puede hacer con sintaxis HTML si es necesario:
// <img src="..." width="600">
// O mediante CSS personalizado en la vista previa.

// ========================================
// CLEANUP
// ========================================
window.addEventListener('unload', function() {
    if (autoSaveInterval) {
        clearInterval(autoSaveInterval);
    }
});
