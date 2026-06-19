<?php
/** @var yii\web\View $this */
?>
<!-- Modal: Detalle de orden de taller -->
<div class="modal fade" id="mdl-taller-movement" tabindex="-1" role="dialog"
     aria-labelledby="mdl-taller-movement-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light p-2">
                <h6 class="modal-title mb-0" id="mdl-taller-movement-title"> <i class="fa-solid fa-info-circle me-2"></i> Detalle de orden</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button> -->
                
            </div>
            <div class="modal-body">
                <!-- Cabecera -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Folio:</strong> <span id="mov-docnum"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Estado:</strong>
                        <span id="mov-status-badge" class="badge"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Prioridad:</strong> <span id="mov-priority"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Fecha:</strong> <span id="mov-doc-date"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Técnico:</strong> <span id="mov-technician"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Origen:</strong> <span id="mov-origin"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Comentarios:</strong>
                        <p id="mov-comments" class="text-muted small"></p>
                    </div>
                </div>

                <!-- Timeline de trazabilidad -->
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h6 class="card-title"><i class="fa-solid fa-clock"></i> Trazabilidad</h6>
                    </div>
                    <div class="card-body">
                        <div class="row small">
                            <div class="col-md-4"><strong>Liberado:</strong> <span id="mov-released-at">-</span></div>
                            <div class="col-md-4"><strong>Iniciado:</strong> <span id="mov-started-at">-</span></div>
                            <div class="col-md-4"><strong>Finalizado:</strong> <span id="mov-completed-at">-</span></div>
                            <div class="col-md-4 mt-2"><strong>Validado:</strong> <span id="mov-validated-at">-</span></div>
                            <div class="col-md-4 mt-2"><strong>Cancelado:</strong> <span id="mov-cancelled-at">-</span></div>
                        </div>
                    </div>
                </div>

                <!-- Unidades -->
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h6 class="card-title"><i class="fa-solid fa-truck"></i> Unidades</h6>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-sm table-striped mb-0" id="tbl-mov-vehicles">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Unidad</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Detalle (llantas) -->
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h6 class="card-title"><i class="fa-solid fa-list"></i> Detalle</h6>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-sm table-striped mb-0" id="tbl-mov-details">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Llanta</th>
                                    <th>Tipo</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-times"></i> Cerrar
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-danger d-none" id="btn-mov-cancel">
                        <i class="fa-solid fa-ban"></i> Cancelar
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-primary d-none" id="btn-mov-start">
                        <i class="fa-solid fa-play"></i> Iniciar
                    </button>
                    <button type="button" class="btn btn-sm btn-warning d-none" id="btn-mov-finish">
                        <i class="fa-solid fa-check"></i> Finalizar
                    </button>
                    <button type="button" class="btn btn-sm btn-success d-none" id="btn-mov-validate">
                        <i class="fa-solid fa-check-double"></i> Validar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
