/*global jQuery, wp, _BetterFindPosts_exports */
/*exported BetterFindPosts */
var BetterFindPosts = ( function ( $ ) {
	'use strict';

	var self = {
		control_count: 0,
		ready: $.Deferred(),
		nonce: '',
		ajax_action: '',
		templates: {
			searchForm: null,
			resultsTable: null
		}
	};
	$.extend( self, _BetterFindPosts_exports );
	window._BetterFindPosts_exports = null;

	/**
	 * Query WordPress for posts with the select query args (post_type, post_status, s)
	 *
	 * @param {Object} args Query
	 * @return {$.Deferred}
	 */
	self.query = function ( args ) {
		var xhr;
		args = args || {};
		args.nonce = self.nonce;

		xhr = wp.ajax.send( self.ajax_action, {
			'type': 'GET',
			'data': args
		} );

		return xhr;
	};

	/**
	 * Set up BetterFindPosts upon DOM ready.
	 */
	self._init = function () {
		self.templates.searchForm = wp.template( 'better-find-posts-search-form' );
		self.templates.resultsTable = wp.template( 'better-find-posts-results-table' );
		self.ready.resolve();
	};

	/**
	 * Set up a new better-find-posts control
	 */
	self.create = function ( args ) {
		var control;

		self.control_count += 1;
		control = _.defaults( args || {}, {
			id: self.control_count,
			searchFormContainer: '',
			resultsTableContainer: '',
			selectedPosts: [],
			defaultQueryArgs: {},
			hiddenColumns: []
		} );

		control.searchForm = $( self.templates.searchForm( { controlId: control.id } ) );
		control.searchFormContainer = $( args.searchFormContainer );
		control.resultsTableContainer = $( args.resultsTableContainer );
		control.searchFormContainer.append( control.searchForm );

		control.searchForm.on( 'submit', function ( e ) {
			var value;
			e.preventDefault();

			value = $( this ).find( '[type=search]' ).val();
			control.searchForm.addClass( 'loading' );
			control.resultsTableContainer.find( '> table' ).addClass( 'loading' );

			control.request = self.query( _.defaults( { s: value }, control.defaultQueryArgs ) );
			control.request.always( function () {
				control.searchForm.removeClass( 'loading' );
			} );
			// @todo what if fail?

			control.request.done( function ( posts ) {
				// @todo what if empty?

				var resultsTable = $( self.templates.resultsTable( {
					posts: posts,
					selected: [],
					controlId: control.id,
					hiddenColumns: control.hiddenColumns
				} ) );
				control.resultsTableContainer.empty();

				control.resultsTableContainer.append( resultsTable );
			} );
		} );

		return control;
	};

	$( function () {
		self._init();
	} );

	return self;
}( jQuery ) );
