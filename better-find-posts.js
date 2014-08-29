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
		},
		l10n: {}
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
		control.messageElement = control.searchForm.find( '.message' );

		/**
		 *
		 */
		control.hideMessage = function () {
			control.messageElement.hide();
		};

		/**
		 *
		 * @param {string} message
		 * @param {string} [type]
		 */
		control.showMessage = function ( message, type ) {
			if ( control.messageElement.data( 'type' ) ) {
				control.messageElement.removeClass( control.messageElement.data( 'type' ) );
			}

			type = type || 'notice';
			control.messageElement.addClass( type );
			control.messageElement.data( 'type', type );
			control.messageElement.text( message );
			control.messageElement.show();
		};

		control.searchForm.on( 'submit', function ( e ) {
			var value;
			e.preventDefault();

			value = $( this ).find( '[type=search]' ).val();

			control.hideMessage();
			control.searchForm.addClass( 'loading' );
			control.resultsTableContainer.find( '> table' ).addClass( 'loading' );

			control.request = self.query( _.defaults( { s: value }, control.defaultQueryArgs ) );

			control.request.always( function () {
				control.searchForm.removeClass( 'loading' );
			} );
			control.request.fail( function ( code ) {
				control.showMessage( self.l10n[ code ] || self.l10n.server_error, 'error' );
			} );

			control.request.done( function ( posts ) {
				control.resultsTableContainer.empty();
				if ( posts.length === 0 ) {
					control.showMessage( self.l10n.no_posts_found );
					return;
				}

				var resultsTable = $( self.templates.resultsTable( {
					posts: posts,
					selected: [],
					controlId: control.id,
					hiddenColumns: control.hiddenColumns
				} ) );

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
