/**
 * ================================================
 * EDITOR DE REPORTES CON PAGINACIÓN AUTOMÁTICA
 * ================================================
 * Sistema de páginas reales con altura fija donde el contenido
 * fluye automáticamente entre páginas, como en Word.
 */

let pages = [];
let unsavedChanges = false;
let autoSaveInterval = null;
let currentReporteId = typeof REPORTE_ID !== 'undefined' ? REPORTE_ID : null;
let currentAreaId = typeof AREA_ID !== 'undefined' ? AREA_ID : null;

// Constantes de página Letter
const PAGE_HEIGHT_CM = 27.94;
const PAGE_WIDTH_CM = 21.59;
const MARGIN_CM = 2.54;
const CONTENT_HEIGHT_CM = PAGE_HEIGHT_CM - (MARGIN_CM * 2);
const CM_TO_PX = 37.795275591;
const CONTENT_HEIGHT_PX = CONTENT_HEIGHT_CM * CM_TO_PX;

// ========================================
// INICIALIZACIÓN
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    initializeEditor();
    initToolbar();
    initAutoSave();
    initThemeToggle();
    initMetadataHidden();
    preventAccidentalExit();
});

// ========================================
// CREAR SISTEMA DE PÁGINAS
// ========================================
function initializeEditor() {
    const container = document.getElementById('pages-container');

    // Obtener contenido inicial si existe
    const initialContent = typeof INITIAL_CONTENT !== 'undefined' ? INITIAL_CONTENT : '';

    // Crear primera página
    createPage(container, initialContent);

    console.log('Editor con paginación automática inicializado');
}

function createPage(container, content = '') {
    const pageNum = pages.length + 1;

    const pageWrapper = document.createElement('div');
    pageWrapper.className = 'document-page';
    pageWrapper.dataset.pageNumber = pageNum;

    const pageContent = document.createElement('div');
    pageContent.className = 'page-content';
    pageContent.contentEditable = 'true';
    pageContent.innerHTML = content;

    // Eventos para detectar cambios y overflow
    pageContent.addEventListener('input', handlePageInput);
    pageContent.addEventListener('paste', handlePaste);
    pageContent.addEventListener('keydown', handleKeyDown);

    pageWrapper.appendChild(pageContent);
    container.appendChild(pageWrapper);

    pages.push({
        wrapper: pageWrapper,
        content: pageContent
    });

    return pageContent;
}

// ========================================
// MANEJO DE OVERFLOW AUTOMÁTICO
// ========================================
function handlePageInput(e) {
    unsavedChanges = true;
    updateWordCount();

    const pageContent = e.target;
    checkOverflow(pageContent);
}

function checkOverflow(pageContent) {
    const pageWrapper = pageContent.parentElement;
    const pageIndex = parseInt(pageWrapper.dataset.pageNumber) - 1;

    // Verificar si el contenido excede la altura permitida
    if (pageContent.scrollHeight > CONTENT_HEIGHT_PX) {
        // Contenido excede la página, necesitamos mover el exceso a la siguiente
        moveOverflowToNextPage(pageIndex);
    }
}

function moveOverflowToNextPage(pageIndex) {
    const currentPage = pages[pageIndex];
    const currentContent = currentPage.content;

    // Asegurar que existe la siguiente página
    let nextPage = pages[pageIndex + 1];
    if (!nextPage) {
        const container = document.getElementById('pages-container');
        const nextContent = createPage(container);
        nextPage = pages[pageIndex + 1];
    }

    // Algoritmo para mover contenido:
    // 1. Obtener todos los nodos hijos
    // 2. Medir altura acumulada
    // 3. Cuando exceda CONTENT_HEIGHT_PX, mover el resto a la siguiente página

    const children = Array.from(currentContent.childNodes);
    let accumulatedHeight = 0;
    let overflowNodes = [];

    for (let i = 0; i < children.length; i++) {
        const child = children[i];
        const childHeight = child.nodeType === Node.ELEMENT_NODE ? child.offsetHeight : 0;

        if (accumulatedHeight + childHeight > CONTENT_HEIGHT_PX && overflowNodes.length === 0) {
            // Este nodo y todos los siguientes van a la siguiente página
            overflowNodes = children.slice(i);
            break;
        }

        accumulatedHeight += childHeight;
    }

    // Mover nodos sobrantes a la siguiente página
    if (overflowNodes.length > 0) {
        const nextContent = nextPage.content;
        const existingContent = nextContent.innerHTML;

        // Mover nodos
        overflowNodes.forEach(node => {
            currentContent.removeChild(node);
            nextContent.insertBefore(node, nextContent.firstChild);
        });

        // Verificar si la siguiente página también se desborda
        setTimeout(() => checkOverflow(nextContent), 100);
    }
}

