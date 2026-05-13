<?php
if ( ! defined( 'ABSPATH' ) ) exit;

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
