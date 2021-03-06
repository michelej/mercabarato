$(document).ready(function() {
    updateResultadosProductos();
    updateResultadosClientes();
    
    $("#detalles_tarifa").validate({        
        rules: {            
            valor: {required: true,number:true},            
            nombre: {required:true}
        },
        messages: {            
            valor: {required: "Este campo es necesario.",number:"Este campo tiene que ser un numero"},            
            nombre: {required:" Indique un nombre para identificar la tarifa."}
        }
    });
});

function updateResultadosProductos() {            
    $('#tabla-resultados-productos').html('<br><br><br>');
    $('#tabla-resultados-productos').block({
        message: '<h4>Procesando espere un momento..</h4>',
        css: {border: '3px solid #a00'}
    });
    $.ajax({
        type: "POST",
        url: SITE_URL + 'panel_vendedor/tarifas/ajax_get_productos',
        data: {
            pagina: $('#pagina').val()
        },
        dataType: "html",
        success: function(response) {
            $('#tabla-resultados-productos').unblock();
            $('#tabla-resultados-productos').html(response);
            bind_pagination_links();
        }
    });
}

function updateResultadosClientes() {            
    $('#tabla-resultados-clientes').html('<br><br><br>');
    $('#tabla-resultados-clientes').block({
        message: '<h4>Procesando espere un momento..</h4>',
        css: {border: '3px solid #a00'}
    });
    $.ajax({
        type: "POST",
        url: SITE_URL + 'panel_vendedor/tarifas/ajax_get_clientes',
        data: {
            pagina: $('#pagina_tab2').val(),
            sexo: "X"
        },
        dataType: "html",
        success: function(response) {
            $('#tabla-resultados-clientes').unblock();
            $('#tabla-resultados-clientes').html(response);
            bind_pagination_links_tab2();
        }
    });
}

function bind_pagination_links() {
    $('#tabla-resultados-productos').find('.pagination a').on('click', function(e) {
        e.preventDefault();
        $('#pagina').val($(this).data('id'));
        updateResultadosProductos();
    });
}

function bind_pagination_links_tab2() {
    $('#tabla-resultados-clientes').find('.pagination a').on('click', function(e) {
        e.preventDefault();
        $('#pagina_tab2').val($(this).data('id'));
        updateResultadosClientes();
    });
}