$(document).ready(function() {
    updateResultados();

    $('#listado-items').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            type: "POST",
            url: SITE_URL +'panel_vendedor/tarifas/ajax_get_clientes',
            data: form.serialize(),            
            complete: function() {                
                updateResultados();           
            }
        });        
    });
});

function updateResultados() {
    var form = $('#listado-items');
    $('#tabla-resultados').html('<br><br><br>');
    $('#tabla-resultados').block({
        message: '<h4>Procesando espere un momento..</h4>',
        css: {border: '3px solid #a00'}
    });
    $.ajax({
        type: "POST",
        url: SITE_URL + 'panel_vendedor/tarifas/ajax_get_clientes',
        data: form.serialize(),
        dataType: "html",
        success: function(response) {
            $('#tabla-resultados').unblock();
            $('#tabla-resultados').html(response);
            bind_pagination_links();
            bind_links();
        }
    });    
}

function bind_pagination_links() {
    $('.pagination a').on('click', function(e) {
        e.preventDefault();
        $('#pagina').val($(this).data('id'));
        updateResultados();
    });
}

function bind_links() {
    
}