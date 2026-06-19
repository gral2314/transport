<div class="modal fade" id="mdl-group">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title mdlGroupTxt">Nuevo Grupo</h4>
                <button type="button" class="close btnNew" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                <div class="row">
                        <div class="col-sm-2">
                            <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
                                <a class="nav-link active" id="vert-tabs-group-tab" data-toggle="pill" href="#vert-tabs-group" role="tab" aria-controls="vert-tabs-group" aria-selected="true">General</a>
                                <a class="nav-link" id="vert-tabs-per-group-tab" data-toggle="pill" href="#vert-tabs-per-group" role="tab" aria-controls="vert-tabs-per-group" aria-selected="false">Permisos</a>
                            </div>
                        </div>
                        <div class="col-sm-10" style="overflow-x:none;">
                            <div class="tab-content" id="vert-tabs-tabContent">
                                <div class="tab-pane text-left fade show active" id="vert-tabs-group" role="tabpanel" aria-labelledby="vert-tabs-group-tab" style="height: 410px; overflow-y: auto; overflow-x:hidden;">
                                <div class="row">
                                <div class="col-md-12">
                                    <div class="col-sm-12 form-group row m-0">
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa-solid fa-people-line"></i></span>
                                            </div>
                                            <input type="text" class="form-control form-control-sm" id="mdl-GroupName" placeholder="Nombre">
                                        </div>
                                    </div>
                                    <div class="col-sm-12 form-group row m-0">
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-book"></i></span>
                                            </div>
                                            <textarea class="form-control form-control-sm" id="mdl-GroupDescrption" cols="100%" rows="3" placeholder="Descripción"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                </div>
                                <div class="tab-pane fade" id="vert-tabs-per-group" role="tabpanel" aria-labelledby="vert-tabs-per-group-tab" style="height: 410px; overflow-y: auto; overflow-x:hidden;">
                                    <div class="col-md-12 row">
                                        <?php 
                            $idMenu=0;
                            foreach ($menujson as $menu) {
                                if($menu->M_type == 'S'){ ?>
                                        <div class="col-md-3 mt-3">
                                            <div class="mb-1">
                                                <div class="form-check">
                                                    <input class="form-check-input option-gruoup-menu" type="checkbox" value="1" id="ModuleG-<?= $menu->M_id?>">
                                                    <label class="form-check-label" for="checkbox1">
                                                        <i class="<?= $menu->M_icon;?> mr-1"></i>
                                                        <?= $menu->M_name?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                            }
                            if($menu->M_type == 'M'){
                                if($idMenu != 0 && $idMenu != $menu->M_id){?>
                                    </div>
                                    <?php }
                                if($idMenu == 0 || $idMenu != $menu->M_id){?>
                                    <div class="col-md-3 mt-3">
                                        <div class="mb-1">
                                            <h6>
                                                <div class="form-check">
                                                    <input class="form-check-input option-gruoup-menu" type="checkbox" value="1" id="ModuleG-<?= $menu->M_id?>">
                                                    <label class="form-check-label" for="checkbox1">
                                                        <i class="<?= $menu->M_icon?> mr-1"></i>
                                                        <?= $menu->M_name?>
                                                    </label>
                                                </div>
                                            </h6>
                                        </div>

                                        <?php
                                    $idMenu = $menu->M_id;
                                }
                                if($idMenu == $menu->M_id){?>
                                        <div class="mb-1 ml-3">
                                            <div class="form-check">
                                                <input class="form-check-input option-gruoup-menu" type="checkbox" value="1" id="ModuleG-<?= $menu->S_id?>">
                                                <label class="form-check-label" for="checkbox1">
                                                    <i class="<?= $menu->S_icon?> mr-1"></i>
                                                    <?= $menu->S_name?>
                                                </label>
                                            </div>
                                        </div>
                                        <?php
                                }
                            }
                            }?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-danger" id="mdl-group-btnclose" data-dismiss="modal"><i class="fa-solid fa-ban"></i> Cancelar</button>
                <button type="button" class="btn btn-outline-success" id="mdl-group-btnsave" onclick="savegroup()"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>