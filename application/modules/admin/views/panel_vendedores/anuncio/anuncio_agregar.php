<div class="container-fluid">
    <!-- Page Heading -->
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-header">
                Agregar Anuncio
            </h1>            
        </div>
    </div>
    <!-- /.row -->
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box_registro">
                <h2 class="text-uppercase">Informacion del Anuncio</h2>                                        
                <hr>
                <?php if ($this->session->flashdata('error')) { ?>
                    <div class="alert alert-danger"> 
                        <a class="close" data-dismiss="alert">×</a>
                        <?= $this->session->flashdata('error') ?> 
                    </div>
                <?php } ?>
                <?php echo form_open('panel_vendedor/anuncio/agregar', 'id="admin_crear_form"'); ?>                 
                <div class="form-group">
                    <label>Titulo</label>
                    <input type="text" class="form-control" name="titulo">
                </div>
                <div class="form-group">
                    <label>Contenido</label>                    
                    <textarea class="form-control" name="contenido" rows="10"></textarea>
                </div>                
                <!--<div class="form-group">
                    <label>Imagen del Producto</label>                    
                    <input id="fileupload" type="file" name="files" data-url="<?php echo site_url('admin/producto_resource/upload_image') ?>">
                    <input type="hidden" name="file_name" id="file_name" value="">                                                            
                </div>               -->
                                
                <hr>
                <div class="text-center">
                    <button type="submit" id="admin_producto_submit" class="btn btn-lg btn-primary"> Publicar</button>
                </div>
                <input type="hidden" name="accion" value="item-crear">
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
    <br>

</div>