<?php
/*
Plugin Name: Excursiones
Description: Plugin para gestionar excursiones con tipos, ubicaciones y detalles como precio y plazas.
Version: 4.3
Author: Francisco Javier Montelongo Costas
Text Domain: funciones-excursiones
*/

/*
   CPT EXCURSIONES
*/
function excursiones()
{

	$labels = array(
		'name'               => _x('Excursiones', 'Post Type General Name', 'funciones-excursiones'),
		'singular_name'      => _x('Excursión', 'Post Type Singular Name', 'funciones-excursiones'),
		'menu_name'          => __('Excursiones', 'funciones-excursiones'),
		'all_items'          => __('Todas las excursiones', 'funciones-excursiones'),
		'add_new_item'       => __('Añadir nueva excursión', 'funciones-excursiones'),
		'edit_item'          => __('Editar excursión', 'funciones-excursiones'),
		'view_item'          => __('Ver excursión', 'funciones-excursiones'),
	);

	$args = array(
		'label'              => __('Excursión', 'funciones-excursiones'),
		'labels'             => $labels,
		'supports'           => array('title', 'editor', 'thumbnail'),
		'public'             => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'menu_icon'          => 'dashicons-location-alt',
		'has_archive'        => true,
		'show_in_rest'       => true,
		'capability_type'    => 'post',
	);

	register_post_type('excursiones', $args);
}
add_action('init', 'excursiones', 0);


/*
   TAXONOMÍAS
*/
function excursiones_taxonomias()
{

	register_taxonomy(
		'tipo_excursion',
		array('excursiones'),
		array(
			'hierarchical' => true,
			'labels' => array(
				'name'              => __('Tipos de excursión', 'funciones-excursiones'),
				'singular_name'     => __('Tipo de excursión', 'funciones-excursiones'),
				'search_items'      => __('Buscar tipos', 'funciones-excursiones'),
				'all_items'         => __('Todos los tipos', 'funciones-excursiones'),
				'edit_item'         => __('Editar tipo', 'funciones-excursiones'),
				'add_new_item'      => __('Añadir tipo', 'funciones-excursiones'),
				'menu_name'         => __('Tipos de excursión', 'funciones-excursiones'),
			),
			'show_ui' => true,
			'show_admin_column' => true,
			'rewrite' => array('slug' => 'tipo-excursion'),
			'show_in_rest' => true,
		)
	);
}
add_action('init', 'excursiones_taxonomias', 0);


/*
   METABOX
*/
function excursiones_meta_boxes()
{

	add_meta_box(
		'excursion_detalles',
		'Detalles de la excursión',
		'excursion_detalles_callback',
		'excursiones',
		'normal',
		'default'
	);
}
add_action('add_meta_boxes', 'excursiones_meta_boxes');


/*
   CAMPOS
*/
function excursion_detalles_callback($post)
{

	wp_nonce_field('guardar_excursion', 'excursion_nonce');

	$precio = get_post_meta($post->ID, '_precio', true);
	$max = get_post_meta($post->ID, '_max_participantes', true);
	$ubicacion = get_post_meta($post->ID, '_ubicacion', true);

	echo '<label>Precio</label><br>';
	echo '<input type="number" step="0.01" min="0" name="precio" value="' . esc_attr($precio) . '" style="width:100%;" /><br><br>';

	echo '<label>Máximo participantes</label><br>';
	echo '<input type="number" min="1" name="max_participantes" value="' . esc_attr($max) . '" style="width:100%;" /><br><br>';

	echo '<label>Ubicación</label><br>';
	echo '<input type="text" name="ubicacion" value="' . esc_attr($ubicacion) . '" style="width:100%;" /><br><br>';
}


/*
   GUARDAR
*/
function guardar_excursion_meta($post_id)
{

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

	if (!isset($_POST['excursion_nonce']) || !wp_verify_nonce($_POST['excursion_nonce'], 'guardar_excursion')) return;

	if (!current_user_can('edit_post', $post_id)) return;

	if (get_post_type($post_id) !== 'excursiones') return;

	if (isset($_POST['precio'])) {
		update_post_meta($post_id, '_precio', floatval($_POST['precio']));
	}

	if (isset($_POST['max_participantes'])) {
		update_post_meta($post_id, '_max_participantes', intval($_POST['max_participantes']));
	}

	if (isset($_POST['ubicacion'])) {
		update_post_meta($post_id, '_ubicacion', sanitize_text_field($_POST['ubicacion']));
	}
}
add_action('save_post', 'guardar_excursion_meta');


