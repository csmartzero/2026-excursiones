<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════
   1. TEMPLATE OVERRIDE — Archive
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
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght=600;700&family=Outfit:wght@400;500;600&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'excursiones-styles',
        EXCURSIONES_URL . 'assets/css/excursiones.css',
        array( 'excursiones-fonts' ),
        '7.0'
    );

    wp_enqueue_script(
        'excursiones-filters',
        EXCURSIONES_URL . 'assets/js/excursiones-filters.js',
        array(),
        '1.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'excursiones_encolar_assets' );

/* ════════════════════════════════════════════
   3. HTML DE LA TARJETA (versión 2026 mejorada con nuevos colores)
   ════════════════════════════════════════════ */
function excursiones_tarjeta_html( $post_id, $title ) {
    $precio    = get_post_meta( $post_id, '_precio',            true );
    $max       = get_post_meta( $post_id, '_max_participantes', true );
    $ubicacion = get_post_meta( $post_id, '_ubicacion',         true );
    $fecha     = get_post_meta( $post_id, '_fecha_salida',      true );
    $duracion  = get_post_meta( $post_id, '_duracion_dias',     true );
    $permalink = get_permalink( $post_id );

    if ( has_post_thumbnail( $post_id ) ) {
        $imagen = get_the_post_thumbnail( $post_id, 'large', array(
            'class'   => 'exc-card__img',
            'loading' => 'lazy',
            'alt'     => esc_attr( $title ),
        ) );
    } else {
        $imagen = '<div class="exc-card__img-placeholder" aria-hidden="true"></div>';
    }

    $badge_tipo = '';
    $tipos = get_the_terms( $post_id, 'tipo_excursion' );
    if ( $tipos && ! is_wp_error( $tipos ) ) {
        $badge_tipo = '<span class="exc-card__tag" style="background:#f39528; color:#fff;">' . esc_html( $tipos[0]->name ) . '</span>';
    }

    $badge_precio = '';
    if ( $precio !== '' && $precio !== false ) {
        $badge_precio = '<span class="exc-card__price" style="background:#17323a; color:#fff;">'
            . number_format( (float) $precio, 2, ',', '.' )
            . ' €</span>';
    }

    $fecha_html = '';
    if ( $fecha ) {
        $ts    = strtotime( $fecha );
        $meses = array( '', 'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic' );
        $fecha_html = '<div class="exc-card__cal" aria-label="Fecha de salida" style="border-top: 3px solid #f39528;">
            <span class="exc-card__cal-day">'   . date( 'd', $ts ) . '</span>
            <span class="exc-card__cal-month">' . $meses[ (int) date( 'm', $ts ) ] . '</span>
        </div>';
    }

    $svg_loc = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>';
    $svg_pax = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>';
    $svg_dur = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';

    $pills = '';
    if ( $ubicacion ) $pills .= '<span class="exc-pill exc-pill--loc">' . $svg_loc . esc_html( $ubicacion ) . '</span>';
    if ( $max )       $pills .= '<span class="exc-pill exc-pill--pax">' . $svg_pax . esc_html( $max ) . ' plazas</span>';
    if ( $duracion )  $pills .= '<span class="exc-pill exc-pill--dur">' . $svg_dur . ( $duracion == 1 ? '1 día' : esc_html( $duracion ) . ' días' ) . '</span>';

    $pills_html = $pills ? '<div class="exc-card__pills">' . $pills . '</div>' : '';

    $arrow = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';

    $precio_val = $precio !== '' && $precio !== false ? floatval( $precio ) : 0;

    return '
<article class="exc-card" data-price="' . esc_attr( $precio_val ) . '" data-title="' . esc_attr( $title ) . '">
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
            <a href="' . esc_url( $permalink ) . '" style="color:#17323a;">' . esc_html( $title ) . '</a>
        </h2>
        ' . $pills_html . '
        <a href="' . esc_url( $permalink ) . '" class="exc-card__cta" aria-label="Reservar ' . esc_attr( $title ) . '" style="color:#f39528;">
            Reservar plaza ' . $arrow . '
        </a>
    </div>
</article>';
}

/* ════════════════════════════════════════════
   4. SINGLE — Formulario de reserva (un solo paso sin PDF)
   ════════════════════════════════════════════ */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( 'excursiones' ) ) return $content;

    global $post;
    $precio    = get_post_meta( $post->ID, '_precio',            true );
    $max       = get_post_meta( $post->ID, '_max_participantes', true );
    $ubicacion = get_post_meta( $post->ID, '_ubicacion',         true );
    $fecha     = get_post_meta( $post->ID, '_fecha_salida',      true );
    $duracion  = get_post_meta( $post->ID, '_duracion_dias',     true );

    // ── Tabla de metadatos ──
    $items = '';
    if ( $precio )    $items .= '<div class="exc-single-item"><strong>Precio</strong><span>' . number_format( (float) $precio, 2, ',', '.' ) . ' €</span></div>';
    if ( $ubicacion ) $items .= '<div class="exc-single-item"><strong>Ubicación</strong><span>' . esc_html( $ubicacion ) . '</span></div>';
    if ( $max )       $items .= '<div class="exc-single-item"><strong>Plazas máx.</strong><span>' . esc_html( $max ) . '</span></div>';
    if ( $fecha )     $items .= '<div class="exc-single-item"><strong>Fecha salida</strong><span>' . esc_html( date_i18n( 'd/m/Y', strtotime( $fecha ) ) ) . '</span></div>';
    if ( $duracion )  $items .= '<div class="exc-single-item"><strong>Duración</strong><span>' . esc_html( $duracion ) . ' días</span></div>';

    $seccion_reserva = '';

    // ── Mensaje de éxito tras reserva confirmada ──
    if ( isset($_GET['reserva']) && $_GET['reserva'] == 'ok' ) {
        $seccion_reserva = '
        <div style="background:#e6f4ea; color:#137333; padding:20px; border-radius:12px; margin-top:28px; border-left:5px solid #f39528; font-family:\'Outfit\', sans-serif;">
            <strong style="font-size:18px;">✅ ¡Reserva confirmada!</strong>
            <p style="margin:8px 0 0;">Tu reserva ha sido registrada correctamente en nuestro sistema de forma inmediata.</p>
            <a href="' . esc_url( site_url('/mis-reservas/') ) . '" style="display:inline-block; margin-top:14px; background:#17323a; color:#fff; padding:8px 20px; border-radius:50px; text-decoration:none; font-weight:600; font-size:14px;">📋 Ver mis reservas</a>
        </div>';
    }

    // ── Formulario de reserva ──
    else {
        if ( is_user_logged_in() ) {
            $seccion_reserva .= '
            <div style="margin-top:28px; padding:24px; background:#fdfdfd; border:1px solid #eaeaea; border-radius:16px; font-family:\'Outfit\', sans-serif;">

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
                    <h3 style="margin:0; color:#17323a; font-family:\'Cormorant Garamond\', serif; font-size:26px;">📝 Datos de tu reserva</h3>
                    <a href="' . esc_url( site_url('/mis-reservas/') ) . '" style="background:#17323a; color:#fff; padding:7px 16px; text-decoration:none; border-radius:50px; font-size:13px; font-weight:600;">📋 Mis reservas</a>
                </div>

                <form action="' . esc_url( admin_url('admin-post.php') ) . '" method="POST" style="display:flex; flex-direction:column; gap:14px;">
                    <input type="hidden" name="action"       value="crear_reserva">
                    <input type="hidden" name="excursion_id" value="' . $post->ID . '">

                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:140px;">
                            <label style="font-weight:600; font-size:13px; display:block; margin-bottom:5px; color:#4b4540;">Fecha de reserva *</label>
                            <input type="date" name="fecha_reserva" required
                                   value="' . esc_attr($fecha) . '"
                                   style="width:100%; padding:9px 12px; border:1.5px solid #ddd; border-radius:8px; box-sizing:border-box;" />
                        </div>
                        <div style="flex:1; min-width:120px;">
                            <label style="font-weight:600; font-size:13px; display:block; margin-bottom:5px; color:#4b4540;">Nº Pasajeros *</label>
                            <input type="number" name="pasajeros" min="1" max="' . ( $max ? esc_attr($max) : '99' ) . '" value="1" required
                                   style="width:100%; padding:9px 12px; border:1.5px solid #ddd; border-radius:8px; box-sizing:border-box;" />
                        </div>
                    </div>

                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:140px;">
                            <label style="font-weight:600; font-size:13px; display:block; margin-bottom:5px; color:#4b4540;">Hotel / Alojamiento</label>
                            <input type="text" name="hotel" placeholder="Nombre del hotel"
                                   style="width:100%; padding:9px 12px; border:1.5px solid #ddd; border-radius:8px; box-sizing:border-box;" />
                        </div>
                        <div style="flex:1; min-width:100px;">
                            <label style="font-weight:600; font-size:13px; display:block; margin-bottom:5px; color:#4b4540;">Nº Habitación</label>
                            <input type="text" name="habitacion" placeholder="Ej: 204"
                                   style="width:100%; padding:9px 12px; border:1.5px solid #ddd; border-radius:8px; box-sizing:border-box;" />
                        </div>
                    </div>

                    <div>
                        <label style="font-weight:600; font-size:13px; display:block; margin-bottom:5px; color:#4b4540;">Punto de recogida</label>
                        <input type="text" name="recogida" placeholder="Ej: Recepción principal del hotel"
                               style="width:100%; padding:9px 12px; border:1.5px solid #ddd; border-radius:8px; box-sizing:border-box;" />
                    </div>

                    <div style="border-top:1px dashed #ddd; padding-top:14px; margin-top:2px;">
                        <label style="font-weight:600; font-size:13px; display:block; margin-bottom:5px; color:#4b4540;">Nombre del titular *</label>
                        <input type="text" name="nombre_completo" required placeholder="Nombre y apellidos"
                               style="width:100%; padding:9px 12px; border:1.5px solid #ddd; border-radius:8px; box-sizing:border-box;" />
                    </div>

                    <div>
                        <label style="font-weight:600; font-size:13px; display:block; margin-bottom:5px; color:#4b4540;">Teléfono (WhatsApp) *</label>
                        <input type="tel" name="telefono" required placeholder="Ej: +34 600 000 000"
                               style="width:100%; padding:9px 12px; border:1.5px solid #ddd; border-radius:8px; box-sizing:border-box;" />
                    </div>

                    ' . wp_nonce_field( 'hacer_reserva_' . $post->ID, '_wpnonce', true, false ) . '

                    <button type="submit" class="exc-single-cta" style="border:none; cursor:pointer; width:100%; justify-content:space-between; align-items:center; margin-top:6px; background:#17323a; color:#fff;">
                        Confirmar reserva
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                    <p style="text-align:center; font-size:12px; color:#a0998e; margin-top:8px; margin-bottom:0;">Tu reserva se procesará de inmediato.</p>
                </form>
            </div>';
        } else {
            $seccion_reserva .= '<div style="margin-top:28px; padding:16px 20px; background:#f8f9fa; border-radius:10px; border-left:4px solid #17323a; font-family:\'Outfit\', sans-serif;"><p style="margin:0;"><em>Debes <a href="' . wp_login_url(get_permalink()) . '">iniciar sesión</a> para realizar una reserva.</em></p></div>';
        }
    }

    if ( ! $items ) return $content;

    return $content
        . '<div class="exc-single-meta">' . $items . '</div>'
        . $seccion_reserva;
} );

