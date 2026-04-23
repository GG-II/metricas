/**
 * Sistema de Exportación
 */

const ExportModule = {
    /**
     * Exporta métricas seleccionadas
     */
    exportMetricas(metricas, formato = 'csv', opciones = {}) {
        if (!metricas || metricas.length === 0) {
            alert('Selecciona al menos una métrica para exportar');
            return;
        }

        const params = new URLSearchParams({
            formato: formato,
            metricas: metricas.join(','),
            periodos: opciones.periodos || 12
        });

        if (opciones.area_id) {
            params.append('area_id', opciones.area_id);
        }

        const url = `/metricas/public/admin/export.php?${params.toString()}`;

        if (formato === 'html') {
            // Abrir en ventana nueva para imprimir
            window.open(url, '_blank');
        } else {
            // Descargar archivo
            window.location.href = url;
        }
    },

    /**
     * Exporta dashboard actual (todos los gráficos visibles)
     */
    exportDashboard(formato = 'html') {
        // Obtener todas las métricas visibles en el dashboard
        const metricasSet = new Set();
        document.querySelectorAll('[data-metrica-id]').forEach(el => {
            const ids = el.getAttribute('data-metrica-id');
            if (ids) {
                // Puede ser una ID o varias separadas por comas
                ids.split(',').forEach(id => {
                    const trimmedId = id.trim();
                    if (trimmedId) {
                        metricasSet.add(trimmedId);
                    }
                });
            }
        });

        const metricas = Array.from(metricasSet);

        if (metricas.length === 0) {
            alert('No hay métricas visibles en el dashboard');
            return;
        }

        this.exportMetricas(metricas, formato);
    },

    /**
     * Muestra modal de opciones de exportación
     */
    showExportModal(metricasPreseleccionadas = []) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'exportModal';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Exportar Datos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Formato</label>
                            <div class="form-selectgroup">
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="formato" value="csv" class="form-selectgroup-input" checked>
                                    <span class="form-selectgroup-label">
                                        <i class="ti ti-table me-1"></i>
                                        Excel (CSV)
                                    </span>
                                </label>
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="formato" value="html" class="form-selectgroup-input">
                                    <span class="form-selectgroup-label">
                                        <i class="ti ti-file-text me-1"></i>
                                        PDF (Imprimible)
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Períodos a incluir</label>
                            <select class="form-select" name="periodos">
                                <option value="6">Últimos 6 meses</option>
                                <option value="12" selected>Últimos 12 meses</option>
                                <option value="24">Últimos 24 meses</option>
                                <option value="36">Últimos 36 meses</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Métricas seleccionadas: <strong id="selectedCount">${metricasPreseleccionadas.length}</strong>
                            </label>
                            <div class="form-hint">
                                ${metricasPreseleccionadas.length > 0
                                    ? 'Se exportarán las métricas seleccionadas'
                                    : 'Se exportarán todas las métricas visibles en el dashboard'}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btnExportar">
                            <i class="ti ti-download me-1"></i>
                            Exportar
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        // Limpiar al cerrar
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });

        // Exportar al hacer clic
        document.getElementById('btnExportar').addEventListener('click', () => {
            const formato = modal.querySelector('input[name="formato"]:checked').value;
            const periodos = parseInt(modal.querySelector('select[name="periodos"]').value);

            if (metricasPreseleccionadas.length > 0) {
                this.exportMetricas(metricasPreseleccionadas, formato, { periodos });
            } else {
                this.exportDashboard(formato);
            }

            bsModal.hide();
        });
    }
};

// Exportar globalmente
window.ExportModule = ExportModule;
