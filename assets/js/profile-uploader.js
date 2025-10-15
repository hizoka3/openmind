jQuery(document).ready(function($) {

    // Abrir Media Uploader
    $('.openmind-upload-image').on('click', function(e) {
        e.preventDefault();

        const button = $(this);
        const targetField = button.data('target');
        const inputField = $('#' + targetField);
        const previewImage = $('#' + targetField + '_preview');

        // Crear frame del media uploader
        const frame = wp.media({
            title: 'Seleccionar Foto Profesional',
            button: {
                text: 'Usar esta imagen'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });

        // Cuando se selecciona una imagen
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();

            // Guardar ID en el campo hidden
            inputField.val(attachment.id);

            // Mostrar preview
            previewImage.attr('src', attachment.url).show();

            // Cambiar texto del botón
            button.text('Cambiar Imagen');

            // Mostrar botón de eliminar si no existe
            if (!button.next('.openmind-remove-image').length) {
                button.after(
                    '<button type="button" class="button button-link-delete openmind-remove-image" data-target="' + targetField + '" style="margin-left: 10px; color: #b32d2e;">Eliminar</button>'
                );
            }
        });

        frame.open();
    });

    // Eliminar imagen
    $(document).on('click', '.openmind-remove-image', function(e) {
        e.preventDefault();

        const button = $(this);
        const targetField = button.data('target');
        const inputField = $('#' + targetField);
        const previewImage = $('#' + targetField + '_preview');
        const uploadButton = button.prev('.openmind-upload-image');

        // Limpiar campos
        inputField.val('');
        previewImage.hide().attr('src', '');

        // Cambiar texto del botón
        uploadButton.text('Seleccionar Imagen');

        // Eliminar botón de "Eliminar"
        button.remove();
    });
});