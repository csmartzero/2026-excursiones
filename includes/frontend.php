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
    $tiene_dashboard = has_shortcode( get_post()->post_content ?? '', 'dashboard_admin_excursiones' );

    if ( ! $en_archive && ! $en_single && ! $tiene_dashboard ) return;

    wp_enqueue_style(
        'excursiones-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Outfit:wght@400;500;600;700&display=swap',
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
   3. HTML DE LA TARJETA
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
   4. SINGLE — Formulario de reserva
   ════════════════════════════════════════════ */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( 'excursiones' ) ) return $content;

    global $post;
    if ( empty( $post ) || empty( $post->ID ) ) {
        return $content;
    }

    $precio    = get_post_meta( $post->ID, '_precio',            true );
    $max       = get_post_meta( $post->ID, '_max_participantes', true );
    $ubicacion = get_post_meta( $post->ID, '_ubicacion',         true );
    $fecha     = get_post_meta( $post->ID, '_fecha_salida',      true );
    $duracion  = get_post_meta( $post->ID, '_duracion_dias',     true );

    $precio_val         = is_numeric( $precio ) && floatval( $precio ) > 0 ? floatval( $precio ) : 0;
    $max_participantes  = is_numeric( $max ) && intval( $max ) > 0 ? intval( $max ) : 0;
    $fecha_salida       = $fecha && strtotime( $fecha ) ? $fecha : '';
    $duracion_text      = $duracion ? esc_html( $duracion ) . ' días' : '—';
    $ubicacion_text     = $ubicacion ? esc_html( $ubicacion ) : '—';

    $seccion_reserva = '';

    if ( isset($_GET['reserva']) && $_GET['reserva'] == 'ok' ) {
        $seccion_reserva = '
        <div style="background:#e6f4ea; color:#137333; padding:20px; border-radius:12px; margin-top:28px; border-left:5px solid #f39528; font-family:\'Outfit\', sans-serif;">
            <strong style="font-size:18px;">✅ ¡Reserva confirmada!</strong>
            <p style="margin:8px 0 0;">Tu reserva ha sido registrada correctamente en nuestro sistema de forma inmediata.</p>
            <a href="' . esc_url( site_url('/mis-reservas/') ) . '" style="display:inline-block; margin-top:14px; background:#17323a; color:#fff; padding:8px 20px; border-radius:50px; text-decoration:none; font-weight:600; font-size:14px;">📋 Ver mis reservas</a>
        </div>';
    }
    else {
        if ( is_user_logged_in() ) {
            $seccion_reserva .= '
            <div class="exc-single-page" style="font-family:\'Outfit\', sans-serif;">
                <div class="exc-single-page__inner">
                    <div class="exc-single-booking-card">
                        <h2 class="exc-single-booking-card__title">Datos de tu reserva</h2>'; 

            if ( $precio_val <= 0 ) {
                $seccion_reserva .= '<div class="exc-single-warning" style="padding:20px; background:#fff4e5; border:1px solid #f5c27b; border-radius:16px; color:#4e3720; margin-bottom:24px;">No se ha configurado un precio válido para esta excursión. Contacta con el administrador para poder reservar.</div>';
            }

            // Selector de paradas de autobús configuradas globalmente
            $paradas = get_option( 'excursiones_paradas_autobus', array() );
            $select_paradas_html = '';
            if ( ! empty( $paradas ) ) {
                $select_paradas_html .= '<label style="display:block; margin-bottom:12px; font-weight:500;">Parada de autobús elegida *';
                $select_paradas_html .= '<select name="parada_autobus" required class="exc-single-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e0dbd3; margin-top:4px;">';
                $select_paradas_html .= '<option value="">-- Selecciona una parada de recogida --</option>';
                foreach ( $paradas as $parada ) {
                    $select_paradas_html .= '<option value="' . esc_attr( $parada ) . '">' . esc_html( $parada ) . '</option>';
                }
                $select_paradas_html .= '</select></label>';
            } else {
                $select_paradas_html .= '<p style="font-size:13px; color:#a0998e;"><em>No se requieren paradas de autobús específicas para esta ruta.</em></p>';
            }

            $seccion_reserva .= '
                        <form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST" class="exc-single-booking-form">
                            <input type="hidden" name="action" value="crear_reserva">
                            <input type="hidden" name="excursion_id" value="' . $post->ID . '">

                            <div class="exc-single-booking-table-wrapper">
                                <table class="exc-single-booking-table">
                                    <tbody>
                                        <tr>
                                            <th>Precio unitario</th>
                                            <td><strong id="exc-price-display" data-base-price="' . esc_attr( $precio_val ) . '">' . number_format( $precio_val, 2, ',', '.' ) . ' €</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Nº Pasajeros</th>
                                            <td><input id="exc-pasajeros" type="number" name="pasajeros" min="1" max="' . ( $max_participantes ? esc_attr( $max_participantes ) : '99' ) . '" value="1" required class="exc-single-input"></td>
                                        </tr>
                                        <tr>
                                            <th>Fecha de reserva</th>
                                            <td><input type="date" name="fecha_reserva" required value="' . esc_attr( $fecha_salida ) . '" class="exc-single-input"></td>
                                        </tr>
                                        <tr>
                                            <th>Total aproximado</th>
                                            <td><strong id="exc-total-display">' . number_format( $precio_val, 2, ',', '.' ) . ' €</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Ubicación</th>
                                            <td>' . $ubicacion_text . '</td>
                                        </tr>
                                        <tr>
                                            <th>Duración</th>
                                            <td>' . $duracion_text . '</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="exc-single-booking-extra">
                                ' . $select_paradas_html . '
                                <label>Hotel / Alojamiento
                                    <input type="text" name="hotel" placeholder="Nombre del hotel" class="exc-single-input">
                                </label>
                                <label>Nº habitación
                                    <input type="text" name="habitacion" placeholder="Ej: 204" class="exc-single-input">
                                </label>
                                <label>Punto de recogida adicional (si aplica)
                                    <input type="text" name="recogida" placeholder="Ej: Recepción principal" class="exc-single-input">
                                </label>
                                <label>Nombre del titular *
                                    <input type="text" name="nombre_completo" required placeholder="Nombre y apellidos" class="exc-single-input">
                                </label>
                                <label>Teléfono (WhatsApp) *
                                    <input type="tel" name="telefono" required placeholder="Ej: +34 600 000 000" class="exc-single-input">
                                </label>
                            </div>

                            ' . wp_nonce_field( 'hacer_reserva_' . $post->ID, '_wpnonce', true, false ) . '

                            <button type="submit" class="exc-single-cta" style="border:none; cursor:pointer; width:100%; justify-content:space-between; align-items:center; margin-top:16px; background:#17323a; color:#fff;">
                                Confirmar reserva
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>';
        } else {
            $seccion_reserva .= '<div style="margin-top:28px; padding:16px 20px; background:#f8f9fa; border-radius:10px; border-left:4px solid #17323a; font-family:\'Outfit\', sans-serif;"><p style="margin:0;"><em>Debes <a href="' . wp_login_url(get_permalink()) . '">iniciar sesión</a> para realizar una reserva.</em></p></div>';
        }
    }

    return $content . $seccion_reserva;
} );

