<div class="container-fluid">
    <!-- Page Heading -->
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-header">
                Ofertas
            </h1>            
        </div>
    </div>      
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>Mis Ofertas</strong>
                </div>
                <?php echo form_open('', 'id="listado-items"'); ?>
                <input type="hidden" value="1" name="pagina" id="pagina"/>
                <?php echo form_close(); ?>
                <div class="panel-body">
                    <div id="tabla-resultados-left"></div>
                </div>
            </div>
        </div>        
    </div>           
    <div id="question" style="display:none; cursor: default">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Esta seguro que deseas eliminar esta Oferta?.</h4>
            </div>
            <div class="modal-body">                                    
                <p class="text-center">
                    <button class="btn btn-success" type="button" id="yes"><i class="fa fa-check"></i> Si</button>
                    <button class="btn btn-danger" type="button" id="no"><i class="fa fa-close"></i> No</button>
                </p>                                            
            </div>        
        </div>
    </div> 
</div>    