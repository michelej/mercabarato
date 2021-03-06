<?php if (sizeof($paquetes)==0): ?>
<div>
    <p> No se encontraron resultados...</p>    
</div>
<?php else: ?>

<div class="table-responsive">
    <table class="table table-bordered table-hover table-striped">
        <thead>
            <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 15%">Nombre</th>                
                <th style="width: 15%">Descripcion</th>                                
                <th style="width: 10%;">Duracion</th>
                <th style="width: 5%;">Precio</th>
                <th style="width: 10%;text-align: center">Productos</th>
                <th style="width: 10%;text-align: center">Anuncios</th>                
                <th style="width: 5%;text-align: center">Orden</th>
                <th style="width: 5%;text-align: center">Activo</th>                
                <th style="width: 5%;text-align: center">Mostrar</th>                
                <th style="width: 5%;text-align: center">&nbsp; Acciones</th> 
            </tr>
        </thead>
        <tbody>
            <?php foreach ($paquetes as $paquete): ?>
                <tr>
                    <td><?php echo $paquete->id; ?></td>
                    <td><?php echo $paquete->nombre; ?></td>                    
                    <td><?php echo $paquete->descripcion; ?></td>                                                            
                    <td><?php echo $paquete->duracion.' Meses' ?></td>                    
                    <td><?php echo $paquete->costo; ?></td>                    
                    <td style="text-align: center"><?php
                        if ($paquete->limite_productos == 0): echo "<span class='label label-danger'>No disponible</span>";
                        elseif ($paquete->limite_productos == -1): echo "<span class='label label-success'>Sin limite</span>";
                        else: echo $paquete->limite_productos;
                        endif;
                        ?>
                    </td>
                    <td style="text-align: center"><?php
                        if ($paquete->limite_anuncios == 0): echo "<span class='label label-danger'>No disponible</span>";
                        elseif ($paquete->limite_anuncios == -1): echo "<span class='label label-success'>Sin limite</span>";
                        else: echo $paquete->limite_anuncios;
                        endif;
                        ?>
                    </td>                    
                    <td><?php echo $paquete->orden; ?></td>                    
                    <td style="text-align: center"><?php
                        if ($paquete->activo == 1): echo "<span class='label label-success'>Si</span>";
                        else: echo "<span class='label label-danger'>No</span>";
                        endif;
                        ?>
                    </td>
                    <td style="text-align: center"><?php
                        if ($paquete->mostrar == 1): echo "<span class='label label-success'>Si</span>";
                        else: echo "<span class='label label-danger'>No</span>";
                        endif;
                        ?>
                    </td>
                    <td>
                        <div class="options">                            
                            <a class="item_borrar" href="<?php echo site_url('admin/paquetes/borrar') . '/' . $paquete->id ?>" data-toogle="tooltip"  title="Eliminar"><i class="glyphicon glyphicon-trash"></i></a>
                        </div>                           
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php echo $pagination; ?>
</div> 
<?php endif; ?>