/* ════════════════════════════════════════════
   5. PROCESAR RESERVA
   ════════════════════════════════════════════ */
add_action( 'admin_post_crear_reserva', 'procesar_creacion_reserva' );
function procesar_creacion_reserva() {
    if ( ! is_user_logged_in() ) wp_die('Debes iniciar sesión para hacer una reserva.');

    $excursion_id = isset($_POST['excursion_id']) ? intval($_POST['excursion_id']) : 0;
    if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'hacer_reserva_' . $excursion_id) ) {
        wp_die('Error de seguridad.');
    }

    $user_id         = get_current_user_id();
    $precio          = get_post_meta( $excursion_id, '_precio', true );
    $pasajeros       = isset($_POST['pasajeros']) ? intval($_POST['pasajeros']) : 1;
    $fecha_reserva   = isset($_POST['fecha_reserva']) ? sanitize_text_field($_POST['fecha_reserva']) : '';
    $parada_autobus  = isset($_POST['parada_autobus']) ? sanitize_text_field($_POST['parada_autobus']) : '';
    $hotel           = isset($_POST['hotel']) ? sanitize_text_field($_POST['hotel']) : '';
    $habitacion      = isset($_POST['habitacion']) ? sanitize_text_field($_POST['habitacion']) : '';
    $recogida        = isset($_POST['recogida']) ? sanitize_text_field($_POST['recogida']) : '';
    $nombre_completo = isset($_POST['nombre_completo']) ? sanitize_text_field($_POST['nombre_completo']) : '';
    $telefono        = isset($_POST['telefono']) ? sanitize_text_field($_POST['telefono']) : '';
    $total           = floatval($precio) * $pasajeros;

    $reserva_id = wp_insert_post( array(
        'post_title'  => 'Reserva: ' . get_the_title($excursion_id) . ' (' . $nombre_completo . ')',
        'post_type'   => 'reservas',
        'post_status' => 'publish',
    ) );

    if ( ! is_wp_error( $reserva_id ) && $reserva_id !== 0 ) {
        update_post_meta( $reserva_id, '_reserva_excursion_id',   $excursion_id );
        update_post_meta( $reserva_id, '_reserva_usuario_id',     $user_id );
        update_post_meta( $reserva_id, '_reserva_estado',         'confirmada' );
        update_post_meta( $reserva_id, '_reserva_fecha_elegida',  $fecha_reserva );
        update_post_meta( $reserva_id, '_reserva_pasajeros',      $pasajeros );
        update_post_meta( $reserva_id, '_reserva_parada_autobus', $parada_autobus );
        update_post_meta( $reserva_id, '_reserva_hotel',          $hotel );
        update_post_meta( $reserva_id, '_reserva_habitacion',     $habitacion );
        update_post_meta( $reserva_id, '_reserva_recogida',       $recogida );
        update_post_meta( $reserva_id, '_reserva_nombre_titular', $nombre_completo );
        update_post_meta( $reserva_id, '_reserva_telefono',       $telefono );
        update_post_meta( $reserva_id, '_reserva_total',          $total );
    }

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
    
    $html = '';
    if ( current_user_can('manage_options') ) {
        $html .= '<div style="background:#fff7ed; border:1px solid #ffedd5; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-family:\'Outfit\',sans-serif; font-size:14px; color:#c2410c;">
            ℹ️ Eres administrador. Puedes acceder directamente al <a href="' . esc_url(site_url('/dashboard-admin/')) . '" style="font-weight:600; color:#17323a; text-decoration:underline;">Dashboard de Gestión Frontend</a>.
        </div>';
    }

    $query = new WP_Query( array(
        'post_type'      => 'reservas',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array( array( 'key' => '_reserva_usuario_id', 'value' => $user_id, 'compare' => '=' ) ),
        'orderby' => 'date', 'order' => 'DESC',
    ) );

    if ( ! $query->have_posts() ) {
        return $html . '<div style="font-family:\'Outfit\', sans-serif; padding:40px; text-align:center; background:#fafafa; border-radius:16px; color:#6b6258;"><p>Todavía no tienes ninguna reserva registrada.</p></div>';
    }

    $html .= '<div style="font-family:\'Outfit\', sans-serif; overflow-x:auto;">';
    $html .= '<table style="width:100%; border-collapse:collapse; min-width:620px; background:#fff; border:1px solid #e0dbd3; border-radius:16px; overflow:hidden;">';
    $html .= '<thead><tr style="background:#17323a; color:#fff;">';
    $html .= '<th style="padding:14px; text-align:left;">Referencia</th><th style="padding:14px; text-align:left;">Excursión</th><th style="padding:14px; text-align:left;">Fecha</th><th style="padding:14px; text-align:center;">Pax</th><th style="padding:14px; text-align:right;">Total</th><th style="padding:14px; text-align:center;">Estado</th>';
    $html .= '</tr></thead><tbody>';

    while ( $query->have_posts() ) {
        $query->the_post();
        $id      = get_the_ID();
        $exc_id  = get_post_meta( $id, '_reserva_excursion_id',   true );
        $fecha   = get_post_meta( $id, '_reserva_fecha_elegida',  true );
        $pax     = get_post_meta( $id, '_reserva_pasajeros',      true );
        $estado  = get_post_meta( $id, '_reserva_estado',         true );
        $total   = get_post_meta( $id, '_reserva_total',          true );
        $titulo  = $exc_id ? get_the_title($exc_id) : 'No disponible';
        $ref     = 'EXC-' . str_pad($id, 6, '0', STR_PAD_LEFT);

        $html .= '<tr style="border-bottom:1px solid #f0ece6;">';
        $html .= '<td style="padding:14px; font-weight:600; color:#6b6258;">' . esc_html($ref) . '</td>';
        $html .= '<td style="padding:14px;"><strong><a href="' . esc_url(get_permalink($exc_id)) . '" style="color:#17323a;">' . esc_html($titulo) . '</a></strong></td>';
        $html .= '<td style="padding:14px;">' . ($fecha ? esc_html(date('d/m/Y', strtotime($fecha))) : '—') . '</td>';
        $html .= '<td style="padding:14px; text-align:center;">' . esc_html($pax) . '</td>';
        $html .= '<td style="padding:14px; text-align:right; font-weight:700;">' . number_format((float)$total, 2, ',', '.') . ' €</td>';
        $html .= '<td style="padding:14px; text-align:center;"><span style="background:#137333; color:#fff; padding:4px 10px; border-radius:50px; font-size:11px;">' . esc_html($estado) . '</span></td>';
        $html .= '</tr>';
    }
    wp_reset_postdata();
    $html .= '</tbody></table></div>';
    return $html;
}