/*
   HTML TARJETA
*/
function excursiones_tarjeta_html($post_id, $title)
{

	$precio = get_post_meta($post_id, '_precio', true);
	$max = get_post_meta($post_id, '_max_participantes', true);
	$ubicacion = get_post_meta($post_id, '_ubicacion', true);

	$imagen = get_the_post_thumbnail(
		$post_id,
		'large',
		array(
			'class' => 'excursion-imagen'
		)
	);

	$html = '
	<div class="excursion-card">

		<h2 class="excursion-card-title">' . $title . '</h2>

		<div class="excursion-imagen-container">
			' . $imagen . '
		</div>

		<div class="excursion-datos">';

	if ($precio !== '') {
		$html .= '<p><strong>Precio:</strong> ' . esc_html($precio) . ' €</p>';
	}

	if ($max !== '') {
		$html .= '<p><strong>Máximo participantes:</strong> ' . esc_html($max) . '</p>';
	}

	if ($ubicacion !== '') {
		$html .= '<p><strong>Ubicación:</strong> ' . esc_html($ubicacion) . '</p>';
	}

	$html .= '
		</div>
	</div>';

	return $html;
}


/*
   TARJETAS EN ARCHIVE
*/
add_filter('the_title', function ($title, $post_id) {

	if (is_admin()) return $title;

	if (
		get_post_type($post_id) === 'excursiones' &&
		is_post_type_archive('excursiones')
	) {

		return excursiones_tarjeta_html($post_id, $title);
	}

	return $title;
}, 10, 2);


/*
   FRONTEND SINGLE
*/
function excursiones_mostrar_datos_frontend($content)
{

	if (!is_singular('excursiones')) {
		return $content;
	}

	return $content;
}
add_filter('the_content', 'excursiones_mostrar_datos_frontend');


/*
   ESTILOS
*/
add_action('wp_head', function () {

	if (!is_post_type_archive('excursiones')) {
		return;
	}

echo '
<style>

/* GRID */
.post-type-archive-excursiones .wp-block-post-template {
	display: grid !important;
	grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
	gap: 12px !important;
	padding: 0 !important;
	margin-top: 30px !important;
	align-items: stretch;
}

/* ITEM */
.post-type-archive-excursiones .wp-block-post-template li {
	list-style: none !important;
	margin: 0 !important;
	padding: 0 !important;
	display: flex;
	height: 100%;
}

/* OCULTAR TITULO ORIGINAL DE WP */
.post-type-archive-excursiones .wp-block-post-title {
	display: none !important;
}

/* OCULTAR IMAGEN DEL THEME (evita duplicados) */
.post-type-archive-excursiones .wp-block-post-featured-image {
	display: none !important;
}

/* IMAGENES GENERALES CONTROLADAS */
.post-type-archive-excursiones img {
	display: block;
	max-width: 100%;
	height: auto;
}

/* TARJETA */
.excursion-card {
	background: #ffffff;
	border: 2px solid #000000;
	border-radius: 18px;
	padding: 18px;
	width: 100%;
	height: 100%;
	min-height: 520px;
	display: flex;
	flex-direction: column;
	box-sizing: border-box;
	transition: all .25s ease;
	box-shadow: 0 4px 10px rgba(0,0,0,.08);
	overflow: hidden;
}

/* HOVER */
.excursion-card:hover {
	transform: translateY(-5px);
	border-color: #ff7a00;
	box-shadow: 0 14px 24px rgba(0,0,0,.18);
}

/* TITULO */
.excursion-card-title {
	font-size: 28px;
	font-weight: 700;
	line-height: 1.2;
	margin-bottom: 15px;
	color: #111827;
	min-height: 70px;
}

/* CONTENEDOR IMAGEN */
.excursion-imagen-container {
	width: 100%;
	height: 220px;
	margin-bottom: 18px;
	border-radius: 14px;
	overflow: hidden;
	position: relative;
}

/* IMAGEN */
.excursion-imagen {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
}

/* DATOS */
.excursion-datos {
	margin-top: auto;
}

.excursion-datos p {
	font-size: 17px;
	margin-bottom: 10px;
	color: #374151;
	line-height: 1.4;
}

.excursion-datos strong {
	color: #111827;
}

/* FECHAS WP OCULTAS */
.post-type-archive-excursiones time,
.post-type-archive-excursiones .wp-block-post-date,
.post-type-archive-excursiones .entry-meta,
.post-type-archive-excursiones .posted-on,
.post-type-archive-excursiones .entry-date {
	display: none !important;
}

/* RESPONSIVE */
@media (max-width: 1400px) {
	.post-type-archive-excursiones .wp-block-post-template {
		grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
	}
}

@media (max-width: 1000px) {
	.post-type-archive-excursiones .wp-block-post-template {
		grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
	}
}

@media (max-width: 650px) {
	.post-type-archive-excursiones .wp-block-post-template {
		grid-template-columns: 1fr !important;
	}
}

</style>
';
});

?>