jQuery(document).ready(function($) {

    // ========================================
    // MEDIA UPLOADER (código original)
    // ========================================

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

    // ========================================
    // REPEATER FIELDS
    // ========================================

    // Agregar nueva fila al repeater
    $(document).on('click', '.add-repeater-item', function(e) {
        e.preventDefault();

        const button = $(this);
        const containerId = button.data('container');
        const fieldName = button.data('name');
        const container = $('#' + containerId);

        const newRow = `
            <div class="repeater-item" style="margin-bottom: 10px; display: flex; gap: 8px; align-items: center;">
                <input
                    type="text"
                    name="${fieldName}[]"
                    value=""
                    class="regular-text"
                    placeholder="Ej: Terapia Cognitivo-Conductual"
                />
                <button
                    type="button"
                    class="button button-link-delete remove-repeater-item"
                    data-container="${containerId}"
                    style="color: #b32d2e;"
                >
                    ×
                </button>
            </div>
        `;

        container.append(newRow);
        updateRemoveButtons(containerId);
    });

    // Eliminar fila del repeater
    $(document).on('click', '.remove-repeater-item', function(e) {
        e.preventDefault();

        const button = $(this);
        const containerId = button.data('container');
        const container = $('#' + containerId);

        // Solo eliminar si hay más de 1 item
        if (container.find('.repeater-item').length > 1) {
            button.closest('.repeater-item').remove();
            updateRemoveButtons(containerId);
        }
    });

    // Actualizar visibilidad de botones de eliminar
    function updateRemoveButtons(containerId) {
        const container = $('#' + containerId);
        const items = container.find('.repeater-item');
        const removeButtons = items.find('.remove-repeater-item');

        if (items.length === 1) {
            removeButtons.hide();
        } else {
            removeButtons.show();
        }
    }

    // Inicializar estado de botones al cargar la página
    $('.repeater-container').each(function() {
        updateRemoveButtons($(this).attr('id'));
    });




    // ========================================
    // FORMATO CLP EN TIEMPO REAL
    // ========================================

    // Formatear valor CLP
    function formatCLP(value) {
        // Remover todo excepto números
        const numero = value.replace(/[^0-9]/g, '');

        if (!numero) return '';

        // Formatear con separador de miles
        const formatted = parseInt(numero).toLocaleString('es-CL');

        return '$' + formatted;
    }

    // Aplicar formato mientras se escribe
    $(document).on('input', '.clp-input', function() {
        const input = $(this);
        const cursorPosition = this.selectionStart;
        const oldLength = input.val().length;

        // Obtener solo números
        const rawValue = input.val().replace(/[^0-9]/g, '');

        // Formatear
        const formattedValue = formatCLP(rawValue);

        // Actualizar valor
        input.val(formattedValue);

        // Guardar valor raw en data attribute
        input.attr('data-raw-value', rawValue);

        // Ajustar posición del cursor
        const newLength = formattedValue.length;
        const lengthDiff = newLength - oldLength;
        const newPosition = cursorPosition + lengthDiff;

        this.setSelectionRange(newPosition, newPosition);
    });

    // Permitir solo números y teclas de control
    $(document).on('keydown', '.clp-input', function(e) {
        const allowedKeys = [
            8,  // Backspace
            9,  // Tab
            46, // Delete
            37, 38, 39, 40, // Arrows
            36, 35 // Home, End
        ];

        const isNumber = (e.keyCode >= 48 && e.keyCode <= 57) || // Números superiores
            (e.keyCode >= 96 && e.keyCode <= 105);   // Numpad

        const isAllowed = allowedKeys.includes(e.keyCode) ||
            isNumber ||
            e.ctrlKey ||
            e.metaKey;

        if (!isAllowed) {
            e.preventDefault();
        }
    });

    // Formatear al cargar la página si hay valor
    $('.clp-input').each(function() {
        const input = $(this);
        const rawValue = input.attr('data-raw-value');

        if (rawValue && rawValue !== '0') {
            input.val(formatCLP(rawValue));
        }
    });
});