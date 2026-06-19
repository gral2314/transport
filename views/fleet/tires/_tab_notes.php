<!-- Tab: Notas -->
<div class="col-12">
    <div class="form-group">
        <label for="tire[notes]" class="form-label">
            <i class="fa-solid fa-note-sticky me-1"></i>
            <b>Notas y Observaciones</b>
        </label>
        <textarea 
            class="form-control" 
            name="tire[notes]" 
            id="tire[notes]" 
            rows="12" 
            placeholder="Ingrese observaciones, historial de reparaciones, condiciones especiales, etc."></textarea>
        <small class="form-text text-muted">
            <i class="fa-solid fa-lightbulb me-1"></i>
            Use este espacio para documentar:
            <ul class="mb-0 mt-1">
                <li>Historial de reparaciones y reencauches</li>
                <li>Condiciones especiales de uso o almacenamiento</li>
                <li>Observaciones sobre desgaste irregular</li>
                <li>Registros de inspecciones visuales</li>
                <li>Motivos de baja o desecho</li>
            </ul>
        </small>
    </div>
</div>

<div class="col-12 mt-3">
    <div class="card">
        <div class="card-header bg-light p-2">
            <h6 class="mb-0"><i class="fa-solid fa-clipboard-list me-1"></i> Plantillas Rápidas</h6>
        </div>
        <div class="card-body p-2">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary" data-template="nueva">
                    <i class="fa-solid fa-sparkles"></i> Nueva
                </button>
                <button type="button" class="btn btn-outline-success" data-template="buena">
                    <i class="fa-solid fa-circle-check"></i> Buena condición
                </button>
                <button type="button" class="btn btn-outline-warning" data-template="desgaste">
                    <i class="fa-solid fa-triangle-exclamation"></i> Desgaste
                </button>
                <button type="button" class="btn btn-outline-danger" data-template="danada">
                    <i class="fa-solid fa-circle-xmark"></i> Dañada
                </button>
                <button type="button" class="btn btn-outline-info" data-template="reparacion">
                    <i class="fa-solid fa-wrench"></i> Reparación
                </button>
                <button type="button" class="btn btn-outline-secondary" data-template="reencauche">
                    <i class="fa-solid fa-recycle"></i> Reencauche
                </button>
            </div>
            <small class="text-muted d-block mt-2">
                <i class="fa-solid fa-info-circle me-1"></i>
                Las plantillas insertan texto de ejemplo que puede modificar según necesite.
            </small>
        </div>
    </div>
</div>

<script>
// Plantillas de notas
document.addEventListener('DOMContentLoaded', function() {
    const templates = {
        nueva: `[${new Date().toLocaleDateString('es-MX')}] Llanta nueva recibida.
- Condición: Nueva sin uso
- Inspección visual: APROBADA
- Sin defectos de fabricación`,
        
        buena: `[${new Date().toLocaleDateString('es-MX')}] Inspección realizada.
- Condición física: Buena
- Desgaste uniforme: SÍ
- Presión verificada: OK
- Sin daños visibles`,
        
        desgaste: `[${new Date().toLocaleDateString('es-MX')}] Desgaste detectado.
- Tipo de desgaste: [Uniforme/Irregular/Lateral]
- Profundidad actual: [X.XX] mm
- Acción recomendada: [Continuar uso/Reemplazo próximo/Reemplazo inmediato]`,
        
        danada: `[${new Date().toLocaleDateString('es-MX')}] Daño detectado.
- Tipo de daño: [Pinchazo/Corte/Reventón/Deformación]
- Ubicación: [Banda de rodadura/Pared lateral/Talón]
- Estado: FUERA DE SERVICIO
- Acción: [Reparación/Desecho]`,
        
        reparacion: `[${new Date().toLocaleDateString('es-MX')}] Reparación realizada.
- Tipo de reparación: [Pinchazo/Parche interno/Vulcanizado]
- Proveedor: [Nombre taller]
- Costo: $[XXX.XX]
- Garantía: [Días/Sin garantía]
- Estado post-reparación: [Aprobada/Rechazada]`,
        
        reencauche: `[${new Date().toLocaleDateString('es-MX')}] Reencauche realizado.
- No. de reencauche: [1/2/3]
- Proveedor: [Nombre taller]
- Tipo de banda: [Diseño]
- Costo: $[XXX.XX]
- Prof. nueva banda: [XX.XX] mm
- Garantía: [Km/Sin garantía]`
    };
    
    document.querySelectorAll('[data-template]').forEach(btn => {
        btn.addEventListener('click', function() {
            const template = this.getAttribute('data-template');
            const textarea = document.getElementById('tire[notes]');
            const currentText = textarea.value.trim();
            const newText = templates[template];
            
            textarea.value = currentText 
                ? currentText + '\n\n' + newText 
                : newText;
        });
    });
});
</script>
