<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════
   1. TEMPLATE OVERRIDE — Archive
   Carga nuestra plantilla en lugar de la del tema,
   eliminando cualquier interferencia de bloques Gutenberg.
   ════════════════════════════════════════════ */
add_filter( 'template_include', function ( $template ) {
    if ( is_post_type_archive( 'excursiones' ) || is_tax( 'tipo_excursion' ) ) {
        $custom = EXCURSIONES_DIR . 'templates/archive-excursiones.php';
        if ( file_exists( $custom ) ) {
            return $custom;
        }
    }
    return $template;
} );

/* ════════════════════════════════════════════
   2. ENCOLAR ASSETS
   ════════════════════════════════════════════ */
function excursiones_encolar_assets() {
    $en_archive = is_post_type_archive( 'excursiones' ) || is_tax( 'tipo_excursion' );
    $en_single  = is_singular( 'excursiones' );

    if ( ! $en_archive && ! $en_single ) return;

    wp_enqueue_style(
        'excursiones-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Outfit:wght@400;500;600&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'excursiones-styles',
        EXCURSIONES_URL . 'assets/css/excursiones.css',
        array( 'excursiones-fonts' ),
        '5.1'
    );
}
add_action( 'wp_enqueue_scripts', 'excursiones_encolar_assets' );

/* ════════════════════════════════════════════
   3. HTML DE LA TARJETA
   ════════════════════════════════════════════ */
function excursiones_tarjeta_html( $post_id, $title ) {
    $precio    = get_post_meta( $post_id, '_precio',            true );
    $max       = get_post_meta( $post_id, '_max_participantes', true );
    $ubicacion = get_post_meta( $post_id, '_ubicacion',         true );
    $fecha     = get_post_meta( $post_id, '_fecha_salida',      true );
    $duracion  = get_post_meta( $post_id, '_duracion_dias',     true );
    $permalink = get_permalink( $post_id );

    /* Imagen — una sola vez, controlada por nosotros */
    if ( has_post_thumbnail( $post_id ) ) {
        $imagen = get_the_post_thumbnail( $post_id, 'large', array(
            'class'   => 'exc-card__img',
            'loading' => 'lazy',
            'alt'     => esc_attr( $title ),
        ) );
    } else {
        $imagen = '<div class="exc-card__img-placeholder" aria-hidden="true"></div>';
    }

    /* Badge de categoría */
    $badge_tipo = '';
    $tipos = get_the_terms( $post_id, 'tipo_excursion' );
    if ( $tipos && ! is_wp_error( $tipos ) ) {
        $badge_tipo = '<span class="exc-card__tag">' . esc_html( $tipos[0]->name ) . '</span>';
    }

    /* Badge de precio */
    $badge_precio = '';
    if ( $precio !== '' && $precio !== false ) {
        $badge_precio = '<span class="exc-card__price">'
            . number_format( (float) $precio, 2, ',', '.' )
            . ' €</span>';
    }

    /* Calendario de fecha */
    $fecha_html = '';
    if ( $fecha ) {
        $ts    = strtotime( $fecha );
        $meses = array( '', 'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic' );
        $fecha_html = '<div class="exc-card__cal" aria-label="Fecha de salida">
            <span class="exc-card__cal-day">'   . date( 'd', $ts ) . '</span>
            <span class="exc-card__cal-month">' . $meses[ (int) date( 'm', $ts ) ] . '</span>
        </div>';
    }

    /* Pills de metadatos */
    $svg_loc = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>';
    $svg_pax = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>';
    $svg_dur = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';

    $pills = '';
    if ( $ubicacion ) $pills .= '<span class="exc-pill exc-pill--loc">' . $svg_loc . esc_html( $ubicacion ) . '</span>';
    if ( $max )       $pills .= '<span class="exc-pill exc-pill--pax">' . $svg_pax . esc_html( $max ) . ' plazas</span>';
    if ( $duracion )  $pills .= '<span class="exc-pill exc-pill--dur">' . $svg_dur . ( $duracion == 1 ? '1 día' : esc_html( $duracion ) . ' días' ) . '</span>';

    $pills_html = $pills ? '<div class="exc-card__pills">' . $pills . '</div>' : '';

    $arrow = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';

    return '
<article class="exc-card">
    <a href="' . esc_url( $permalink ) . '" class="exc-card__media-link" tabindex="-1" aria-hidden="true">
        <div class="exc-card__media">
            ' . $imagen . '
            <div class="exc-card__overlay"></div>
            ' . $badge_tipo . '
            ' . $badge_precio . '
            ' . $fecha_html . '
        </div>
    </a>
    <div class="exc-card__body">
        <h2 class="exc-card__title">
            <a href="' . esc_url( $permalink ) . '">' . esc_html( $title ) . '</a>
        </h2>
        ' . $pills_html . '
        <a href="' . esc_url( $permalink ) . '" class="exc-card__cta" aria-label="Reservar ' . esc_attr( $title ) . '">
            Reservar plaza ' . $arrow . '
        </a>
    </div>
</article>';
}