function handlePaste(e) {
    e.preventDefault();

    // Obtener texto plano del portapapeles
    const text = e.clipboardData.getData('text/plain');

    // Insertar como HTML sanitizado
    document.execCommand('insertHTML', false, text.replace(/\n/g, '<br>'));

    // Verificar overflow después del paste
    setTimeout(() => checkOverflow(e.target), 100);
}

function handleKeyDown(e) {
    // Manejar teclas especiales si es necesario
}

// ========================================
// TOOLBAR
// ========================================
function initToolbar() {
    // Botones de formato
    document.querySelectorAll('[data-command]').forEach(button => {
        button.addEventListener('click', function() {
            const command = this.dataset.command;
            document.execCommand(command, false, null);
            focusEditor();
        });
    });

    // Selector de formato de bloque
    const formatBlock = document.getElementById('formatBlock');
    if (formatBlock) {
        formatBlock.addEventListener('change', function() {
            document.execCommand('formatBlock', false, this.value);
            focusEditor();
        });
    }
}

function focusEditor() {
    // Enfocar la primera página visible
    if (pages.length > 0) {
        pages[0].content.focus();
    }
}

// ========================================
// OBTENER TODO EL CONTENIDO
// ========================================
function getAllContent() {
    let allContent = '';
    pages.forEach(page => {
        allContent += page.content.innerHTML;
    });
    return allContent;
}

// ========================================
// AUTOGUARDADO Y GUARDADO MANUAL
// ========================================
function initAutoSave() {
    autoSaveInterval = setInterval(() => {
        if (unsavedChanges) {
            saveReporte(true);
        }
    }, 15000);

    console.log('Autoguardado activado (cada 15 segundos)');
}

function saveReporte(isAutoSave = false) {
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

    // Contenido HTML de todas las páginas
    formData.append('contenido', getAllContent());
    formData.append('auto_save', isAutoSave ? '1' : '0');

    fetch('reportes-save.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
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
// FUNCIONES AUXILIARES
// ========================================
function initMetadataHidden() {
    if (currentAreaId && !currentReporteId) {
        createHiddenInputs();
        setTimeout(() => saveReporte(true), 1000);
    }
}

function createHiddenInputs() {
    const form = document.createElement('form');
    form.id = 'hidden-metadata-form';
    form.style.display = 'none';

    const campos = {
        titulo: 'Nuevo Reporte - ' + new Date().toLocaleDateString('es-GT'),
        area_id: currentAreaId,
        tipo_reporte: 'mensual',
        anio: new Date().getFullYear(),
        periodo_id: '',
        descripcion: ''
    };

    Object.entries(campos).forEach(([id, value]) => {
        const input = document.createElement(id === 'descripcion' ? 'textarea' : 'input');
        input.type = id === 'descripcion' ? undefined : 'hidden';
        input.id = id;
        input.value = value;
        if (id === 'descripcion') input.style.display = 'none';
        form.appendChild(input);
    });

    document.body.appendChild(form);
}

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

function updateWordCount() {
    const allText = getAllContent().replace(/<[^>]*>/g, '').trim();
    const words = allText.split(/\s+/).filter(word => word.length > 0).length;

    const wordCountEl = document.getElementById('word-count');
    if (wordCountEl) {
        wordCountEl.textContent = `${words} palabra${words !== 1 ? 's' : ''}`;
    }
}

function preventAccidentalExit() {
    window.addEventListener('beforeunload', function (e) {
        if (unsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
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
                didOpen: () => Swal.showLoading()
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
// CLEANUP
// ========================================
window.addEventListener('unload', function() {
    if (autoSaveInterval) {
        clearInterval(autoSaveInterval);
    }
});
