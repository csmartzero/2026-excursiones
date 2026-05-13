<?php
/**
 * Plantilla de archivo para el CPT Excursiones.
 * Se carga via template_include en frontend.php.
 * Compatible con temas clásicos y de bloques (FSE).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<main class="exc-archive" id="exc-main">

    <?php if ( have_posts() ) : ?>

        <div class="exc-archive__header">
            <h1 class="exc-archive__heading">
                <?php post_type_archive_title(); ?>
            </h1>

            <?php
            /* Filtro por taxonomía (opcional, muestra links de tipo) */
            $tipos = get_terms( array( 'taxonomy' => 'tipo_excursion', 'hide_empty' => true ) );
            if ( $tipos && ! is_wp_error( $tipos ) ) : ?>
                <nav class="exc-filter-nav" aria-label="Filtrar por tipo">
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'excursiones' ) ); ?>"
                       class="exc-filter-btn <?php echo ! is_tax() ? 'is-active' : ''; ?>">
                        Todos
                    </a>
                    <?php foreach ( $tipos as $tipo ) : ?>
                        <a href="<?php echo esc_url( get_term_link( $tipo ) ); ?>"
                           class="exc-filter-btn <?php echo is_tax( 'tipo_excursion', $tipo->term_id ) ? 'is-active' : ''; ?>">
                            <?php echo esc_html( $tipo->name ); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
        </div>

        <div class="exc-grid">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php echo excursiones_tarjeta_html( get_the_ID(), get_the_title() ); ?>
            <?php endwhile; ?>
        </div>

        <div class="exc-pagination">
            <?php
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => '← Anterior',
                'next_text' => 'Siguiente →',
            ) );
            ?>
        </div>

    <?php else : ?>

        <p class="exc-empty">No hay excursiones disponibles en este momento.</p>

    <?php endif; ?>

</main>

<?php get_footer(); ?>