/* ════════════════════════════════════════════
   4. SINGLE — tabla de datos debajo del contenido
   ════════════════════════════════════════════ */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( 'excursiones' ) ) return $content;

    global $post;
    $precio    = get_post_meta( $post->ID, '_precio',            true );
    $max       = get_post_meta( $post->ID, '_max_participantes', true );
    $ubicacion = get_post_meta( $post->ID, '_ubicacion',         true );
    $fecha     = get_post_meta( $post->ID, '_fecha_salida',      true );
    $duracion  = get_post_meta( $post->ID, '_duracion_dias',     true );

    $items = '';
    if ( $precio )    $items .= '<div class="exc-single-item"><strong>Precio</strong><span>' . number_format( (float) $precio, 2, ',', '.' ) . ' €</span></div>';
    if ( $ubicacion ) $items .= '<div class="exc-single-item"><strong>Ubicación</strong><span>' . esc_html( $ubicacion ) . '</span></div>';
    if ( $max )       $items .= '<div class="exc-single-item"><strong>Plazas máx.</strong><span>' . esc_html( $max ) . '</span></div>';
    if ( $fecha )     $items .= '<div class="exc-single-item"><strong>Fecha salida</strong><span>' . esc_html( date_i18n( 'd/m/Y', strtotime( $fecha ) ) ) . '</span></div>';
    if ( $duracion )  $items .= '<div class="exc-single-item"><strong>Duración</strong><span>' . esc_html( $duracion ) . ' días</span></div>';

    // -- INICIO DEL NUEVO SISTEMA DE RESERVAS --
    
    // 1. Mensaje de éxito si viene de reservar
    $mensaje = '';
    if ( isset($_GET['reserva']) && $_GET['reserva'] == 'ok' ) {
        $mensaje = '<div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-top:20px; font-weight:bold;">✅ ¡Reserva realizada con éxito! Revisa la pestaña "Mis Reservas".</div>';
    }

    // 2. Botón de reserva (solo si está logueado)
    $boton_reserva = '';
    if ( is_user_logged_in() ) {
        $boton_reserva .= '<form action="' . esc_url( admin_url('admin-post.php') ) . '" method="POST" style="margin-top: 28px;">';
        $boton_reserva .= '<input type="hidden" name="action" value="crear_reserva">';
        $boton_reserva .= '<input type="hidden" name="excursion_id" value="' . $post->ID . '">';
        $boton_reserva .= wp_nonce_field( 'hacer_reserva_' . $post->ID, '_wpnonce', true, false );
        
        // El botón mantiene tu diseño y tu icono SVG
        $boton_reserva .= '<button type="submit" class="exc-single-cta" style="border:none; cursor:pointer; width:100%; display:flex; justify-content:space-between; align-items:center;">Reservar plaza <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></button>';
        
        $boton_reserva .= '</form>';
    } else {
        $boton_reserva .= '<div style="margin-top: 28px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #0073aa;"><p style="margin:0;"><em>Debes <a href="' . wp_login_url(get_permalink()) . '">iniciar sesión</a> para poder reservar.</em></p></div>';
    }

    if ( ! $items ) return $content;

    return $content
        . '<div class="exc-single-meta">' . $items . '</div>'
        . $boton_reserva
        . $mensaje;
} );

