<div class="container-fluid">
    <!-- Page Heading -->
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-header">
                Agregar Producto
            </h1>            
        </div>
    </div>
    <!-- /.row -->
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box_registro">
                <h2 class="text-uppercase">Informacion del producto</h2>                                        
                <hr>
                <?php if ($this->session->flashdata('error')) { ?>
                    <div class="alert alert-danger"> 
                        <a class="close" data-dismiss="alert">×</a>
                        <?= $this->session->flashdata('error') ?> 
                    </div>
                <?php } ?>
                <?php echo form_open('panel_vendedor/producto/agregar', 'id="admin_producto_form"'); ?>                 
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">                            
                            <label>Nombre</label>
                            <input type="text" class="form-control" name="nombre">
                        </div>                    
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Descripcion</label>                    
                            <textarea class="form-control" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Imagen del Producto</label>                    
                            <input id="fileupload" type="file" name="files" data-url="<?php echo site_url('panel_vendedor/producto/upload_image') ?>">
                            <input type="hidden" name="file_name" id="file_name" value="">                                                            
                        </div> 
                    </div> 
                </div> 
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">                            
                            <label>Precio Venta Publico</label>
                            <input type="text" class="form-control" name="precio">
                        </div>
                    </div>                    
                    <div class="col-md-8">
                        <div class="form-group">                            
                            <label>Enlace externo al producto</label>
                            <input type="text" class="form-control" name="link_externo">
                        </div>
                    </div> 
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <br>
                            <label>Desea mostrar el producto al publico general?</label><br>
                            <label>Si<input type="radio" name="mostrar_producto" id="mostrar_si" value="1" checked class="radioInput"></label>
                            <label>No<input type="radio" name="mostrar_producto" id="mostrar_no" value="0" class="radioInput"></label>

                        </div>
                    </div>   
                    <div class="col-md-4">
                        <div class="form-group">
                            <br>
                            <label>Desea mostrar el precio al publico general?</label><br>
                            <label>Si<input type="radio" name="mostrar_precio" id="mostrar_precio_si" value="1" checked class="radioInput"></label>
                            <label>No<input type="radio" name="mostrar_precio" id="mostrar_precio_no" value="0" class="radioInput"></label>
                        </div>                                                                       
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <label>Seleccione una Categoria:</label>
                        <div class="alert alert-danger" id="seleccionar-categoria_alert" style="display:none;"> 
                            <a class="close" data-dismiss="alert">×</a>
                            Debe seleccionar una categoria.
                        </div>
                        <div id="categorias_jtree">                
                            <?php echo $categorias_tree_html; ?>
                        </div>
                    </div>
                </div>
            </div>


            <hr>                                
            <div class="text-center">
                <button type="submit" id="admin_producto_submit" class="btn btn-lg btn-primary"> Agregar</button>
            </div>
            <input type="hidden" name="categoria_id" value="">
            <input type="hidden" name="accion" value="producto-crear">
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<br>
<div id="throbber" style="display:none;">
    <img src="<?php echo assets_url('imgs/small-ajax-loader.gif'); ?>" />
</div>
</div>