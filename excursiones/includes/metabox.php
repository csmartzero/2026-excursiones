<?php
if ( ! defined( 'ABSPATH' ) ) exit;
///....................................................EXCURSIONES
/* ── Registrar metabox ── */
function excursiones_add_meta_box() {
    add_meta_box(
        'excursion_detalles',
        'Detalles de la excursión',
        'excursiones_meta_box_callback',
        'excursiones',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'excursiones_add_meta_box' );

/* ── Render campos ── */
function excursiones_meta_box_callback( $post ) {
    wp_nonce_field( 'guardar_excursion', 'excursion_nonce' );

    $precio    = get_post_meta( $post->ID, '_precio',           true );
    $max       = get_post_meta( $post->ID, '_max_participantes', true );
    $ubicacion = get_post_meta( $post->ID, '_ubicacion',        true );
    $fecha     = get_post_meta( $post->ID, '_fecha_salida',     true );
    $duracion  = get_post_meta( $post->ID, '_duracion_dias',    true );

    ?>
    <style>
        .exc-metabox { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 8px 0; }
        .exc-metabox label { display: block; font-weight: 600; margin-bottom: 4px; color: #1d2327; }
        .exc-metabox input { width: 100%; box-sizing: border-box; }
        .exc-metabox .full { grid-column: 1 / -1; }
    </style>
    <div class="exc-metabox">
        <div>
            <label for="exc_precio">💶 Precio (€)</label>
            <input type="number" step="0.01" min="0" id="exc_precio"
                   name="precio" value="<?php echo esc_attr( $precio ); ?>" />
        </div>
        <div>
            <label for="exc_max">👥 Máximo participantes</label>
            <input type="number" min="1" id="exc_max"
                   name="max_participantes" value="<?php echo esc_attr( $max ); ?>" />
        </div>
        <div>
            <label for="exc_fecha">📅 Fecha de salida</label>
            <input type="date" id="exc_fecha"
                   name="fecha_salida" value="<?php echo esc_attr( $fecha ); ?>" />
        </div>
        <div>
            <label for="exc_duracion">⏱ Duración (días)</label>
            <input type="number" min="1" id="exc_duracion"
                   name="duracion_dias" value="<?php echo esc_attr( $duracion ); ?>" />
        </div>
        <div class="full">
            <label for="exc_ubicacion">📍 Ubicación</label>
            <input type="text" id="exc_ubicacion"
                   name="ubicacion" value="<?php echo esc_attr( $ubicacion ); ?>" />
        </div>
    </div>
    <?php
}

/* ── Guardar metadatos ── */
function excursiones_guardar_meta( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['excursion_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['excursion_nonce'], 'guardar_excursion' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( get_post_type( $post_id ) !== 'excursiones' ) return;

    $campos = array(
        '_precio'            => array( 'key' => 'precio',           'sanitize' => 'floatval' ),
        '_max_participantes' => array( 'key' => 'max_participantes', 'sanitize' => 'intval' ),
        '_fecha_salida'      => array( 'key' => 'fecha_salida',      'sanitize' => 'sanitize_text_field' ),
        '_duracion_dias'     => array( 'key' => 'duracion_dias',     'sanitize' => 'intval' ),
        '_ubicacion'         => array( 'key' => 'ubicacion',         'sanitize' => 'sanitize_text_field' ),
    );

    foreach ( $campos as $meta_key => $campo ) {
        if ( isset( $_POST[ $campo['key'] ] ) ) {
            $valor = call_user_func( $campo['sanitize'], $_POST[ $campo['key'] ] );
            update_post_meta( $post_id, $meta_key, $valor );
        }
    }
}
add_action( 'save_post', 'excursiones_guardar_meta' );

///....................................................RESERVAS
/* ── Registrar Metabox para Reservas ── */
function reservas_add_meta_box() {
    add_meta_box(
        'reserva_detalles',
        'Detalles de la Reserva',
        'reserva_meta_box_callback',
        'reservas', // Se muestra solo en el CPT reservas
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'reservas_add_meta_box' );

/* ── Render del formulario en el Admin ── */
function reserva_meta_box_callback( $post ) {
    // Recuperamos los datos actuales
    $excursion_id = get_post_meta( $post->ID, '_reserva_excursion_id', true );
    $usuario_id   = get_post_meta( $post->ID, '_reserva_usuario_id', true );
    $estado       = get_post_meta( $post->ID, '_reserva_estado', true );

    // Obtenemos lista de excursiones y usuarios para los selectores
    $excursiones = get_posts( array( 'post_type' => 'excursiones', 'posts_per_page' => -1 ) );
    $usuarios    = get_users();

    wp_nonce_field( 'guardar_detalle_reserva', 'reserva_nonce' );
    ?>
    <div style="padding: 10px;">
        <p>
            <label style="display:block; margin-bottom:5px; font-weight:bold;">Excursión Reservada:</label>
            <select name="reserva_excursion_id" class="widefat">
                <?php foreach ( $excursiones as $exc ) : ?>
                    <option value="<?php echo $exc->ID; ?>" <?php selected( $excursion_id, $exc->ID ); ?>>
                        <?php echo esc_html( $exc->post_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label style="display:block; margin-bottom:5px; font-weight:bold;">Usuario que reserva:</label>
            <select name="reserva_usuario_id" class="widefat">
                <?php foreach ( $usuarios as $user ) : ?>
                    <option value="<?php echo $user->ID; ?>" <?php selected( $usuario_id, $user->ID ); ?>>
                        <?php echo esc_html( $user->display_name ) . ' (' . esc_html( $user->user_email ) . ')'; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label style="display:block; margin-bottom:5px; font-weight:bold;">Estado de la reserva:</label>
            <select name="reserva_estado" class="widefat" style="border: 2px solid #0073aa;">
                <option value="pendiente" <?php selected( $estado, 'pendiente' ); ?>>⏳ Pendiente</option>
                <option value="confirmada" <?php selected( $estado, 'confirmada' ); ?>>✅ Confirmada</option>
                <option value="cancelada" <?php selected( $estado, 'cancelada' ); ?>>❌ Cancelada</option>
            </select>
        </p>
    </div>
    <?php
}
/* ── Guardar los datos cuando el Admin edita la reserva ── */
function reservas_save_meta( $post_id ) {
    if ( ! isset( $_POST['reserva_nonce'] ) || ! wp_verify_nonce( $_POST['reserva_nonce'], 'guardar_detalle_reserva' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['reserva_excursion_id'] ) ) {
        update_post_meta( $post_id, '_reserva_excursion_id', intval( $_POST['reserva_excursion_id'] ) );
    }
    if ( isset( $_POST['reserva_usuario_id'] ) ) {
        update_post_meta( $post_id, '_reserva_usuario_id', intval( $_POST['reserva_usuario_id'] ) );
    }
    if ( isset( $_POST['reserva_estado'] ) ) {
        update_post_meta( $post_id, '_reserva_estado', sanitize_text_field( $_POST['reserva_estado'] ) );
    }
}
add_action( 'save_post', 'reservas_save_meta' );
