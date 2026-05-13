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

    if ( ! $items ) return $content;

    return $content
        . '<div class="exc-single-meta">' . $items . '</div>'
        . '<a href="#reservar" class="exc-single-cta">Reservar plaza
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
           </a>';
} );
