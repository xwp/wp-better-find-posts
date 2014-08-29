# Better Find Posts

Example code:

```php

add_action( 'add_meta_boxes', function () {
	$id = 'linked-post-metabox';
	$title = __( 'Linked Post' );
	$post_type = 'post';
	$context = 'normal';
	$priority = 'high';
	$callback = 'linked_post_metabox';
	add_meta_box( $id, $title, $callback, $post_type, $context, $priority );
} );

function linked_post_metabox() {
	\Better_Find_Posts::instance()->enqueue();
	?>
	<div class="search-form"></div>
	<div class="results-table"></div>
	<?php
	add_action( 'admin_footer', 'linked_post_footer_js', 20 );
}

function linked_post_footer_js() {
	wp_print_scripts( array( 'better-find-posts' ) );
	?>
	<script>
	BetterFindPosts.ready.done( function () {
		var control = BetterFindPosts.create( {
			searchFormContainer: '#linked-post-metabox .search-form',
			resultsTableContainer: '#linked-post-metabox .results-table',
			defaultQueryArgs: {
				'post_type': 'post',
				'post_status': 'publish'
			},
			hiddenColumns: [ 'type', 'status', 'time' ]
		} );

		// @todo Need some built-in events here

		control.resultsTableContainer.on( 'click', 'input', function () {
			var post_id = jQuery( this ).val();
			// Do something with this
		} );
	} );
	</script>
	<?php
}

```