/* ════════════════════════════════════════════
   5. PROCESAR RESERVA — Pago simulado sin PDF
   ════════════════════════════════════════════ */
add_action( 'admin_post_crear_reserva', 'procesar_creacion_reserva' );
add_action( 'admin_post_nopriv_crear_reserva', function() {
    wp_die('Debes iniciar sesión para hacer una reserva.');
});

function procesar_creacion_reserva() {
    if ( ! is_user_logged_in() ) wp_die('Debes iniciar sesión para hacer una reserva.');

    $excursion_id = isset($_POST['excursion_id']) ? intval($_POST['excursion_id']) : 0;
    if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'hacer_reserva_' . $excursion_id) ) {
        wp_die('Error de seguridad. Por favor, vuelve atrás e inténtalo de nuevo.');
    }

    $user_id   = get_current_user_id();

    $precio          = get_post_meta( $excursion_id, '_precio', true );
    $pasajeros       = isset($_POST['pasajeros'])       ? intval($_POST['pasajeros'])           : 1;
    $fecha_reserva   = isset($_POST['fecha_reserva'])   ? sanitize_text_field($_POST['fecha_reserva'])   : '';
    $hotel           = isset($_POST['hotel'])           ? sanitize_text_field($_POST['hotel'])           : '';
    $habitacion      = isset($_POST['habitacion'])      ? sanitize_text_field($_POST['habitacion'])      : '';
    $recogida        = isset($_POST['recogida'])        ? sanitize_text_field($_POST['recogida'])        : '';
    $nombre_completo = isset($_POST['nombre_completo']) ? sanitize_text_field($_POST['nombre_completo']) : '';
    $telefono        = isset($_POST['telefono'])        ? sanitize_text_field($_POST['telefono'])        : '';
    $total           = floatval($precio) * $pasajeros;

    // Crear reserva en la base de datos
    $reserva_id = wp_insert_post( array(
        'post_title'  => 'Reserva: ' . get_the_title($excursion_id) . ' (' . $nombre_completo . ')',
        'post_type'   => 'reservas',
        'post_status' => 'publish',
    ) );

    if ( is_wp_error( $reserva_id ) || $reserva_id === 0 ) {
        wp_die('Error interno al procesar la reserva. Contacta con nosotros.');
    }

    // Guardar todos los metadatos
    update_post_meta( $reserva_id, '_reserva_excursion_id',   $excursion_id );
    update_post_meta( $reserva_id, '_reserva_usuario_id',     $user_id );
    update_post_meta( $reserva_id, '_reserva_estado',         'confirmada' );
    update_post_meta( $reserva_id, '_reserva_fecha_elegida',  $fecha_reserva );
    update_post_meta( $reserva_id, '_reserva_pasajeros',      $pasajeros );
    update_post_meta( $reserva_id, '_reserva_hotel',          $hotel );
    update_post_meta( $reserva_id, '_reserva_habitacion',     $habitacion );
    update_post_meta( $reserva_id, '_reserva_recogida',       $recogida );
    update_post_meta( $reserva_id, '_reserva_nombre_titular', $nombre_completo );
    update_post_meta( $reserva_id, '_reserva_telefono',       $telefono );
    update_post_meta( $reserva_id, '_reserva_total',          $total );

    wp_redirect( add_query_arg('reserva', 'ok', get_permalink($excursion_id)) );
    exit;
}

