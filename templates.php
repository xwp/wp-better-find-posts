<script type="text/html" id="tmpl-better-find-posts-search-form">
	<#
	data = data || {};
	#>
	<form class="better-find-posts-search-form">
		<label class="screen-reader-text" for="better-find-posts-search-{{ data.controlId }}"><?php _e( 'Search', 'better-find-posts' ); ?></label>
		<input type="search" id="better-find-posts-search-{{ data.controlId }}" class="better-find-posts-input" name="s" value="{{ data.value }}" />
		<button type="submit" class="button bitton-primary better-find-posts-search"><?php esc_html_e( 'Search', 'better-find-posts' ) ?></button>
		<span class="spinner"></span>
		<span class="message"></span>
	</form>
</script>

<script type="text/html" id="tmpl-better-find-posts-results-table">
	<#
	data = data || {};
	var inputType, selected, hiddenColumns;
	inputType = data.inputType || 'radio';
	selected = data.selected || [];
	hiddenColumns = data.hiddenColumns || [];
	#>
	<table class="better-find-posts-results-table">
		<thead>
			<tr>
				<th class="column-select"></th>
				<th class="column-title"><?php esc_html_e( 'Title', 'better-find-posts' ) ?></th>
				<# if ( -1 === jQuery.inArray( 'type', hiddenColumns ) ) { #>
					<th class="column-type"><?php esc_html_e( 'Type', 'better-find-posts' ) ?></th>
				<# } #>
				<# if ( -1 === jQuery.inArray( 'type', hiddenColumns ) ) { #>
					<th class="column-status"><?php esc_html_e( 'Status', 'better-find-posts' ) ?></th>
				<# } #>
				<# if ( -1 === jQuery.inArray( 'date', hiddenColumns ) ) { #>
					<th class="column-date"><?php esc_html_e( 'Date', 'better-find-posts' ) ?></th>
				<# } #>
				<# if ( -1 === jQuery.inArray( 'time', hiddenColumns ) ) { #>
					<th class="column-time"><?php esc_html_e( 'Time', 'better-find-posts' ) ?></th>
				<# } #>
			</tr>
		</thead>
		<tbody>
			<# _.each( data.posts, function ( post ) { #>
				<tr>
					<td class="column-select">
						<input id="better-find-posts-search-{{ data.controlId }}-{{ post.ID }}" type="{{ inputType }}" name="posts[]" value="{{ post.ID }}"
							<# if ( -1 !== jQuery.inArray( post.ID, data.selected ) ) { #>
								<# print( 'radio' === inputType ? 'selected' : 'checked' ) #>
							<# } #>
							>
					</td>
					<td class="column-title">
						<label for="better-find-posts-search-{{ data.controlId }}-{{ post.ID }}">{{ post.post_title_filtered }}</label>
					</td>
					<# if ( -1 === jQuery.inArray( 'type', hiddenColumns ) ) { #>
						<td class="column-type">
							{{ post.post_type_label }}
						</td>
					<# } #>
					<# if ( -1 === jQuery.inArray( 'type', hiddenColumns ) ) { #>
						<td class="column-status">
							{{ post.post_status_label }}
						</td>
					<# } #>
					<# if ( -1 === jQuery.inArray( 'date', hiddenColumns ) ) { #>
						<td class="column-date">
							<time datetime="{{ post.post_date_iso }}">{{ post.post_date_formatted }}</time>
						</td>
					<# } #>
					<# if ( -1 === jQuery.inArray( 'time', hiddenColumns ) ) { #>
						<td class="column-time">
							<time datetime="{{ post.post_date_iso }}">{{ post.post_time_formatted }}</time>
						</td>
					<# } #>
				</tr>
			<# } ) #>
		</tbody>
	</table>
</script>
