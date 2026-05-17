<?php
/*
|--------------------------------------------------------------------------
| GESTIÓN DE EXCURSIONES: TABLA Y EDICIÓN LIMPIA
|--------------------------------------------------------------------------
*/

/**
 * 1. Definir columnas de la tabla
 */
add_filter('manage_excursiones_posts_columns', function($columns) {
    $nuevas_columns = array(
        'cb'       => '<input type="checkbox" />', // Selector masivo obligatorio
        'id'       => 'ID',
        'title'    => 'Título',
        'precio'   => 'Precio',
        'plazas'   => 'Plazas',
        'acciones' => 'Acciones',
    );
    return $nuevas_columns;
});

/**
 * 2. Rellenar datos y botones
 */
add_action('manage_excursiones_posts_custom_column', function($column, $post_id) {
    switch ($column) {
        case 'id': 
            echo '<span style="color:#999">' . $post_id . '</span>'; 
            break;
        
        case 'precio': 
            $precio = get_post_meta($post_id, '_precio', true);
            echo '<strong>' . ($precio !== '' ? esc_html($precio) . ' €' : '—') . '</strong>';
            break;
        
        case 'plazas': 
            echo esc_html(get_post_meta($post_id, '_max_participantes', true) ?: '—'); 
            break;
        
        case 'acciones':
            $edit_link   = get_edit_post_link($post_id);
            $delete_link = get_delete_post_link($post_id, '', true); // true para forzar papelera
            
            echo '<a href="' . $edit_link . '" class="button button-primary" style="background:#2271b1; border:none; margin-right:4px;">Editar</a>';
            echo '<a href="' . $delete_link . '" class="button" style="color:#d63638; border-color:#d63638;" onclick="return confirm(\'¿Enviar a la papelera?\')">Borrar</a>';
            break;
    }
}, 10, 2);

/**
 * 3. Hacer columnas ordenables
 */
add_filter('manage_edit-excursiones_sortable_columns', function($columns) {
    $columns['id']     = 'ID';
    $columns['precio'] = 'precio';
    return $columns;
});

/**
 * 4. Interfaz de edición ultra-limpia (Adiós Gutenberg/Editor)
 */


add_action('admin_head', function() {

    if ( ! function_exists('get_current_screen') ) {
        return;
    }

    $screen = get_current_screen();

    if ( ! $screen || $screen->post_type !== 'excursiones' ) {
        return;
    }

    ?>
    <style>
        /* Ocultar elementos innecesarios del editor de bloques */
        .editor-styles-wrapper { background-color: #f0f0f1 !important; }
        .block-editor-writing-flow { max-width: 800px; margin: 0 auto; }
        
        /* Ajustar el título para que parezca un campo de formulario */
        .editor-post-title__input {
            font-family: inherit !important;
            font-size: 28px !important;
            border: 1px solid #ddd !important;
            padding: 10px 15px !important;
            border-radius: 4px !important;
            background: #fff !important;
        }

        /* Estilizar tu Metabox para que sea el centro de atención */
        #excursion_detalles {
            margin-top: 20px;
            border-radius: 8px;
            border: 1px solid #c3c4c7;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        #excursion_detalles .postbox-header {
            background: #f8f9fa;
            border-bottom: 1px solid #c3c4c7;
            border-radius: 8px 8px 0 0;
        }

        /* Botones de acción en la tabla más modernos */
        .column-acciones .button {
            transition: all 0.2s ease;
            font-weight: 500;
        }
        .column-acciones .button:hover {
            transform: translateY(-1px);
        }
    </style>
    <?php
});

//..........................................................................RESERVAS
/**
 * Definir columnas para la tabla de Reservas
 */
add_filter('manage_reservas_posts_columns', function($columns) {
    $nuevas_columns = array(
        'cb'           => '<input type="checkbox" />',
        'title'        => 'Reserva',
        'excursion'    => 'Excursión',
        'usuario'      => 'Usuario / Email',
        'fecha_reserva'=> 'Fecha',
        'estado'       => 'Estado',
    );
    return $nuevas_columns;
});

/**
 * Rellenar las columnas con los datos reales
 */
add_action('manage_reservas_posts_custom_column', function($column, $post_id) {
    switch ($column) {
        case 'excursion':
            $exc_id = get_post_meta($post_id, '_reserva_excursion_id', true);
            if ($exc_id) {
                echo '<a href="' . get_edit_post_link($exc_id) . '"><strong>' . get_the_title($exc_id) . '</strong></a>';
            } else {
                echo '<span style="color:red;">No asignada</span>';
            }
            break;

        case 'usuario':
            $user_id = get_post_meta($post_id, '_reserva_usuario_id', true);
            $user_info = get_userdata($user_id);
            if ($user_info) {
                echo '<strong>' . esc_html($user_info->display_name) . '</strong><br>';
                echo '<small style="color:#666;">' . esc_html($user_info->user_email) . '</small>';
            } else {
                echo 'Usuario desconocido';
            }
            break;

        case 'fecha_reserva':
            echo get_the_date('d/m/Y H:i', $post_id);
            break;

        case 'estado':
            $estado = get_post_meta($post_id, '_reserva_estado', true);
            $colores = array(
                'pendiente'  => array('bg' => '#ffc107', 'txt' => '#000'),
                'confirmada' => array('bg' => '#28a745', 'txt' => '#fff'),
                'cancelada'  => array('bg' => '#dc3545', 'txt' => '#fff')
            );
            $style = isset($colores[$estado]) ? $colores[$estado] : $colores['pendiente'];
            
            echo '<span style="background:' . $style['bg'] . '; color:' . $style['txt'] . '; padding: 5px 10px; border-radius: 4px; font-weight: bold; text-transform: uppercase; font-size: 10px;">';
            echo $estado ? esc_html($estado) : 'pendiente';
            echo '</span>';
            break;
    }
}, 10, 2);

add_filter('manage_edit-reservas_sortable_columns', function($columns) {
    $columns['estado'] = 'estado';
    $columns['excursion'] = 'excursion';
    return $columns;
});