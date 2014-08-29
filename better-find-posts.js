/*global jQuery, wp, _BetterFindPosts_exports */
/*exported BetterFindPosts */
var BetterFindPosts = ( function ( $ ) {
	'use strict';

	var self = {
		'nonce': '',
		'ajax_action': ''
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
	self.init = function () {
		// do stuff
	};

	$( function () {
		self.init();
	} );

	return self;
}( jQuery ) );
