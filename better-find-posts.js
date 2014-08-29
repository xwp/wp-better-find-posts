/*global jQuery, wp, _BetterFindPosts_exports */
/*exported BetterFindPosts */
var BetterFindPosts = ( function ( $ ) {
	'use strict';

	var self = {
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
	self.get = function ( args ) {
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
	};

	/**
	 * Set up a new better-find-posts control
	 */
	self.create = function ( args ) {
		args = _.defaults( args || {}, {
			searchFormContainer: '',
			resultsTableContainer: ''
		} );

		$( args.searchFormContainer ).append( self.templates.searchForm() );
	};

	$( function () {
		self._init();
	} );

	return self;
}( jQuery ) );