//RESERVAS
function shortcode_mis_reservas() {
    if ( ! is_user_logged_in() ) {
        return '<p>Debes iniciar sesión para ver tus reservas.</p>';
    }

    $current_user_id = get_current_user_id();

    // Buscamos las reservas de este usuario
    $query = new WP_Query(array(
        'post_type'  => 'reservas',
        'meta_query' => array(
            array(
                'key'   => '_reserva_usuario_id',
                'value' => $current_user_id,
            )
        )
    ));

    if ( ! $query->have_posts() ) return '<p>No tienes reservas todavía.</p>';

    $html = '<div class="mis-reservas-container">';
    $html .= '<table style="width:100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">';
    $html .= '<thead style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                <tr>
                    <th style="padding: 15px; text-align: left;">Excursión</th>
                    <th style="padding: 15px; text-align: left;">Fecha</th>
                    <th style="padding: 15px; text-align: left;">Estado</th>
                </tr>
              </thead><tbody>';

    while ( $query->have_posts() ) {
        $query->the_post();
        $exc_id = get_post_meta(get_the_ID(), '_reserva_excursion_id', true);
        $estado = get_post_meta(get_the_ID(), '_reserva_estado', true);
        $fecha  = get_the_date('d/m/Y');
        
        $color_estado = ($estado == 'confirmada') ? '#28a745' : (($estado == 'pendiente') ? '#ffc107' : '#dc3545');

        $html .= '<tr style="border-bottom: 1px solid #eee;">';
        $html .= '<td style="padding: 15px;"><strong>' . get_the_title($exc_id) . '</strong></td>';
        $html .= '<td style="padding: 15px;">' . $fecha . '</td>';
        $html .= '<td style="padding: 15px;"><span style="background:' . $color_estado . '; color:#fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; text-transform: uppercase;">' . $estado . '</span></td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';
    wp_reset_postdata();

    return $html;
}
add_shortcode('mis_reservas', 'shortcode_mis_reservas');

/* ════════════════════════════════════════════
   6. PROCESAR LA RESERVA EN BACKEND
   ════════════════════════════════════════════ */
add_action( 'admin_post_crear_reserva', 'procesar_creacion_reserva' );
function procesar_creacion_reserva() {
    // Seguridad
    if ( ! is_user_logged_in() ) wp_die('Debes iniciar sesión para hacer una reserva.');
    
    $excursion_id = isset($_POST['excursion_id']) ? intval($_POST['excursion_id']) : 0;
    if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'hacer_reserva_' . $excursion_id) ) {
        wp_die('Error de seguridad. Inténtalo de nuevo.');
    }

    $user_id = get_current_user_id();
    $user_info = get_userdata($user_id);

    // Creamos el post tipo "Reservas"
    $reserva_data = array(
        'post_title'  => 'Reserva: ' . get_the_title($excursion_id) . ' (' . $user_info->display_name . ')',
        'post_type'   => 'reservas',
        'post_status' => 'publish'
    );

    $reserva_id = wp_insert_post( $reserva_data );

    // Si se crea bien, guardamos los metadatos y redirigimos
    if ( ! is_wp_error( $reserva_id ) && $reserva_id > 0 ) {
        update_post_meta( $reserva_id, '_reserva_excursion_id', $excursion_id );
        update_post_meta( $reserva_id, '_reserva_usuario_id', $user_id );
        update_post_meta( $reserva_id, '_reserva_estado', 'pendiente' );

        // Devolvemos al usuario a la página de la excursión con el mensaje "?reserva=ok"
        wp_redirect( add_query_arg('reserva', 'ok', get_permalink($excursion_id)) );
        exit;
    } else {
        wp_die('Hubo un error al procesar tu reserva. Contacta con nosotros.');
    }
}