/* ════════════════════════════════════════════
   6. SHORTCODE [mis_reservas]
   ════════════════════════════════════════════ */
add_shortcode( 'mis_reservas', 'excursiones_shortcode_mis_reservas' );

function excursiones_shortcode_mis_reservas() {
    if ( ! is_user_logged_in() ) {
        return '<p style="font-family:\'Outfit\', sans-serif;">Debes <a href="' . wp_login_url(get_permalink()) . '">iniciar sesión</a> para ver tu historial de reservas.</p>';
    }

    $user_id = get_current_user_id();
    $query = new WP_Query( array(
        'post_type'      => 'reservas',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_reserva_usuario_id',
                'value'   => $user_id,
                'compare' => '='
            )
        ),
        'orderby' => 'date',
        'order'   => 'DESC',
    ) );

    if ( ! $query->have_posts() ) {
        return '<div style="font-family:\'Outfit\', sans-serif; padding:40px; text-align:center; background:#fafafa; border-radius:16px; color:#6b6258;">
                    <span style="font-size:40px;">🧭</span>
                    <p style="margin:14px 0 0; font-size:16px;">Todavía no tienes ninguna reserva registrada.</p>
                </div>';
    }

    $html  = '<div style="font-family:\'Outfit\', sans-serif; overflow-x:auto;">';
    $html .= '<table style="width:100%; border-collapse:collapse; min-width:620px; background:#fff; border:1px solid #e0dbd3; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.05);">';
    $html .= '<thead>';
    $html .= '<tr style="background:#17323a; color:#fff;">';
    $html .= '<th style="padding:14px 16px; text-align:left; font-weight:600; font-size:12px; text-transform:uppercase; letter-spacing:.06em;">Referencia</th>';
    $html .= '<th style="padding:14px 16px; text-align:left; font-weight:600; font-size:12px; text-transform:uppercase; letter-spacing:.06em;">Excursión</th>';
    $html .= '<th style="padding:14px 16px; text-align:left; font-weight:600; font-size:12px; text-transform:uppercase; letter-spacing:.06em;">Fecha</th>';
    $html .= '<th style="padding:14px 16px; text-align:center; font-weight:600; font-size:12px; text-transform:uppercase; letter-spacing:.06em;">Pax</th>';
    $html .= '<th style="padding:14px 16px; text-align:right; font-weight:600; font-size:12px; text-transform:uppercase; letter-spacing:.06em;">Total</th>';
    $html .= '<th style="padding:14px 16px; text-align:center; font-weight:600; font-size:12px; text-transform:uppercase; letter-spacing:.06em;">Estado</th>';
    $html .= '</tr>';
    $html .= '</thead><tbody>';

    $fila = 0;
    while ( $query->have_posts() ) {
        $query->the_post();
        $id      = get_the_ID();
        $exc_id  = get_post_meta( $id, '_reserva_excursion_id',   true );
        $fecha   = get_post_meta( $id, '_reserva_fecha_elegida',  true );
        $pax     = get_post_meta( $id, '_reserva_pasajeros',      true );
        $estado  = get_post_meta( $id, '_reserva_estado',         true );
        $total   = get_post_meta( $id, '_reserva_total',          true );
        $titulo  = $exc_id ? get_the_title($exc_id) : 'Excursión no disponible';
        $ref     = 'EXC-' . str_pad($id, 6, '0', STR_PAD_LEFT);

        $bg_row = $fila % 2 === 0 ? '#fff' : '#fafaf8';

        switch ( $estado ) {
            case 'confirmada': $bg_est = '#137333'; $txt_est = '#fff'; $ico = '✅'; break;
            case 'cancelada':  $bg_est = '#dc2626'; $txt_est = '#fff'; $ico = '❌'; break;
            default:           $bg_est = '#f39528'; $txt_est = '#fff'; $ico = '⏳'; break;
        }

        $html .= '<tr style="background:' . $bg_row . '; border-bottom:1px solid #f0ece6;">';
        $html .= '<td style="padding:14px 16px; font-size:13px; color:#6b6258; font-weight:600;">' . esc_html($ref) . '</td>';
        $exc_link = $exc_id ? get_permalink($exc_id) : '#';

            $html .= '<td style="padding:14px 16px; font-size:14px;">
                <strong>
                    <a href="' . esc_url($exc_link) . '" style="color:#17323a; text-decoration:none;">
                        ' . esc_html($titulo) . '
                    </a>
                </strong>
            </td>';
        $html .= '<td style="padding:14px 16px; font-size:13px;">' . ( $fecha ? esc_html( date_i18n('d/m/Y', strtotime($fecha)) ) : '—' ) . '</td>';
        $html .= '<td style="padding:14px 16px; font-size:13px; text-align:center;">' . ( $pax ? esc_html($pax) : '1' ) . '</td>';
        $html .= '<td style="padding:14px 16px; font-size:14px; text-align:right; font-weight:700;">' . ( $total ? number_format((float)$total, 2, ',', '.') . ' €' : '—' ) . '</td>';
        $html .= '<td style="padding:14px 16px; text-align:center;"><span style="display:inline-flex; align-items:center; gap:5px; background:' . $bg_est . '; color:' . $txt_est . '; padding:4px 12px; border-radius:50px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;">' . $ico . ' ' . esc_html($estado) . '</span></td>';
        $html .= '</tr>';
        $fila++;
    }
    wp_reset_postdata();

    $html .= '</tbody></table></div>';
    return $html;
}