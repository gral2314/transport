<?php
use yii\helpers\Url;
?>

<div class="modal fade" id="mdl-users">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title mdlUserTxt">Nuevo Usuario</h4>
                <button type="button" class="close btnNew" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-2">
                            <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
                                <a class="nav-link active" id="vert-tabs-users-tab" data-toggle="pill" href="#vert-tabs-users" role="tab" aria-controls="vert-tabs-users" aria-selected="true">General</a>
                                <a class="nav-link" id="vert-tabs-permition-tab" data-toggle="pill" href="#vert-tabs-permition" role="tab" aria-controls="vert-tabs-permition" aria-selected="false">Permisos</a>
                            </div>
                        </div>
                        <div class="col-sm-10" style="overflow-y:auto">
                            <div class="tab-content" id="vert-tabs-tabContent">
                                <div class="tab-pane text-left fade show active" id="vert-tabs-users" role="tabpanel" aria-labelledby="vert-tabs-users-tab">
                                    <div class="col-sm-12 row m-0" style="height: 410px;">
                                        <div class="col-sm-5 text-center m-0 p-0">
                                            <img src="<?= Url::home()?>assets/img/prof_users/av1.jpg" class="product-image profile-user-img img-fluid img-circle" alt="Product Image" style="max-height:150px; max-width: 100%; object-fit: contain;">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="inputFile" onclick="unselect_image()" accept="image/*" style="display: none;">
                                                <label class="btn btn-outline-info btn-block" for="inputFile">
                                                    <i class="fa-solid fa-upload"></i> Subir Foto
                                                </label>
                                            </div>
                                            <div class="col-12 product-image-thumbs m-0" style="height: 200px; overflow-y: auto;">
                                                <div class="row mt-0">
                                                    <div class="product-image-thumb active selected-image m-0"><img src="<?= Url::home()?>assets/img/prof_users/av1.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av2.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av3.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av4.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av5.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av6.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av7.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av8.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av9.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av10.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av11.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av12.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av13.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av14.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av15.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av16.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av17.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av18.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av19.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av20.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av21.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av22.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av23.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                    <div class="product-image-thumb m-0"><img src="<?= Url::home()?>assets/img/prof_users/av24.jpg" alt="Product Image" style="max-height:50px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-7 form-group mt-3">
                                            <div class="input-group row mb-3 ml-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm" id="mdl-UserName" placeholder="Nombre">
                                            </div>
                                            <div class="input-group row mb-3 ml-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fa-solid fa-qrcode"></i></span>
                                                </div>
                                                <input required disable="true" type="text" class="form-control form-control-sm" id="mdl-UserCode" value="" placeholder="Codigo del usuario">
                                            </div>
                                            <div class="input-group row mb-3 ml-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                                </div>
                                                <input required type="password" class="form-control form-control-sm" id="mdl-UserPass" value="" placeholder="Contraseña">
                                            </div>
                                            <div class="input-group row mb-3 ml-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
                                                </div>
                                                <input required type="text" class="form-control form-control-sm" id="mdl-codeemploye" value="" placeholder="No. Empleado SAP">
                                            </div>
                                            <div class="input-group row mb-3 ml-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                                </div>
                                                <input required type="text" class="form-control form-control-sm" id="mdl-UserEmail" value="" placeholder="Email del usuario">
                                            </div>
                                            <div class="input-group row mb-3 ml-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                                </div>
                                                <input required type="text" class="form-control form-control-sm" id="mdl-UserPhone" value="" placeholder="Telefono del usuario">
                                            </div>
                                            <div class="input-group row mb-3 ml-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fa-solid fa-user-group"></i></span>
                                                </div>
                                                <select required class="form-control form-control-sm" id="mdl-selUserGroup" value="" onchange="changeGroupToUser(this)">
                                                    <option value="-1" disabled selected hidden>Grupo del usuario</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-sm-12 m-0 ml-3">
                                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="checkbox" class="custom-control-input" id="mdl-UserActivo">
                                                    <label class="custom-control-label" for="mdl-UserActivo">Activo</label>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-12 m-0 ml-3">
                                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="checkbox" class="custom-control-input" id="mdl-can-create-reports">
                                                    <label class="custom-control-label" for="mdl-can-create-reports">Crear reportes de equipo para otros usuarios</label>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-12 m-0 ml-3">
                                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="checkbox" class="custom-control-input" id="mdl-can-open-nominaauth">
                                                    <label class="custom-control-label" for="mdl-can-open-nominaauth">Abrir Nómina Autorizada</label>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-12 m-0 ml-3">
                                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="checkbox" class="custom-control-input" id="mdl-can-modify-horomertro">
                                                    <label class="custom-control-label" for="mdl-can-modify-horomertro">Modificar Horomertro en Equipos</label>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-12 m-0 ml-3">
                                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="checkbox" class="custom-control-input" id="mdl-can-editprj">
                                                    <label class="custom-control-label" for="mdl-can-editprj">Puede editar el proyecto</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="vert-tabs-permition" role="tabpanel" aria-labelledby="vert-tabs-permition-tab">
                                    <div class="col-md-12 row" style="height: 410px; overflow-y: auto;">
                                        <?php 
                                        $idMenu=0;
                                        foreach ($menujson as $menu) {
                                            if($menu->M_type == 'S'){ ?>
                                        <div class="col-md-3 mt-3">
                                            <div class="mb-1">
                                                <div class="form-check">
                                                    <input class="form-check-input option-user-menu" type="checkbox" value="1" id="Module-<?= $menu->M_id?>">
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
                                                    <input class="form-check-input option-user-menu" type="checkbox" value="1" id="Module-<?= $menu->M_id?>">
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
                                                <input class="form-check-input option-user-menu" type="checkbox" value="1" id="Module-<?= $menu->S_id?>">
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
        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-outline-danger" id="mdl-user-btnclose" data-dismiss="modal"><i class="fa-solid fa-ban"></i> Cancelar</button>
            <!--<button type="submit" class="btn btn-success" name="btn_mailpassword" id="btn_mailpassword">Send Mail Password</button>-->
            <button type="button" class="btn btn-outline-success" id="mdl-user-btnsave" onclick="saveuser()"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
        </div>
    </div>
</div>
</div>