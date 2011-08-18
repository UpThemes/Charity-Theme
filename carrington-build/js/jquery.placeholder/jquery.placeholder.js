/**
 * Placeholder
 * Crowd Favorite
 * @requires jQuery v1.2 or above
 *
 * Version: 1.0.1
 * Patches the HTML5 placeholder atttribute functionality for browsers that don't support it
 */
;(function($) {
	$.fn.placeholder = function(settings) {
		// Merge default options and user options
		var opts = $.extend({}, $.fn.placeholder.settings, settings);

		/* Are we using the placholder attribute?
		 * Does the browser support placeholders?
		 * Should we run if it does?
		 * If no, exit out.
		 */
		if (opts.attribute == 'placeholder' && opts.disableIfSupported == true && 'placeholder' in document.createElement('input')) {
			return null;
		};

		// Run placholders
		this.each(function() {
			var _this = $(this);

			prepPlaceholder(_this, opts);
			_this.focus(function(){
				togglePlaceholder(_this, opts);
			});
			_this.blur(function(){
				togglePlaceholder(_this, opts);
			});
		});
		clearPlaceholdersOnSubmit(opts);
	};

	/**
	 * Plugin settings defaults
	 * Set in separate object so they are public
	 */
	$.fn.placeholder.settings = {
		classname: 'cfp-placeholder',
		attribute: 'placeholder',
		disableIfSupported: true
	};

	/**
	 * Call this to enable standard-style HTML5 placeholders globally
	 */
	$.placeholders = function(settings) {
		$('input[placeholder]').placeholder(settings);
	};

	/* Private helper functions */

	function prepPlaceholder(el, opts) {
		var c = opts.classname;
		if(el.val() == '' || el.val() == el.attr(opts.attribute)) {
			el.addClass(c);
			if(el.val() == '') {
				el.attr('value', el.attr(opts.attribute));
			};
		} else {
			el.removeClass(c);
		};
	};
	function togglePlaceholder(el, opts) {
		// Check if the input already has a value...
		if(el.val() && el.val() != el.attr(opts.attribute)) {
			return false;
		};
		if(el.val() == el.attr(opts.attribute)) {
			el.attr('value', '');
		} else if(!el.val()) {
			el.attr('value', el.attr(opts.attribute));
		};
		el.toggleClass(opts.classname);
	};
	function clearPlaceholdersOnSubmit(opts) {
		$('form').submit(function() {
			clearPlaceholders(this, opts);
		});
	};
	clearPlaceholders = function(form, opts) {
		$(form).find('input').each(function(){
			_this = $(this);
			if(_this.val() == _this.attr(opts.attribute)) {
				_this.attr('value', '');
			};
		});
	};
})(jQuery);