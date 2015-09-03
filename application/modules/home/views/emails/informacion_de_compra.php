<table border="0" cellpadding="0" cellspacing="0" width="100%">	
    <tr>
        <td style="padding: 10px 0 30px 0;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc; border-collapse: collapse;">
                <tr>
                    <td align="center" bgcolor="#d99327" style="padding: 40px 0 30px 0; color: #153643; font-size: 28px; font-weight: bold; font-family: Arial, sans-serif;">
                        <img src="<?php echo assets_url('imgs/logo.png') ?>" alt="Mercabarato.com" width="340" height="76" style="display: block;" />
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">
                                    <b>Informacion de compra de paquete</b>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    A continuacion se muestra los datos.
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td width="40%" valign="top" style="text-align: right;color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                <strong>Paquete</strong>
                                            </td>  
                                            <td width="10%" style="font-size: 0; line-height: 0;" width="20">
                                                &nbsp;
                                            </td>
                                            <td width="40%" valign="top" style="color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                <?php echo $paquete["nombre_paquete"] ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="40%" valign="top" style="text-align: right;color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                <strong>Comprado el</strong>
                                            </td>  
                                            <td style="font-size: 0; line-height: 0;" width="20">
                                                &nbsp;
                                            </td>
                                            <td width="40%" valign="top" style="color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                <?php echo date("d-m-Y", strtotime($paquete["fecha_comprado"])) ?>
                                            </td>
                                        </tr>                                        
                                        <tr>
                                            <td width="40%" valign="top" style="text-align: right;color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                <strong>Monto a cancelar</strong>
                                            </td>  
                                            <td style="font-size: 0; line-height: 0;" width="20">
                                                &nbsp;
                                            </td>
                                            <td width="40%" valign="top" style="color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                <?php echo $paquete["monto_a_cancelar"] ?> &euro;
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>                            
                            <tr>
                                <td style="padding: 40px 0 5px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    A continuacion los datos para realizar el pago: 
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0 5px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    <p><strong>Cuenta:</strong>  ES51 2100 2798 7501 0008 6475</p>                                    
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0 15px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                    Envia un correo una vez hecho el pago que incluya:
                                    <ul>
                                        <li>Nombre de la empresa</li>
                                        <li>El paquete comprado</li>
                                        <li>El email con el que estas registrado en el sitio</li>
                                    </ul>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#333" style="padding: 30px 30px 30px 30px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="color: #ffffff; text-align: center; font-family: Arial, sans-serif; font-size: 14px;" width="100%">
                                    Si deseas darte de baja puedes darle click al siguiente link <a href="<?php echo site_url('usuario/eliminar-cuenta')?>" style="color: #ffffff; font-size: 16px;"> BAJA </a> <br><br>
                                    Copyright &copy; 2015. Mercabarato.com Todos los derechos reservados.                                    
                                </td>                                
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>