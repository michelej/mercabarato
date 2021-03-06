<div class="container-fluid">
    <!-- Page Heading -->
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-header">
                Nueva Tarifa
            </h1>            
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box_registro">
                <h2 class="text-uppercase">Detalles de la Tarifa</h2>                                        
                <hr>                
                <?php echo form_open('panel_vendedor/tarifas/crear', 'id="detalles_tarifa" rel="preventDoubleSubmission"'); ?>                                 
                <input type="hidden" value="1" name="pagina" id="pagina"/>                                        
                <input type="hidden" value="1" name="pagina_tab2" id="pagina_tab2"/>                                        
                <div class="row">
                    <div class="col-xs-6 col-md-4">
                        <div class="form-group">
                            <label>Descuento</label>
                            <input type="text" class="form-control" name="valor">
                        </div>
                    </div>
                    <div class="col-xs-6 col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <select class="form-control" name="tipo">                                
                                <option value="porcentaje">( % )</option>
                                <?php if (!$mas_de_uno): ?>
                                    <option value="valor">( <?php echo $this->config->item("money_sign") ?> )</option>                                
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">                    
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Titulo</label>                    
                            <input type="text" class="form-control" name="nombre">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Descripcion</label>                    
                            <textarea class="form-control" name="descripcion" rows="10"></textarea>
                        </div>
                    </div> 
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Condiciones particulares ( Este texto sera visible a los clientes )</label>                    
                            <input type="text" class="form-control" name="condicion_particular">
                        </div>
                    </div>                 
                </div>
                <hr>
                <div class="text-center">
                    <button type="submit" id="admin_producto_submit" class="btn btn-lg btn-primary"> Crear Tarifa</button>
                </div>
                <input type="hidden" name="accion" value="item-crear">
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>Productos Seleccionados</strong>
                </div>
                <div class="panel-body">
                    <div id="tabla-resultados-productos"></div>
                </div>
            </div>
        </div>        
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>Clientes Seleccionados</strong>
                </div>
                <div class="panel-body">                            
                    <div id="tabla-resultados-clientes"></div>
                </div>
            </div>
        </div>
    </div>

    
</div>

