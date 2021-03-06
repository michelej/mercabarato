$(document).ready(function() {
    updateResultados();

    $('#listado-items').on('submit', function(e) {
        e.preventDefault();
        $('#pagina').val('1');
        updateResultados();
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
        url: SITE_URL + 'panel_vendedor/anuncio/ajax_get_listado_resultados',
        data: form.serialize(),
        dataType: "html",
        success: function(response) {
            $('#tabla-resultados').unblock();
            $('#tabla-resultados').html(response);
            bind_pagination_links();
            bind_borrar_links();
            bind_check_all();
            bind_btn_eliminar_seleccion();
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

function bind_borrar_links() {
    $('.table-responsive').find('.options').find('.row_action').off();
    $('.table-responsive').find('.options').find('.row_action').on('click', function(e) {
        e.preventDefault();
        var a_href = $(this).attr('href');
        $.blockUI({message: $('#question'),blockMsgClass: 'modal-confimacion'});

        if ($(this).hasClass('borrar')) {
            $('#question').find('.modal-title').html("Estas seguro que deseas eliminar este anuncio?.");
        } else if ($(this).hasClass('habilitar')) {
            $('#question').find('.modal-title').html("Estas seguro que deseas habilitar este anuncio?.");
        } else {
            $('#question').find('.modal-title').html("Estas seguro que deseas inhabilitar este anuncio?.");
        }

        $('#yes').off();
        $('#yes').click(function() {
            $.ajax({
                url: a_href,
                cache: false,
                complete: function() {
                    updateResultados();
                    $.unblockUI();
                }
            });
        });
        $('#no').click(function() {
            $.unblockUI();
            return false;
        });
    });
}

function bind_check_all() {
    $('#tabla-resultados').find("input[name='select_all']").click(function() {
        if ($(this).is(':checked')) {
            $('#tabla-resultados').find("input[name='eliminar']").each(function() {
                $(this).prop("checked", true);
            });

        } else {
            $('#tabla-resultados').find("input[name='eliminar']").each(function() {
                $(this).prop("checked", false);
            });
        }
    });
}

function bind_btn_eliminar_seleccion() {
    $('#btn-eliminar-seleccionados').on('click', function(e) {
        e.preventDefault();
        $.blockUI({message: $('#question'),blockMsgClass: 'modal-confimacion'});
        $('#question').find('.modal-title').html("Estas seguro que deseas eliminar estos anuncios?.");
        
        $('#yes').off();
        $('#yes').click(function() {
            $.ajax({
                url: SITE_URL+"panel_vendedor/anuncio/borrar-multi",                
                type:"POST",
                data:{
                    anuncio_ids:get_anuncios_seleccionados_checkboxes()
                },
                complete: function() {
                    updateResultados();
                    $.unblockUI();
                }
            });
        });
        $('#no').click(function() {
            $.unblockUI();
            return false;
        });

    });
}

function get_anuncios_seleccionados_checkboxes() {
    var string = "";
    $('#tabla-resultados').find('input[name="eliminar"]:checked').each(function() {
        string += $(this).parents('tr').data('id');
        string += ";;";
    });
    if (string.length > 1) {
        string = string.slice(0, -2);
    }
    if (string.length === 0) {
        return false;
    } else {
        return string;
    }
}