/* ════════════════════════════════════════════
   7. DASHBOARD ADMISTRADOR FRONTEND [dashboard_admin_excursiones]
   ════════════════════════════════════════════ */
add_shortcode( 'dashboard_admin_excursiones', 'excursiones_dashboard_admin_html' );
function excursiones_dashboard_admin_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return '<div style="padding:20px; background:#fde8e8; color:#9b1c1c; border-radius:8px; font-family:\'Outfit\',sans-serif;">Acceso denegado. Este panel está reservado exclusivamente para administradores.</div>';
    }

    // Identificar pestaña activa por parámetro URL (evita que se junte todo)
    $tab_activa = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'excursiones';

    $paradas = get_option( 'excursiones_paradas_autobus', array() );

    $query_reservas = new WP_Query( array(
        'post_type'      => 'reservas',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );

    $html = '<div class="exc-dashboard" style="font-family:\'Outfit\', sans-serif; color:#17323a; max-width:1200px; margin:0 auto; padding:20px;">';
    $html .= '<h1 style="font-family:\'Cormorant Garamond\', serif; font-size:36px; border-bottom:2px solid #f39528; padding-bottom:10px; margin-bottom:20px;">🧭 Panel de Control de Excursiones</h1>';

    if ( isset($_GET['admin_status']) && $_GET['admin_status'] == 'success' ) {
        $html .= '<div style="background:#e6f4ea; color:#137333; padding:14px; border-radius:8px; margin-bottom:20px; font-weight:500;">✔ Operación realizada con éxito en el sistema.</div>';
    }

    // --- NAVEGACIÓN POR PESTAÑAS (TABS) ---
    $html .= '<div class="exc-tabs-nav" style="display:flex; gap:8px; margin-bottom:30px; border-bottom:2px solid #f0ece6; padding-bottom:12px;">';
    
    $btn_exc_style = ($tab_activa === 'excursiones') ? 'background:#17323a; color:#fff;' : 'background:#f5f3ef; color:#17323a;';
    $btn_res_style = ($tab_activa === 'reservas') ? 'background:#17323a; color:#fff;' : 'background:#f5f3ef; color:#17323a;';
    $btn_par_style = ($tab_activa === 'paradas') ? 'background:#17323a; color:#fff;' : 'background:#f5f3ef; color:#17323a;';

    $html .= '<a href="' . esc_url(add_query_arg('tab', 'excursiones')) . '" style="padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:600; font-size:15px; ' . $btn_exc_style . '">✨ Crear Excursión</a>';
    $html .= '<a href="' . esc_url(add_query_arg('tab', 'reservas')) . '" style="padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:600; font-size:15px; ' . $btn_res_style . '">📊 Ver Reservas (' . $query_reservas->found_posts . ')</a>';
    $html .= '<a href="' . esc_url(add_query_arg('tab', 'paradas')) . '" style="padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:600; font-size:15px; ' . $btn_par_style . '">🚌 Rutas y Paradas</a>';
    $html .= '</div>';

    // TAB A: CREAR EXCURSIÓN
    if ( $tab_activa === 'excursiones' ) {
        $html .= '<div style="background:#fff; border:1px solid #e0dbd3; padding:28px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.03); max-width:700px; margin: 0 auto;">';
        $html .= '<h2 style="margin-top:0; color:#17323a; font-size:22px; margin-bottom:20px; border-bottom:1px solid #f0ece6; padding-bottom:8px;">✨ Crear Nueva Excursión</h2>';
        // Añadido enctype para soportar subida de imágenes
        $html .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:16px;">';
        $html .= '<input type="hidden" name="action" value="admin_guardar_excursion">';
        $html .= wp_nonce_field( 'admin_action_nonce', '_wpnonce', true, false );
        
        $html .= '<label style="font-weight:500;">Título de la excursión *<input type="text" name="titulo" required placeholder="Ej: Tour por los Volcanes" style="width:100%; padding:10px; margin-top:6px; border-radius:6px; border:1px solid #ccc; font-family:inherit;"></label>';
        
        $html .= '<div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">';
        $html .= '<label style="font-weight:500;">Precio (€) *<input type="number" step="0.01" name="precio" required style="width:100%; padding:10px; margin-top:6px; border-radius:6px; border:1px solid #ccc; font-family:inherit;"></label>';
        $html .= '<label style="font-weight:500;">Plazas Máx *<input type="number" name="max_participantes" required style="width:100%; padding:10px; margin-top:6px; border-radius:6px; border:1px solid #ccc; font-family:inherit;"></label>';
        $html .= '</div>';
        
        $html .= '<div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">';
        $html .= '<label style="font-weight:500;">Fecha de salida<input type="date" name="fecha_salida" style="width:100%; padding:10px; margin-top:6px; border-radius:6px; border:1px solid #ccc; font-family:inherit;"></label>';
        $html .= '<label style="font-weight:500;">Duración (días)<input type="number" name="duracion_dias" value="1" style="width:100%; padding:10px; margin-top:6px; border-radius:6px; border:1px solid #ccc; font-family:inherit;"></label>';
        $html .= '</div>';
        
        $html .= '<label style="font-weight:500;">Ubicación / Destino<input type="text" name="ubicacion" placeholder="Ej: Isla Norte" style="width:100%; padding:10px; margin-top:6px; border-radius:6px; border:1px solid #ccc; font-family:inherit;"></label>';
        
        // Input para subir la foto de portada
        $html .= '<label style="font-weight:500; background:#faf9f6; padding:14px; border-radius:8px; border:1px dashed #c0b7ad;">📸 Imagen de Portada (Destacada)<input type="file" name="imagen_portada" accept="image/*" style="display:block; margin-top:8px; font-size:14px;"></label>';
        
        $html .= '<button type="submit" style="background:#17323a; color:#fff; border:none; padding:14px; border-radius:6px; font-weight:600; cursor:pointer; margin-top:10px; font-size:16px;">Publicar Excursión</button>';
        $html .= '</form>';
        $html .= '</div>';
    }

    // TAB B: PARADAS DE AUTOBÚS
    if ( $tab_activa === 'paradas' ) {
        $html .= '<div style="background:#fff; border:1px solid #e0dbd3; padding:28px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.03); max-width:600px; margin: 0 auto;">';
        $html .= '<h2 style="margin-top:0; color:#17323a; font-size:22px; margin-bottom:20px; border-bottom:1px solid #f0ece6; padding-bottom:8px;">🚌 Rutas y Paradas de Autobús</h2>';
        
        $html .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST" style="display:flex; gap:10px; margin-bottom:24px;">';
        $html .= '<input type="hidden" name="action" value="admin_gestion_paradas">';
        $html .= '<input type="hidden" name="subaction" value="add">';
        $html .= wp_nonce_field( 'admin_action_nonce', '_wpnonce', true, false );
        $html .= '<input type="text" name="nueva_parada" required placeholder="Nombre de la nueva parada" style="flex:1; padding:10px; border-radius:6px; border:1px solid #ccc; font-family:inherit;">';
        $html .= '<button type="submit" style="background:#f39528; color:#fff; border:none; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer; font-family:inherit;">Añadir</button>';
        $html .= '</form>';

        if ( ! empty( $paradas ) ) {
            $html .= '<ul style="list-style:none; padding:0; margin:0; border:1px solid #f0ece6; border-radius:8px; max-height:350px; overflow-y:auto;">';
            foreach ( $paradas as $idx => $parada ) {
                $html .= '<li style="padding:12px 16px; border-bottom:1px solid #f0ece6; display:flex; justify-content:space-between; align-items:center; font-size:15px;">';
                $html .= '<span>📍 ' . esc_html( $parada ) . '</span>';
                $html .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST" style="margin:0;" onsubmit="return confirm(\'¿Eliminar esta parada?\');">';
                $html .= '<input type="hidden" name="action" value="admin_gestion_paradas">';
                $html .= '<input type="hidden" name="subaction" value="delete">';
                $html .= '<input type="hidden" name="delete_idx" value="' . $idx . '">';
                $html .= wp_nonce_field( 'admin_action_nonce', '_wpnonce', true, false );
                $html .= '<button type="submit" style="background:none; border:none; color:#dc2626; cursor:pointer; font-size:13px; font-weight:600; font-family:inherit;">Eliminar</button>';
                $html .= '</form>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= '<p style="color:#6b6258; font-size:15px; text-align:center; padding:30px; background:#fafaf8; border-radius:8px;">No hay paradas dadas de alta aún.</p>';
        }
        $html .= '</div>';
    }

    // TAB C: HISTORIAL DE RESERVAS DETALLADO + ACCIONES
    if ( $tab_activa === 'reservas' ) {
        $html .= '<div style="background:#fff; border:1px solid #e0dbd3; padding:24px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.03); overflow-x:auto;">';
        $html .= '<h2 style="margin-top:0; color:#17323a; font-size:22px; margin-bottom:20px; border-bottom:1px solid #f0ece6; padding-bottom:8px;">📊 Listado Detallado de Reservas Recibidas</h2>';

        if ( $query_reservas->have_posts() ) {
            $html .= '<table style="width:100%; border-collapse:collapse; font-size:13px; min-width:1050px;">';
            $html .= '<thead><tr style="background:#17323a; color:#fff; border-bottom:2px solid #f39528;">';
            $html .= '<th style="padding:12px 10px; text-align:left;">Ref / Cliente</th>';
            $html .= '<th style="padding:12px 10px; text-align:left;">Excursión (Enlace)</th>';
            $html .= '<th style="padding:12px 10px; text-align:center;">Fecha Salida</th>';
            $html .= '<th style="padding:12px 10px; text-align:center;">Pax</th>';
            $html .= '<th style="padding:12px 10px; text-align:left;">Datos de Recogida y Alojamiento</th>';
            $html .= '<th style="padding:12px 10px; text-align:right;">Total Cobrado</th>';
            $html .= '<th style="padding:12px 10px; text-align:center;">Estado Actual</th>';
            $html .= '<th style="padding:12px 10px; text-align:center; width:170px;">Acciones de Gestión</th>';
            $html .= '</tr></thead><tbody>';

            while ( $query_reservas->have_posts() ) {
                $query_reservas->the_post();
                $r_id = get_the_ID();

                $exc_id     = get_post_meta( $r_id, '_reserva_excursion_id', true );
                $fecha      = get_post_meta( $r_id, '_reserva_fecha_elegida', true );
                $pax        = get_post_meta( $r_id, '_reserva_pasajeros', true );
                $total      = get_post_meta( $r_id, '_reserva_total', true );
                $estado     = get_post_meta( $r_id, '_reserva_estado', true );
                $titular    = get_post_meta( $r_id, '_reserva_nombre_titular', true );
                $tel        = get_post_meta( $r_id, '_reserva_telefono', true );
                $parada     = get_post_meta( $r_id, '_reserva_parada_autobus', true );
                $hotel      = get_post_meta( $r_id, '_reserva_hotel', true );
                $habitacion = get_post_meta( $r_id, '_reserva_habitacion', true );
                $recogida   = get_post_meta( $r_id, '_reserva_recogida', true );

                $ref = 'EXC-' . str_pad($r_id, 6, '0', STR_PAD_LEFT);
                $excursion_title = $exc_id ? get_the_title($exc_id) : 'Eliminada o no disponible';
                $excursion_link  = $exc_id ? get_permalink($exc_id) : '#';

                // Determinar color rápido según estado
                $bg_status = '#17323a';
                if ($estado === 'pendiente') $bg_status = '#f39528';
                if ($estado === 'cancelada') $bg_status = '#dc2626';

                $html .= '<tr style="border-bottom:1px solid #f0ece6; vertical-align: top; background: '.($estado === 'cancelada' ? '#fff5f5' : 'transparent').';">';
                $html .= '<td style="padding:12px 10px;"><strong>' . esc_html($ref) . '</strong><br><span style="color:#f39528; font-weight:600;">' . esc_html($titular) . '</span><br><small style="color:#6b6258;">📞 ' . esc_html($tel) . '</small></td>';
                
                // Click a la excursión a la que pertenece
                $html .= '<td style="padding:12px 10px;">';
                if ($exc_id) {
                    $html .= '<a href="' . esc_url($excursion_link) . '" target="_blank" style="color:#17323a; font-weight:700; text-decoration:underline;" title="Ver excursión en el mapa/web">' . esc_html($excursion_title) . ' ↗</a>';
                } else {
                    $html .= '<span style="color:#999; font-style:italic;">' . esc_html($excursion_title) . '</span>';
                }
                $html .= '</td>';
                
                $html .= '<td style="padding:12px 10px; text-align:center;">' . ($fecha ? esc_html(date('d/m/Y', strtotime($fecha))) : '—') . '</td>';
                $html .= '<td style="padding:12px 10px; text-align:center; font-weight:600;">' . esc_html($pax) . '</td>';
                $html .= '<td style="padding:12px 10px; line-height:1.4;">';
                if($parada)   $html .= '<strong>Bus:</strong> ' . esc_html($parada) . '<br>';
                if($hotel)    $html .= '<strong>Hotel:</strong> ' . esc_html($hotel) . ' (Hab: ' . esc_html($habitacion) . ')<br>';
                if($recogida) $html .= '<strong>Extra:</strong> ' . esc_html($recogida);
                if(!$parada && !$hotel && !$recogida) $html .= '—';
                $html .= '</td>';
                $html .= '<td style="padding:12px 10px; text-align:right; font-weight:700; color:#17323a; font-size:14px;">' . number_format((float)$total, 2, ',', '.') . ' €</td>';
                
                // Estado actual visual
                $html .= '<td style="padding:12px 10px; text-align:center;"><span style="background:'.$bg_status.'; color:#fff; padding:4px 10px; border-radius:40px; font-size:11px; font-weight:600; display:inline-block; text-transform:uppercase;">' . esc_html($estado) . '</span></td>';
                
                // Formulario de Cambiar Estado + Botón de Eliminar
                $html .= '<td style="padding:12px 10px; text-align:center;">';
                
                // 1. Selector de Estado
                $html .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST" style="margin:0 0 8px 0; display:flex; gap:4px; justify-content:center;">';
                $html .= '<input type="hidden" name="action" value="admin_cambiar_estado_reserva">';
                $html .= '<input type="hidden" name="reserva_id" value="' . $r_id . '">';
                $html .= wp_nonce_field( 'admin_action_nonce', '_wpnonce', true, false );
                $html .= '<select name="nuevo_estado" style="padding:4px; font-size:11px; border-radius:4px; border:1px solid #ccc; font-family:inherit;">';
                $html .= '<option value="confirmada" ' . selected($estado, 'confirmada', false) . '>Confirmada</option>';
                $html .= '<option value="pendiente" ' . selected($estado, 'pendiente', false) . '>Pendiente</option>';
                $html .= '<option value="cancelada" ' . selected($estado, 'cancelada', false) . '>Cancelada</option>';
                $html .= '</select>';
                $html .= '<button type="submit" style="background:#17323a; color:#fff; border:none; padding:4px 6px; font-size:11px; border-radius:4px; cursor:pointer; font-family:inherit;">OK</button>';
                $html .= '</form>';
                
                // 2. Botón Eliminar
                $html .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST" style="margin:0;" onsubmit="return confirm(\'🚨 ¿Estás completamente seguro de que deseas ELIMINAR esta reserva? Esta acción no se puede deshacer.\');">';
                $html .= '<input type="hidden" name="action" value="admin_eliminar_reserva">';
                $html .= '<input type="hidden" name="reserva_id" value="' . $r_id . '">';
                $html .= wp_nonce_field( 'admin_action_nonce', '_wpnonce', true, false );
                $html .= '<button type="submit" style="background:#dc2626; color:#fff; border:none; padding:5px 10px; font-size:11px; border-radius:4px; cursor:pointer; font-weight:500; font-family:inherit; width:90px;">🗑 Eliminar</button>';
                $html .= '</form>';
                
                $html .= '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        } else {
            $html .= '<p style="color:#6b6258; text-align:center; padding:40px; background:#fafaf8; border-radius:8px; font-size:15px;">No hay registros de reservas en el sistema actualmente.</p>';
        }

        $html .= '</div>';
    }

    $html .= '</div>'; // Fin .exc-dashboard
    wp_reset_postdata();

    return $html;
}

/* ════════════════════════════════════════════
   8. PROCESAR ACCIONES DEL DASHBOARD FRONTEND
   ════════════════════════════════════════════ */

// A: Guardar excursión (Soporta ahora foto de portada destacada)
add_action( 'admin_post_admin_guardar_excursion', 'excursiones_action_admin_guardar_excursion' );
function excursiones_action_admin_guardar_excursion() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die('No permitido.');
    check_admin_referer( 'admin_action_nonce', '_wpnonce' );

    $titulo = isset($_POST['titulo']) ? sanitize_text_field($_POST['titulo']) : '';
    if( empty($titulo) ) wp_die('Faltan campos obligatorios.');

    $post_id = wp_insert_post( array(
        'post_title'   => $titulo,
        'post_type'    => 'excursiones',
        'post_status'  => 'publish',
    ) );

    if ( ! is_wp_error($post_id) && $post_id > 0 ) {
        update_post_meta( $post_id, '_precio', isset($_POST['precio']) ? floatval($_POST['precio']) : 0 );
        update_post_meta( $post_id, '_max_participantes', isset($_POST['max_participantes']) ? intval($_POST['max_participantes']) : 0 );
        update_post_meta( $post_id, '_fecha_salida', isset($_POST['fecha_salida']) ? sanitize_text_field($_POST['fecha_salida']) : '' );
        update_post_meta( $post_id, '_duracion_dias', isset($_POST['duracion_dias']) ? intval($_POST['duracion_dias']) : 1 );
        update_post_meta( $post_id, '_ubicacion', isset($_POST['ubicacion']) ? sanitize_text_field($_POST['ubicacion']) : '' );

        // Procesar subida de archivo de imagen de portada de forma segura en el frontend
        if ( ! empty( $_FILES['imagen_portada']['name'] ) ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            $attachment_id = media_handle_upload( 'imagen_portada', $post_id );

            if ( ! is_wp_error( $attachment_id ) ) {
                set_post_thumbnail( $post_id, $attachment_id );
            }
        }
    }

    wp_redirect( add_query_arg( array( 'admin_status' => 'success', 'tab' => 'excursiones' ), wp_get_referer() ) );
    exit;
}

// B: Cambiar Estado de una Reserva desde el listado
add_action( 'admin_post_admin_cambiar_estado_reserva', 'excursiones_action_admin_cambiar_estado_reserva' );
function excursiones_action_admin_cambiar_estado_reserva() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die('No permitido.');
    check_admin_referer( 'admin_action_nonce', '_wpnonce' );

    $reserva_id   = isset($_POST['reserva_id']) ? intval($_POST['reserva_id']) : 0;
    $nuevo_estado = isset($_POST['nuevo_estado']) ? sanitize_text_field($_POST['nuevo_estado']) : '';

    if ( $reserva_id && in_array($nuevo_estado, array('confirmada', 'pendiente', 'cancelada')) ) {
        update_post_meta( $reserva_id, '_reserva_estado', $nuevo_estado );
    }

    wp_redirect( add_query_arg( array( 'admin_status' => 'success', 'tab' => 'reservas' ), wp_get_referer() ) );
    exit;
}

// C: Eliminar Reserva Completa desde el listado
add_action( 'admin_post_admin_eliminar_reserva', 'excursiones_action_admin_eliminar_reserva' );
function excursiones_action_admin_eliminar_reserva() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die('No permitido.');
    check_admin_referer( 'admin_action_nonce', '_wpnonce' );

    $reserva_id = isset($_POST['reserva_id']) ? intval($_POST['reserva_id']) : 0;

    if ( $reserva_id && get_post_type($reserva_id) === 'reservas' ) {
        wp_delete_post( $reserva_id, true ); // Forzar borrado completo saltándose la papelera
    }

    wp_redirect( add_query_arg( array( 'admin_status' => 'success', 'tab' => 'reservas' ), wp_get_referer() ) );
    exit;
}

// D: Gestión de Paradas de Autobús (Mantener redirección a pestaña correcta)
add_action( 'admin_post_admin_gestion_paradas', 'excursiones_action_admin_gestion_paradas' );
function excursiones_action_admin_gestion_paradas() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die('No permitido.');
    check_admin_referer( 'admin_action_nonce', '_wpnonce' );

    $paradas = get_option( 'excursiones_paradas_autobus', array() );
    $subaction = isset($_POST['subaction']) ? sanitize_text_field($_POST['subaction']) : '';

    if ( $subaction === 'add' ) {
        $nueva = isset($_POST['nueva_parada']) ? sanitize_text_field($_POST['nueva_parada']) : '';
        if ( ! empty($nueva) && ! in_array($nueva, $paradas) ) {
            $paradas[] = $nueva;
            update_option( 'excursiones_paradas_autobus', $paradas );
        }
    } 
    elseif ( $subaction === 'delete' ) {
        $idx = isset($_POST['delete_idx']) ? intval($_POST['delete_idx']) : -1;
        if ( isset($paradas[$idx]) ) {
            unset($paradas[$idx]);
            update_option( 'excursiones_paradas_autobus', array_values($paradas) );
        }
    }

    wp_redirect( add_query_arg( array( 'admin_status' => 'success', 'tab' => 'paradas' ), wp_get_referer() ) );
    exit;
}