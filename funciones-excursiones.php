<?php
/*
Plugin Name: Excursiones
Description: Plugin para gestionar excursiones con tipos, ubicaciones y detalles como precio y plazas.
Version: 3.6
Author: Francisco Javier Montelongo Costas
Text Domain: funciones-excursiones
*/

/*
   CPT EXCURSIONES
*/
function excursiones() {

	$labels = array(
		'name' => _x('Excursiones', 'Post Type General Name', 'funciones-excursiones'),
		'singular_name' => _x('Excursión', 'Post Type Singular Name', 'funciones-excursiones'),
		'menu_name' => __('Excursiones', 'funciones-excursiones'),
		'all_items' => __('Todas las excursiones', 'funciones-excursiones'),
		'add_new_item' => __('Añadir nueva excursión', 'funciones-excursiones'),
		'edit_item' => __('Editar excursión', 'funciones-excursiones'),
		'view_item' => __('Ver excursión', 'funciones-excursiones'),
	);

	$args = array(
		'label' => __('Excursión', 'funciones-excursiones'),
		'labels' => $labels,
		'supports' => array('title', 'editor', 'thumbnail'),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_icon' => 'dashicons-location-alt',
		'has_archive' => true,
		'show_in_rest' => true,
		'capability_type' => 'post',
	);

	register_post_type('excursiones', $args);
}
add_action('init', 'excursiones', 0);


/*
   TAXONOMÍAS
*/
function excursiones_taxonomias() {

	register_taxonomy(
		'tipo_excursion',
		array('excursiones'),
		array(
			'hierarchical' => true,
			'labels' => array(
				'name'          => __('Tipos de excursión', 'funciones-excursiones'),
				'singular_name' => __('Tipo de excursión', 'funciones-excursiones'),
				'search_items'  => __('Buscar tipos', 'funciones-excursiones'),
				'all_items'     => __('Todos los tipos', 'funciones-excursiones'),
				'edit_item'     => __('Editar tipo', 'funciones-excursiones'),
				'add_new_item'  => __('Añadir tipo', 'funciones-excursiones'),
				'menu_name'     => __('Tipos de excursión', 'funciones-excursiones'),
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
function excursiones_meta_boxes() {
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


function excursion_detalles_callback($post) {

	wp_nonce_field('guardar_excursion', 'excursion_nonce');

	$precio = get_post_meta($post->ID, '_precio', true);
	$max = get_post_meta($post->ID, '_max_participantes', true);
	$ubicacion = get_post_meta($post->ID, '_ubicacion', true);

	echo '<label>Precio</label><br>';
	echo '<input type="number" step="0.01" min="0" name="precio" value="' . esc_attr($precio) . '" /><br><br>';

	echo '<label>Máximo participantes</label><br>';
	echo '<input type="number" min="1" name="max_participantes" value="' . esc_attr($max) . '" /><br><br>';

	echo '<label>Ubicación</label><br>';
	echo '<input type="text" name="ubicacion" value="' . esc_attr($ubicacion) . '" /><br><br>';
}


/*
   GUARDADO SEGURO
*/
function guardar_excursion_meta($post_id) {

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
   FUNCIÓN HTML DATOS
*/
function excursiones_datos_html($post_id) {

	$precio = get_post_meta($post_id, '_precio', true);
	$max = get_post_meta($post_id, '_max_participantes', true);
	$ubicacion = get_post_meta($post_id, '_ubicacion', true);

	$html = '<div class="excursion-datos">';

	if ($precio !== '') {
		$html .= '<p><strong>Precio:</strong> ' . esc_html($precio) . ' €</p>';
	}

	if ($max !== '') {
		$html .= '<p><strong>Máximo participantes:</strong> ' . esc_html($max) . '</p>';
	}

	if ($ubicacion !== '') {
		$html .= '<p><strong>Ubicación:</strong> ' . esc_html($ubicacion) . '</p>';
	}

	$html .= '</div>';

	return $html;
}


/*
   AÑADIR DATOS EN ARCHIVE (debajo del título)
*/
add_filter('the_title', function($title, $post_id) {

	if (is_admin()) return $title;

	if (get_post_type($post_id) === 'excursiones' && is_post_type_archive('excursiones')) {
		return $title . excursiones_datos_html($post_id);
	}

	return $title;

}, 10, 2);


/*
   FRONTEND SINGLE
*/
function excursiones_mostrar_datos_frontend($content) {

	if (!is_singular('excursiones')) {
		return $content;
	}

	return $content . excursiones_datos_html(get_the_ID());
}
add_filter('the_content', 'excursiones_mostrar_datos_frontend');


/*
   OCULTAR FECHA EN ARCHIVE
*/
add_action('wp_head', function() {
	if (is_post_type_archive('excursiones')) {
		echo '<style>
			.entry-date, .posted-on, time {
				display:none !important;
			}
		</style>';
	}
});

?>