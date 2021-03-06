<?php if (sizeof($anuncios) == 0): ?>
    <div>        
        <div class="alert alert-warning">             
            <p> No se encontro ningun anuncio que se ajuste a estos parametros, puedes intentar lo siguiente: </p>    
            <ul>                
                <li> Prueba con un nombre o palabras claves diferentes.</li>
            </ul>
        </div>        
    </div>
<?php else: ?>
    <?php if ($this->session->flashdata('success')) { ?>
        <div class="alert alert-success"> 
            <a class="close" data-dismiss="alert">×</a>
            <i class="fa fa-info-circle"></i> <?= $this->session->flashdata('success') ?> 
        </div>
    <?php } ?>      
    <?php if ($this->session->flashdata('error')) { ?>
        <div class="alert alert-danger"> 
            <a class="close" data-dismiss="alert">×</a>
            <i class="fa fa-warning"></i> <?= $this->session->flashdata('error') ?> 
        </div>
    <?php } ?> 
    <?php if ($ilimitado): ?>
        <div class="alert alert-info">                 
            <p> Puedes publicar anuncios sin limites</p>
        </div>
    <?php else: ?>

        <div class="alert alert-info">                 
            <?php $diff = $limite_anuncios - $anuncios_total; ?>
            <?php if ($diff < 0): ?>
                <p> Tienes un exceso de <?php echo $diff * -1 ?> anuncio(s) de un maximo de <?php echo $limite_anuncios ?> anuncio(s).</p>
            <?php else: ?>
                <p> Puedes publicar <?php echo $diff ?> anuncio(s) mas de un maximo de <?php echo $limite_anuncios ?> anuncio(s).</p>
            <?php endif; ?>                
        </div>                
    <?php endif; ?>

    <div class="table-responsive">        
        <table class="table table-bordered table-hover table-striped">
            <thead>
                <tr>   
                    <th style="width: 1%"><input type="checkbox" name="select_all" value="ON" /></th>              
                    <th style="width: 14%">Titulo</th>                
                    <th style="width: 40%">Contenido</th>                                
                    <th style="width: 5%;text-align: center">Habilitado</th>
                    <th style="width: 10%">&nbsp; Acciones</th> 
                </tr>
            </thead>
            <tbody>
                <?php foreach ($anuncios as $anuncio): ?>
                    <tr data-id="<?php echo $anuncio->id; ?>">
                        <td><input type="checkbox" name="eliminar" value="ON"/></td>
                        <td><?php echo $anuncio->titulo; ?></td>                    
                        <td><?php echo strip_tags(truncate($anuncio->contenido, 300)); ?></td>                                        
                        <td style="text-align: center"><?php
                            if ($anuncio->habilitado == 1): echo "<span class='label label-success'>Si</span>";
                            else: echo "<span class='label label-danger'>No</span>";
                            endif;
                            ?>
                        </td>                                                                
                        <td>
                            <div class="options">
                                <?php if ($anuncio->habilitado == 0): ?>
                                    <a class="row_action habilitar" href="<?php echo site_url('panel_vendedor/anuncio/habilitar') . '/' . $anuncio->id ?>" data-toogle="tooltip"  title="Habilitar"><i class="glyphicon glyphicon-ok"></i></a>
                                <?php else: ?>
                                    <a class="row_action inhabilitar" href="<?php echo site_url('panel_vendedor/anuncio/inhabilitar') . '/' . $anuncio->id ?>" data-toogle="tooltip"  title="Inhabilitar"><i class="glyphicon glyphicon-remove"></i></a>
                                <?php endif; ?>
                                <a href="<?php echo site_url('panel_vendedor/anuncio/editar') . '/' . $anuncio->id ?>" data-toogle="tooltip"  title="Modificar"><i class="glyphicon glyphicon-edit"></i></a>
                                <a class="row_action borrar" href="<?php echo site_url('panel_vendedor/anuncio/borrar') . '/' . $anuncio->id ?>" data-toogle="tooltip"  title="Eliminar"><i class="glyphicon glyphicon-trash"></i></a>
                            </div>                           
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php echo $pagination; ?>        
    </div> 
    <button type="button" id="btn-eliminar-seleccionados" class="btn btn-danger">Eliminar Seleccionados</button>
<?php endif; ?>
