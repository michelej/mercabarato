$(document).ready(function() {

    $('#fileupload').fileupload({
        dataType: 'json',
        replaceFileInput: false,
        method: "post",
        autoUpload: "false",
        singleFileUploads: false,
        add: function(e, data) {
            $("#admin_producto_submit").off('click').on('click', function(e) {
                var numFiles;
                if (typeof iebad !== 'undefined') {
                    numFiles = 0;
                }else{
                    numFiles = $("#fileupload")[0].files.length;
                }

                if ($("#admin_producto_form").valid() && $('input[name="categoria_id"]').val() !== "" && (numFiles >= 0 && numFiles <= 3)) {
                    e.preventDefault();
                    data.submit();
                }
            });
        },
        start: function(e, data) {
            $.blockUI({
                message: $('#throbber'),
                css: {border: '0'}});
        },
        done: function(e, data) {
            var files = "";
            var error = false;
            var error_text = "";
            $.each(data.result.files, function(index, file) {
                if (typeof file.error !== 'undefined') {
                    error = true;
                    error_text = file.error;
                } else {
                    files = files + file.name;
                    files = files + ";;";
                }
            });

            if (!error) {
                files = files.slice(0, -2);
                $('#file_name').val(files);
                $('#admin_producto_form').submit();
            } else {
                $('#fileupload_alert').css('display', 'block');
                $('html, body').animate({
                    scrollTop: $('#grupo-imagenes').offset().top
                }, 1000);
                $('#fileupload_alert').find('span').html(error_text);
            }

            $.unblockUI();
        }
    });

    $('#cambiar_imagen').on('click', function(e) {
        e.preventDefault();
        $('.fileupload_button').css('display', 'block');
        $('.producto-img-container').html('');
        $('.producto-img-container').css('display', 'none');
        $(this).css('display', 'none');
    });

    $('#categorias_jtree').jstree({expand_selected_onload: true});

    $('#categorias_jtree').bind("select_node.jstree", function(event, data) {
        var obj = data.instance.get_node(data.node, true);
        if (obj) {
            obj.siblings(".jstree-open").each(function() {
                data.instance.close_node(this, 0);
            });
        }
    });

    $('#categorias_jtree').on("changed.jstree", function(e, data) {
        var categoria_id = $('#categorias_jtree').find('#' + data.selected).data('id');
        $('input[name="categoria_id"]').val(categoria_id);
    });

    $('input[name="transporte"]').on('change', function() {
        if ($(this).val() == '1') {
            $('input[name="transporte_txt"]').css('display', 'block');
        } else {
            $('input[name="transporte_txt"]').val('');
            $('input[name="transporte_txt"]').css('display', 'none');
        }
    });

    $('input[name="impuesto"]').on('change', function() {
        if ($(this).val() == '0') {
            $('input[name="impuesto_txt"]').css('display', 'block');
        } else {
            $('input[name="impuesto_txt"]').val('');
            $('input[name="impuesto_txt"]').css('display', 'none');
        }
    });

    validateForms();
});

function validateForms() {
    jQuery.validator.addMethod("pasado5Dias", function(value, element) {
        if ($('input[name="fecha_anterior"]').length) {
            var inicio = $('input[name="fecha_anterior"]').val();
            var fin = $.datepicker.formatDate('dd/mm/yy', new Date());
            var diff = daydiff(parseDate(inicio), parseDate(fin));
            var precio_sinmodificar = $('input[name="precio_sinmodif"]').val();
            var precio_modificado = $('input[name="precio"]').val();

            if (parseFloat(precio_sinmodificar) != parseFloat(precio_modificado)) {
                if (diff > 5) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return true;
        }
    }, "Tienen que haber pasado 5 dias desde la ultima modificacion.");


    $("#admin_producto_form").validate({
        submitHandler: function(form) {
            console.log("submit");
            var flag = true;
            var numFiles;
                if (typeof iebad !== 'undefined') {
                    numFiles = 0;
                }else{
                    numFiles = $("#fileupload")[0].files.length;
                }

            if (numFiles >= 0 && numFiles <= 3) {
                $('#fileupload_alert').css('display', 'none');
            } else {
                $('#fileupload_alert').css('display', 'block');
                $('#fileupload_alert').find('span').html("Debe seleccionar un máximo de 3 imágenes.");
                $('html, body').animate({
                    scrollTop: $('#fileupload_alert').offset().top
                }, 1000);
                return false;
            }

            if ($('input[name="categoria_id"]').val() !== "") {
                $('#seleccionar-categoria_alert').css('display', 'none');
            } else {
                $('#seleccionar-categoria_alert').css('display', 'block');
                $('html, body').animate({
                    scrollTop: $('#seleccionar-categoria_alert').offset().top
                }, 1000);
                return false;
            }

            if (flag) {
                form.submit();
            }
        },
        rules: {
            nombre: {required: true},
            precio: {
                required: true,
                number: true,
                pasado5Dias: true
            },
            precio_extra1_cantidad: {number: true},
            precio_extra2_cantidad: {number: true},
            precio_extra3_cantidad: {number: true},
            grupo_txt: {required: {
                    depends: function(element) {
                        return ($('input[name="familia_txt"]').val() != '' || $('input[name="subfamilia_txt"]').val() != '');
                    }
                }, maxlength: 23
            },
            familia_txt: {required: {
                    depends: function(element) {
                        return ($('input[name="subfamilia_txt"]').val() != '');
                    }
                }, maxlength: 23
            },
            subfamilia_txt: {maxlength: 23}
        },
        messages: {
            nombre: {
                required: "El nombre del producto es necesario."
            },
            precio: {
                required: "Ingrese un monto.",
                number: "Ingrese un numero"
            },
            precio_extra1_cantidad: {number: "La cantidad debe ser numerica"},
            precio_extra2_cantidad: {number: "La cantidad debe ser numerica"},
            precio_extra3_cantidad: {number: "La cantidad debe ser numerica"},
            grupo_txt: {required: "Debes ingresar un grupo", maxlength: " Debe ser maximo 23 caracteres"},
            familia_txt: {required: "Debes ingresar una familia para este grupo", maxlength: " Debe ser maximo 23 caracteres"},
            subfamilia_txt: {maxlength: " Debe ser maximo 23 caracteres"}
        }
    });

}

function parseDate(str) {
    var mdy = str.split('/')
    return new Date(mdy[2], mdy[1] - 1, mdy[0]);
}

function daydiff(first, second) {
    return Math.floor((second - first) / (1000 * 60 * 60 * 24));
}