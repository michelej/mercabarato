<div id="heading-breadcrumbs">
    <div class="container">
        <div class="row">
            <div class="col-md-7">
                <h1>Infocompras - Seguros</h1>
            </div>
            <div class='col-md-5 text-right'>
                <img class="infocompras-seguros-icono" src="<?php echo assets_url('imgs/icono-infocomp-seguros.png')?>"/>
            </div>
        </div>
    </div>
</div>

<div id="content" class="clearfix">
    <div class="container">
        <div class="col-md-8 col-md-offset-2">
            <div class="row">
                <div class="col-md-12">
                    <p class="lead">A continuación puede filtrar por provincia y población para enviar el presupuesto a los proveedores que coincidan con sus criterios de búsqueda.</p>
                </div>
                <?php echo form_open('', 'id="form_buscar"'); ?>                
                    <input type="hidden" name="pagina" id="pagina" value="1">                    
                    <div class="col-md-6">
                        <div class="form-group">                                
                            <select name="provincia" class="form-control">
                                <option value="0">Todas las provincias</option>
                                <?php foreach ($provincias as $provincia): ?>
                                    <option value="<?php echo $provincia->id ?>"><?php echo $provincia->nombre ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">                        
                            <select name="poblacion" class="form-control">
                                <option value="0">Todas las poblaciones</option>                        
                            </select>
                        </div>
                    </div>
                <?php echo form_close(); ?>
            </div>
            <div class="col-md-12">
                <div class="row">                    
                    <div class="col-md-3 pull-right">
                        <div class="pull-right terminar-btn" <?php echo (!$hide_terminar) ? "style='display:none;'" : ""; ?>>                        
                            <a href="<?php echo site_url('seguros/finalizar') ?>" class="btn btn-template-primary" id="terminar-seguros"> Terminar</a>
                        </div>                
                    </div>
                    <div class="col-md-3 pull-right">
                        <div class="pull-right enviar-todos-btn" style="display:none;">                        
                            <a href="" class="btn btn-template-primary" id="enviar-todos"> Enviar a Todos</a>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <hr>
            <div class="col-md-12">
                <div class="row">
                    <div id="tabla-resultados"></div>
                </div>
            </div>            
        </div>
    </div>
    <div id="throbber" style="display:none;">
        <img src="<?php echo assets_url('imgs/loader_on_white_nb_big.gif'); ?>" alt="Espere un momento."/>
    </div>
</div>

