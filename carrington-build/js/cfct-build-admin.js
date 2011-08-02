/*
 * hoverFix 1.0
 * Crowd Favorite
 */
(function($) {
	$.fn.moduleHoverFix = function(classname) {
		var c = classname || 'hover';
		this.hover(
			function() {
				var _this = $(this);
				if (_this.parents('div.ui-sortable').attr('aria-disabled') == 'false') {
					_this.addClass(c);
				}
			},
			function() {
				$(this).removeClass(c);
			}
		);
	};
})(jQuery);

// Carrington Build Admin Functions
;(function($) {

// Admin Init	
	cfctAdminEditInit = function() {
		// process UI tabs
		var cfctInitialActiveTab = null;
		$('#cfct-build-tabs li a',cfct_build).each(function() {
			var _this = $(this);
			// add click event to tab
			_this.click(function() {
				cfctTabSwitch(_this);
				return false;
			});
			var _thisHref = $(_this.attr('href'));
			// hide tab content if its not the active tab
			if (_this.parents('li').hasClass('active')) {
				_thisHref.show();
				cfctInitialActiveTab = _this.attr('href');
			}
		});
		cfctPrepMediaGallery(cfctInitialActiveTab == '#cfct-build-data' ? 'build' : 'wordpress');
		
		// init sortables
		$('#cfct-sortables').sortable({
			handle:cfct_builder.opts.row_handle,
			items:'.cfct-row',
			placeholder:'cfct-row-draggable-placeholder',
			forcePlaceholderSize:true,
			opacity:0.5,
			axis:'y',
			cancel:'.cfct-row-delete',
			update:cfct_builder.updateRowOrder
		});
				
		// insert in to DOM
		$('#titlediv').after(cfct_build);
		cfct_builder.bindClickables();
		
		// dialog helper for window resize
		$(window).resize(function() {
			cfct_builder.resizeDOMWindow();
		});
		
		// init options box
		$('.cfct-build-options-header').click(function() {
			cfct_builder.toggleOptions();
			return false;
		});
	};

// Tab Switching
	cfctTabSwitch = function(clicked) {
		$('body').trigger('click');
		var _this = $(clicked);
		var _tgt = _this.attr('href');
		
		if ( $(_tgt).is(':visible') ) {
			return false;
		}
		
		var switchMessage = "Are you sure you want to switch editing modes?\nSwitching tabs will erase your progress in the current tab and CANNOT be undone.";
				
		switch (true) {
			case (_tgt == '#postdivrich' || _tgt == '#postdiv') && !cfct_builder.hasRows():
			case _tgt == '#cfct-build-data' && $('#content').val().length == 0:
				// do switch, no prompt
				cfctDoTabSwitch(_this);
				break;
			case (_tgt == '#postdivrich' || _tgt == '#postdiv') && cfct_builder.hasRows():
			case _tgt == '#cfct-build-data' && $('#content').val().length > 0:
				// prompt for switch
				if (confirm(switchMessage)) {
					cfctDoTabSwitch(_this);
				}
				break;
		}
		
		return false;
	};
	
	cfctDoTabSwitch = function(_this) {
		_this.parents('li').addClass('active').siblings().each(function() {
			_l = $(this);
			_l.removeClass('active');
			$(_l.children('a').attr('href')).hide();
		});
		
		var tgt = _this.attr('href');
		cfct_builder.fetch('reset_edit_state',{},'reset-edit-state');
		switch (tgt) {
			case '#postdivrich':
			case '#postdiv':
				cfctClearWordPressData();
				cfctPrepMediaGallery('wordpress');
				$(tgt).show();
				cfctClearBuildData();
				break;
			case '#cfct-build-data':
				cfctClearBuildData();
				cfctPrepMediaGallery('build');
				cfct_builder.showWelcome();
				$(tgt).show();
				cfctClearWordPressData();
				break;
		}
		cfct_builder.toggleOptionsMenu('hide');
	};
	
	cfctClearBuildData = function() {
		$('#cfct-sortables > div').not('#cfct-welcome-chooser').remove();
		cfctClearWordPressData();
	};
	
	cfctClearWordPressData = function() {		
		if (cfct_builder.opts.rich_text && $('#editor-toolbar a#edButtonPreview').hasClass('active')) {
			var edC = tinyMCE.activeEditor;
			edC.setContent('');
			edC.save();	
		}
		else {
			$('#content').val('');
		}
	};
	
	cfctPrepMediaGallery = function(moveTo) {
		var mediaButtons = $('#media-buttons');
		switch (moveTo) {
			case 'wordpress':
				$('#editor-toolbar').append(mediaButtons);
				break;
			case 'build':
				$('#build-editor-toolbar').append(mediaButtons);
				break;
		}
	};

// Messages
	window.cfct_messenger = {};

	cfct_messenger.opts = {
		messages:{},
		current_message:'',
		last_message:'',
		message_div:'#cfct-build-messages',
		message_timeout:'5000',
		message_timeout_id:'',
		message_type_classes:{
			info:'cfct-message-info',
			warning:'cfct-message-warning',
			error:'cfct-message-error',
			confirm:'cfct-message-confirm',
			loading:'cfct-message-loading'
		}
	};

	cfct_messenger.setMessage = function(message,type,expire) {
		clearTimeout(this.message_timeout_id);
		
		$(this.opts.message_div)
			.attr('class',this.opts.message_type_classes[type || 'info'])
			.html('<span class="cfct-message-content">' + message + '</span>');
		
		if (expire !== false) {
			this.setExpire(this.opts.message_timeout);
		}
	};

	cfct_messenger.setLoading = function(message) {
		var _message_text = message || 'Loading.';
		this.setMessage(_message_text,'loading',false);
	};

	cfct_messenger.clearMessage = function() {
		var _tgt = $(cfct_messenger.opts.message_div);
		_tgt.children('span.cfct-message-content').fadeOut('fast',function(){
			_tgt.attr('class','').html('');
		});
	};

	cfct_messenger.setExpire = function(timeout) {
		this.opts.message_timeout_id = setTimeout(this.clearMessage,timeout || this.opts.message_timeout);
	};
	
// Builder
	window.cfct_builder = {};
	
	cfct_builder.opts = {
		ajax_url:ajaxurl, // ajaxurl is pre-defined by wp-admin for autosave purposes... we may or may not want to define this ourselves
		dialogs:{},
		sortables:{
			sender:null,
			receiver:null
		},
		moduleSortables:null,
		DOMWindow_defaults:{
			windowSourceID:'#cfct-popup-placeholder',
			overlay:0,
			borderSize:0,
			windowBGColor:'none',
			windowPadding: 0,
			positionType:'centered',
			width:800, // static at 800
			height:650, // gets updated at box generation
			overlay:1,
			overlayOpacity:'65',
			modal:1
		},
		row_handle:'.cfct-row-handle',
		module_save_callbacks:{},
		module_load_callbacks:{},
		rich_text:true
	};
	
// DOMWindow Helpers
	// recalculate the DOMWindow width, no smaller than 500
	// cfct_builder.DOMWindowWidth = function() {
	// 	var w = $(window).width()*0.6;
	// 	return w < 500 ? 500 : w;
	// };

	// recalculate DOMWindow height
	cfct_builder.DOMWindowHeight = function() {
		return $(window).height()*0.8;
	};

	cfct_builder.resizeDOMWindow = function() {
		//this.opts.DOMWindow_defaults.width = this.DOMWindowWidth();
		this.opts.DOMWindow_defaults.height = this.DOMWindowHeight();
	};
	cfct_builder.resizeDOMWindow();
	
	cfct_builder.showLoadingDialog = function() {
		if ($('#DOMWindow').not(':visible')) {
			$(this.opts.dialogs.popup_wrapper).html(cfct_builder.spinner());
			$.openDOMWindow(this.opts.DOMWindow_defaults);
		}
	};
	
// Welcome
	cfct_builder.showWelcome = function() {
		cfct_builder.opts.welcome_removed = false;
		
		var _welcome = $('#cfct-welcome-chooser');
		var _rowchooser = $('#cfct-sortables-add-container');
		
		$('#cfct-welcome-faux-bottom-rows,#cfct-welcome-splash',_welcome).show();
		_rowchooser.hide();
		_welcome.show();
		
		// choose build
		$('#cfct-start-build').unbind().click(function() {
			$('body').trigger('click');
			$('#cfct-welcome-splash').hide();
			$('#cfct-welcome-faux-bottom-rows').slideUp('normal',function() {
				_rowchooser.fadeIn('fast',function(){
					cfct_builder.toggleRowSelect('show');
				});
			});
			return false;
		});
		
		// choose template
		$('#cfct-start-template-chooser').unbind().click(function() {
			$('body').trigger('click');
			$('#cfct-welcome-splash').hide();
			$('#cfct-welcome-templates').show();
			return false;
		});
		$('#cfct-choose-template-cancel').unbind().click(function() {
			$('body').trigger('click');
			$('#cfct-welcome-templates').hide();
			$('#cfct-welcome-splash').show();
			return false;
		});
		
		// add template
		$('#cfct-welcome-templates li a.cfct-template-name').click(function() {
			$('body').trigger('click');
			var _this = $(this);
			cfct_builder.insertTemplate(_this.attr('href').slice(_this.attr('href').indexOf('#')+1));
			return false;
		});
		
		return true;
	};
	
	cfct_builder.hideWelcome = function() {
		var _welcome = $('#cfct-welcome-chooser');
		_welcome.slideUp('fast');
		cfct_builder.opts.welcome_removed = true;
	};
	
// Template insert
	cfct_builder.insertTemplate = function(template_id) {
		if (jQuery('#post_ID').val() < 0) {
			cfct_builder.initPost('insertTemplate',template_id);
			return false;
		}		
		cfct_builder.fetch('insert_template',{ 'template_id':template_id },'insert-template-response');
		return true;
	};

	$(cfct_builder).bind('insert-template-response',function(evt,res) {
		if (!res.success) {
			return false;
		}

		if (!cfct_builder.opts.welcome_removed) {
			cfct_builder.hideWelcome();
		}

		$('#cfct-sortables',cfct_build).append($(res.html)).sortable('refresh');
		$('#cfct-choose-template').slideUp('normal',function() {
			cfct_builder.toggleRowSelect('open');
			$('#cfct-sortables-add-container').slideDown();
		});
		cfct_builder.bindClickables();
		cfct_builder.toggleOptionsMenu('show');
		return true;
	});
	
// Editing helper
	cfct_builder.editing_items = {
		row_id:null,
		block_id:null,
		module_id:null,
		module_name:null
	};

	cfct_builder.editing = function(params) {
		if (params === 0) {
			// reset
			for (i in this.editing_items) {
				this.editing_items[i] = null;
			}
		}
		else {
			// add to
			for (i in params) {
				this.editing_items[i] = params[i];
			}
		}
		return true;
	};
	
// Builder Ajax	
	cfct_builder.fetch = function(fn, data, successTrigger, beforeTrigger) {
		data.post_id = $('#post_ID').val();
		opts = {
			url:this.opts.ajax_url,
			type:'POST',
			async:true,
			cache:false,
			dataType:'json',
			data:{
				action:'cfbuild_fetch',
				func:fn,
				args:(typeof Prototype == 'object' ? Object.toJSON(data) : JSON.stringify(data)) // prototype.js fix - use Prototype's JSON encoder if present - it conflicts with json2.js
			},
			beforeSend: function(request) {
				$(cfct_builder).trigger(beforeTrigger || 'ajaxDoBefore',request);
				return; 
			},
			success: function(response) { 
				$(cfct_builder).trigger(successTrigger || 'ajaxSuccess',response);
				return; 
			},
			error: function(xhr,textStatus,e) {
				switch(textStatus) {
					case 'parsererror':
						var _errstring = $('<pre />').text(xhr.responseText);
						var _html = '<p><b>Parse Error in data returned from server</b>' +
									' <a href="#" onclick="cfct_builder.toggleAjaxErrorString(); return false">toggle</a></p>' +
									'<pre class="cfct-ajax-error-string" style="display: none;">' + _errstring.html() + '</pre>';
						cfct_builder.doError({
							html:_html,
							message: 'parsererror'
						});
						break;
					default:
						cfct_builder.doError({
							html:'<b>Invalid response from server during Ajax Request</b>',
							message:'invalidajax'
						});
				}
				return; 
			}
		};
		$.ajax(opts);
	};

// Error Processing	
	cfct_builder.doError = function(ret, callback) {
		$('#cfct-error-notice-message',this.opts.dialogs.error_dialog).html(ret.html);
		this.opts.dialogs.popup_wrapper.html(this.opts.dialogs.error_dialog);
		
		if ($('#DOMWindow').not(':visible')) {
			$.openDOMWindow(this.opts.DOMWindow_defaults);
		}		
		this.prepErrorActions(callback);
				
		return true;
	};
	
	cfct_builder.prepErrorActions = function(callback) {
		$('#cfct-error-notice-close',this.opts.dialogs.error_dialog).click(function() {
			if (callback) {
				callback.apply();
			}
			
			$.closeDOMWindow();
			return false;
		});
		return true;
	};
	
	cfct_builder.toggleAjaxErrorString = function() {
		$('.cfct-ajax-error-string').slideToggle();
	};

// Reordering Rows	
	cfct_builder.updateRowOrder = function(event,ui) {
		var items = $('#cfct-sortables').sortable('toArray');
		cfct_builder.fetch('reorder_rows',{
			order:items.toString()
		},'reorder-rows-response');
	};
	
	$(cfct_builder).bind('reorder-rows-response',function(evt,result) {
		if (!result.success) {
			cfct_builder.doError(ret);
			return false;
		}
		cfct_messenger.setMessage('Row Order Updated','confirm');
		return true;
	});

// Reordering Modules
	cfct_builder.initSortables = function() {
		this.opts.moduleSortables = $('.cfct-block-modules').sortable('destroy').each(function() {
			$(this).sortable({
				items:'.cfct-module',
				opacity:0.4,
				placeholder:'cfct-module-draggable-placeholder',
				helper: 'clone',
				revert: 150,
				forcePlaceholderSize:true,
				remove:function() {
					cfct_builder.opts.sortables.sender = this;
				},
				receive:function() {
					cfct_builder.opts.sortables.receiver = this;
				},
				stop:cfct_builder.updateModuleOrderEnd,
				connectWith:'.cfct-block-modules'
			});
		});
		this.enableSortables();
	};
	
	cfct_builder.updateModuleOrderEnd = function() {
		cfct_builder.disableSortables();
		
		var blocks = {};
		cfct_builder.opts.moduleSortables.each(function(){
			var _this = $(this);
			blocks[_this.parents('td').attr('id')] = _this.sortable('toArray');
		});

		cfct_builder.fetch('reorder_modules',{
			order:blocks
		},'reorder-modules-response');

	};
	
	$(cfct_builder).bind('cfctAjaxError', function(responseText) {
		cfct_builder.enableSortables();
	});
	
	$(cfct_builder).bind('reorder-modules-response',function(evt,result) {
		cfct_builder.enableSortables();

		if (!result.success) {
			cfct_builder.doError(result, function() {
				$(cfct_builder.opts.sortables.sender).sortable('cancel');
			});
			return false;
		}
		
		cfct_messenger.setMessage('Module Order Updated','confirm');
		return true;
	});

	cfct_builder.disableSortables = function() {
		cfct_builder.opts.moduleSortables.sortable('disable');
		$('#cfct-sortables').append(
			$('<div class="cfct-reorder-status">')
				.append($('<div class="cfct-reorder-status-wrapper" />')
					.css({
						'padding-top':($('#cfct-sortables').height() / 3) + 'px',
						'height':$('#cfct-sortables').height() + 'px'
					})
					.append('<div class="cfct-reorder-overlay" />', cfct_builder.spinner())
				)
		);
	};
	
	cfct_builder.enableSortables = function() {
		cfct_builder.opts.moduleSortables.sortable('enable');
		$('#cfct-sortables .cfct-reorder-status').remove();
	};

// Add Row Functions
	cfct_builder.addRow = function() {
		this.toggleRowSelect();
	};
	
	cfct_builder.insertRow = function(row_type) {
		this.toggleRowSelect('hide');
		if (!cfct_builder.opts.welcome_removed) {
			cfct_builder.hideWelcome();
		}
		
		return $('#cfct-loading-row').slideDown('fast',function() {
			if ($('#post_ID').val() < 0) {
				cfct_builder.initPost('insertRow',row_type);
				return false;
			}
			cfct_builder.fetch('new_row',{type:row_type},'do-insert-row');
			return true;
		});
	};
	
	$(cfct_builder).bind('do-insert-row',function(evt,row) {
		if (!row.success) {
			cfct_builder.doError(row);
			return false;
		}
		
		$('#cfct-loading-row').hide();
		$('#cfct-sortables',cfct_build).append($(row.html)).sortable('refresh');
		
		cfct_builder.bindClickables();
		$('#cfct-build').removeClass('new');
	
		cfct_messenger.setMessage('Row Saved','confirm');
		$(cfct_builder).trigger('new-row-inserted', row);
		return true;	
	});

// Remove Row Functions	
	cfct_builder.confirmRemoveRow = function(row) {
		$('#cfct-delete-row-id',this.opts.dialogs.delete_row).val(row.attr('id'));
		
		// pop dialog
		this.opts.dialogs.popup_wrapper.html(this.opts.dialogs.delete_row);
		$.openDOMWindow(this.opts.DOMWindow_defaults);

		// bind actions
		$('#cfct-delete-row-confirm',this.opts.dialogs.delete_row).click(function() {
			cfct_builder.doRemoveRow( $('#'+$('#cfct-delete-row-id').val()) );
		});
		$('#cfct-delete-row-cancel',this.opts.dialogs.delete_row).click(function() {
			$.closeDOMWindow();
			return false;
		});
		$(cfct_builder).trigger('confirm-remove-row');
	};
	
	cfct_builder.doRemoveRow = function(row) {		
		var _row = $(row);
		cfct_builder.editing({
			'row_id':_row.attr('id')
		});
		cfct_builder.showPopupActivityDiv(cfct_builder.opts.dialogs.delete_row);
		
		var data = {
			row_id:_row.attr('id')
		};
		cfct_builder.fetch('delete_row',data,'do-remove-row-response');
	};
	
	$(cfct_builder).bind('do-remove-row-response',function(evt,ret) {
		if (!ret.success) {
			cfct_builder.doError(ret);
			return false;
		}
		$('#cfct-sortables #' + cfct_builder.editing_items.row_id,cfct_build).slideUp('fast',function() {
			$(this).remove();			
			$.closeDOMWindow();
			cfct_builder.hidePopupActivityDiv(cfct_builder.opts.dialogs.delete_row);
			$(cfct_builder).trigger('row-removed');
		});
		
		cfct_messenger.setMessage('Row Deleted','confirm');
		cfct_builder.editing(0);
		return true;
	});

// Template Save
	cfct_builder.saveAsTemplate = function() {
		$(this.opts.dialogs.save_template).find('input[type="text"],textarea').val('');
		$(this.opts.dialogs.popup_wrapper).html(this.opts.dialogs.save_template);
		$.openDOMWindow(this.opts.DOMWindow_defaults);

		$('.cancel',this.opts.dialogs.save_template).click(function() {
			cfct_builder.editing(0);
			$.closeDOMWindow();
			return false;
		});
		$('input[type="submit"]',cfct_builder.opts.dialogs.popup_wrapper).unbind().click(function(){
			$(this).parents('form').submit();
			return false;
		});
		$('#cfct-save-template-form').unbind().submit(function(){
			cfct_builder.submitTemplateForm($(this));
			return false;
		});
		
		return true;
	};
	
	cfct_builder.submitTemplateForm = function(form) {
		var _formdata = $(form).serialize();
		cfct_builder.fetch('save_as_template',{
			'data':_formdata
		},'save-template-response');	
	};
	
	$(cfct_builder).bind('save-template-response',function(evt,ret) {
		if (!ret.success) {
			cfct_builder.doError(ret);
			return false;
		}

		cfct_builder.bindClickables();
		cfct_builder.editing(0);
		$.closeDOMWindow();
		cfct_messenger.setMessage('Template Saved','confirm');
		return true;		
	});

// Add Module functions	
	cfct_builder.selectModule = function() {
		this.opts.dialogs.popup_wrapper.html(this.opts.dialogs.add_module);
		this.opts.dialogs.add_module.find('div.cfct-popup-content').css({'max-height':this.DOMWindowHeight()-(45+25+14+20),'overflow':'auto'});
		
		if ($('#DOMWindow').not(':visible')) {
			$.openDOMWindow(this.opts.DOMWindow_defaults);
		}
		$('#DOMWindow').css({'overflow':'visible'});
		
		this.prepSelectModuleActions();
	};
	
	cfct_builder.prepSelectModuleActions = function() {
		$('.cancel',this.opts.dialogs.add_module).click(function() {
			cfct_builder.editing(0);
			$.closeDOMWindow();
			return false;
		});
		
		$('.cfct-module-list-toggle',this.opts.dialogs.add_module).click(function() {
			var _this = $(this);
			_tgt = $(_this.attr('href'),cfct_builder.opts.dialogs.add_module);

			switch (_this.attr('id')) {
				case 'cfct-module-list-toggle-detail':
					_tgt.removeClass('cfct-il-mini');
					state = 'column';
					break;
				case 'cfct-module-list-toggle-compact':
					_tgt.addClass('cfct-il-mini');
					state = 'icon';
					break;
			}
			_this.addClass('active').siblings().removeClass('active');			
			cfct_builder.fetch('content_chooser_state',{ 'state':state });
			return false;			
		});
		
		$('.cfct-module-list li a',this.opts.dialogs.add_module).click(function() {
			cfct_builder.showPopupActivityDiv(cfct_builder.opts.dialogs.add_module);
			var _this = $(this);
			cfct_builder.editing({
				'module_name':_this.attr('href').slice(_this.attr('href').indexOf('#')+1)
			});
			
			cfct_builder.editModule();
			return false;
		});
	};
	
// Edit Module Functions
	cfct_builder.editModule = function(extra_data) {
		var data = {
			'module_type':this.editing_items.module_name,
			'module_id':this.editing_items.module_id,
			'block_id':this.editing_items.block_id,
			'row_id':this.editing_items.row_id,
			'max-height':(this.DOMWindowHeight()-(45+25+14+20)) // subtract fudged header, fudged footer, border width, plus safety number to get max body height
		};
		$.extend(data, extra_data || {});

		$(cfct_builder).trigger('edit-module',data);
		
		cfct_builder.fetch('edit_module',data,'edit-module-response');
		return true;
	};
	
	$(cfct_builder).bind('edit-module-response', function(evt,ret) {		
		if (!ret.success) {
			cfct_builder.doError(ret);
			return false;
		}
	
		if ($('#DOMWindow').not(':visible')) {
			$.openDOMWindow(cfct_builder.opts.DOMWindow_defaults);
		}
	
		$('#DOMWindow').css({'overflow':'visible'});
	
		var _form = $('form',ret.html);
	
		cfct_builder.hidePopupActivityDiv(cfct_builder.opts.dialogs.add_module);
		cfct_messenger.clearMessage();
	
		$(cfct_builder.opts.dialogs.popup_wrapper).html(ret.html);
		cfct_builder.prepEditModuleActions();
		cfct_builder.doModuleLoadCallback(_form);
		return true;
	});
	
	cfct_builder.prepEditModuleActions = function() {
		// actions menu action
		$('.cfct-build-module-options .cfct-build-options-header a',cfct_builder.opts.dialogs.popup_wrapper).click(function() {
			cfct_builder.toggleModuleOptions();
			return false;
		});
		
		// take the link ID as a reference to the ID of the item that needs to be displayed
		$('.cfct-build-module-options #cfct-advanced-options-list a', cfct_builder.opts.dialogs.popup_wrapper).click(function() {
			cfct_builder.toggleModuleOptions();
			cfct_builder.showModuleOptionsItem($(this).attr('href'));
			return false;
		});
		
		$('div#cfct-popup-advanced-actions a.close', cfct_builder.opts.dialogs.popup_wrapper).click(function() {
			cfct_builder.hideModuleOptionsItem();
			return false;
		});
		
		// cancel button action
		$('.cancel',cfct_builder.opts.dialogs.popup_wrapper).click(function() {
			cfct_builder.editing(0);
			$.closeDOMWindow();
			return false;
		});
		// handle form submit
		$('form',cfct_builder.opts.dialogs.popup_wrapper).unbind().submit(function(){
			cfct_builder.submitModuleForm($(this));
			return false;
		});
		// add action to module form submit button
		$('input[type="submit"]',cfct_builder.opts.dialogs.popup_wrapper).click(function(){
			$(this).parents('form').submit();
			return false;
		});
	};
	
	cfct_builder.submitModuleForm = function(form) {
		cfct_messenger.clearMessage();
		cfct_builder.showPopupActivityDiv(this.opts.dialogs.popup_wrapper);

		if (false === cfct_builder.doModuleSaveCallback(form)) {
			cfct_builder.hidePopupActivityDiv(this.opts.dialogs.popup_wrapper);
			return false;
		}
		
		var _formdata = $(form).serialize();
		var data = {
			'data':_formdata,
			'row_id':this.editing_items.row_id,
			'block_id':this.editing_items.block_id,
			'module_type':this.editing_items.module_name,
			'module_id':this.editing_items.module_id
		};
		this.fetch('save_module',data,'submit-module-form-response');
		return true;
	};
	
	$(cfct_builder).bind('submit-module-form-response',function(evt,ret) {
		if (!ret.success) {
			cfct_builder.doError(ret);
			return false;
		}
				
		if (this.editing_items.module_id === null) {
			$('#' + this.editing_items.block_id + ' .cfct-block-modules').append(ret.html);
		}
		else {
			$('#' + this.editing_items.module_id).replaceWith(ret.html);
		}
		
		// kill empty row flag
		if ($('#' + this.editing_items.row_id).hasClass('cfct-row-empty')) {
			$('#' + this.editing_items.row_id).removeClass('cfct-row-empty');
		}
		
		this.bindClickables();
		this.editing(0);
		cfct_messenger.setMessage('Module Saved','confirm');
		$.closeDOMWindow();
		
		cfct_builder.hidePopupActivityDiv(this.opts.dialogs.popup_wrapper);
		return true;
	});
	
// Module Advanced Options

	cfct_builder.toggleModuleOptions = function() {
		var _prnt = $('.cfct-build-module-options', cfct_builder.opts.dialogs.popup_wrapper);
		if (_prnt.hasClass('cfct-build-options-active')) {
			_prnt.removeClass('cfct-build-options-active');
		}
		else {
			_prnt.addClass('cfct-build-options-active');
		}
	};


	cfct_builder.showModuleOptionsItem = function(tgt) {
		var _wrapper = $('div#cfct-popup-advanced-actions', cfct_builder.opts.dialogs.popup_wrapper);
		var _tgt = $(tgt, _wrapper);

		if (_wrapper.is(':visible')) {
			if (!_tgt.is(':visible')) {
				_wrapper.slideUp(function() {
					cfct_builder.moduleOptionsItemShowHide(_tgt, _wrapper);				
				});
			}
		}
		else {
			cfct_builder.moduleOptionsItemShowHide(_tgt, _wrapper);
		}
	};

	cfct_builder.moduleOptionsItemShowHide = function(_tgt, _wrapper) {
		_tgt.css({'display':'block'})
			.siblings('div').css({'display':'none'});
		_wrapper.slideDown();
	};

	cfct_builder.hideModuleOptionsItem = function() {
		$('div#cfct-popup-advanced-actions', cfct_builder.opts.dialogs.popup_wrapper).slideUp();
		return false;
	};

// Remove Module Functions
	cfct_builder.confirmRemoveModule = function() {
		this.opts.dialogs.popup_wrapper.html(this.opts.dialogs.delete_module);
		$.openDOMWindow(this.opts.DOMWindow_defaults);

		$('#cfct-delete-module-confirm',this.opts.dialogs.delete_module).click(function() {
			cfct_builder.doRemoveModule();
		});
		$('#cfct-delete-module-cancel',this.opts.dialogs.delete_module).click(function() {
			cfct_builder.editing(0);
			$.closeDOMWindow();
			return false;
		});
	};

	cfct_builder.doRemoveModule = function() {
		cfct_builder.showPopupActivityDiv(cfct_builder.opts.dialogs.delete_module);

		var data = {
			'row_id':this.editing_items.row_id,
			'block_id':this.editing_items.block_id,
			'module_id':this.editing_items.module_id
		};
		cfct_builder.fetch('delete_module',data,'remove-module-response');
	};
	
	$(cfct_builder).bind('remove-module-response',function(evt,ret) {
		if (!ret.success) {
			cfct_builder.doError(ret);
			return false;
		}

		$('#' + cfct_builder.editing_items.module_id).slideUp(function() {
			$(this).remove();
			cfct_builder.editing(0);
			cfct_builder.bindClickables();
			cfct_builder.hidePopupActivityDiv(cfct_builder.opts.dialogs.delete_module);
			$.closeDOMWindow();
		});

		cfct_messenger.setMessage('Module Deleted','confirm');
		return true;
	});

// Reset Build Functions
	cfct_builder.confirmResetTemplate = function() {
		this.opts.dialogs.popup_wrapper.html(this.opts.dialogs.reset_build);
		$.openDOMWindow(this.opts.DOMWindow_defaults);
		
		$('#cfct-reset-build-confirm',this.opts.dialogs.reset_build).click(function() {
			cfct_builder.doResetBuild();
		});
		$('a.cancel',this.opts.dialogs.reset_build).click(function() {
			$.closeDOMWindow();
			return false;
		});
	};
	
	cfct_builder.doResetBuild = function() {
		cfct_builder.showPopupActivityDiv(cfct_builder.opts.dialogs.reset_build);
		
		$.closeDOMWindow();
		$('#cfct-sortables').slideUp('normal',function(){
			cfct_builder.fetch('reset_build',{},'reset-template-response');
		});
	};
	
	$(cfct_builder).bind('reset-template-response',function(evt,ret) {
		if (!ret.success) {
			cfct_builder.doError(ret);
			return false;
		}
		
		cfct_builder.showWelcome();
		$('#cfct-sortables').children('.cfct-row').remove().end().slideDown('normal', function() {
			$(cfct_builder).trigger('row-removed');
		});

		return true;
	});
	
// Module Callbacks
	cfct_builder.doModuleSaveCallback = function(form) {
		var _form = $(form);
		if (cfct_builder.hasModuleSaveCallback(_form.attr('name'))) {
			return cfct_builder.opts.module_save_callbacks[_form.attr('name')].call(_form);
		}
		return true;
	};
	
	cfct_builder.addModuleSaveCallback = function(id,func) {
		return cfct_builder.opts.module_save_callbacks[id] = func;
	};
	
	cfct_builder.hasModuleSaveCallback = function(formName) {
		return (formName in cfct_builder.opts.module_save_callbacks);
	};
	
	cfct_builder.doModuleLoadCallback = function(form) {
		var _form = $(form);
		if (_form.attr('name') in cfct_builder.opts.module_load_callbacks) {
			$.each(
				cfct_builder.opts.module_load_callbacks[_form.attr('name')],
				function(i, func) {
					func.call(null, _form);
				}
			);
		}
		return true;
	};
	
	cfct_builder.addModuleLoadCallback = function(id,func) {
		if (!(cfct_builder.opts.module_load_callbacks[id] instanceof Array)) {
			cfct_builder.opts.module_load_callbacks[id] = [];
		}
		cfct_builder.opts.module_load_callbacks[id].push(func);
	};
	
// module activity display
	cfct_builder.showPopupActivityDiv = function(tgt) {
		$('.cfct-dialog-activity',tgt).show();
	};
	
	cfct_builder.hidePopupActivityDiv = function(tgt) {
		$('.cfct-dialog-activity',tgt).hide();
	};
	
// Build Options
	cfct_builder.toggleOptions = function() {
		var _prnt = $('#cfct-build-header .cfct-build-options');
		if (_prnt.hasClass('cfct-build-options-active')) {
			_prnt.removeClass('cfct-build-options-active');
		}
		else {
			_prnt.addClass('cfct-build-options-active');
		}
	};
	
// Dialogs
	cfct_builder.initDialogs = function() {
		// main popup & wrapper
		this.opts.dialogs.popup = $('#cfct-popup');
		this.opts.dialogs.popup_wrapper = $('#cfct-popup-inner',this.opts.dialogs.popup);
		// delete row
		this.opts.dialogs.delete_row = $('#cfct-delete-row');
		// delete module
		this.opts.dialogs.delete_module = $('#cfct-delete-module');
		// edit module
		this.opts.dialogs.edit_module = $('#cfct-edit-module');
		// add module
		this.opts.dialogs.add_module = $('#cfct-add-module');
		// error dialog
		this.opts.dialogs.error_dialog = $('#cfct-error-notice');
		// reset build
		this.opts.dialogs.reset_build = $('#cfct-reset-build');
		// save template dialog
		this.opts.dialogs.save_template = $('#cfct-save-template');
		$('.cfct-module-form',this.opts.dialogs.save_template).wrap('<form id="cfct-save-template-form" name="cfct-save-template-form" />');
	};
	
// Actions
	cfct_builder.bindClickables = function() {
		// save layout as template
		$('#cfct-save-as-template').unbind().click(function() {
			cfct_builder.editing(0);
			cfct_builder.saveAsTemplate();
			cfct_builder.toggleOptions();
			return false;
		});
		
		// reset layout
		$('#cfct-reset-build-data').unbind().click(function() {
			cfct_builder.editing(0);
			cfct_builder.toggleOptions();
			cfct_builder.confirmResetTemplate();
			return false;
		});
		
		// delete row buttons
		$('.cfct-row-delete',$('#cfct-sortables')).unbind().click(function() {
			var _this = $(this);
			if (_this.parents('.cfct-row').find('div.cfct-module').length == 0) {
				cfct_builder.doRemoveRow(_this.parents('.cfct-row'));
			}
			else {
				cfct_builder.confirmRemoveRow(_this.parents('.cfct-row'));				
			}
			return false;
		});
		
		// add new module button
		$('a.cfct-add-new-module').unbind().click(function() {
			var _this = $(this);			
			cfct_builder.editing({
				'block_id':_this.attr('href').slice(_this.attr('href').indexOf('#')+1),
				'row_id':_this.parents('.cfct-row').attr('id')
			});
			cfct_builder.selectModule();
			return false;
		});
		
		// edit module buttons
		$('a.cfct-module-edit').unbind().click(function() {
			$('body').trigger('click');
			var _this = $(this);
			cfct_builder.editing({
				'module_id':_this.attr('href').slice(_this.attr('href').indexOf('#')+1),
				'block_id':_this.parents('.cfct-block').attr('id'),
				'row_id':_this.parents('.cfct-row').attr('id')
			});
			cfct_builder.showLoadingDialog();
			cfct_builder.editModule();
			return false;
		});
		
		// clear module buttons
		$('a.cfct-module-clear').unbind().click(function() {
			var _this = $(this);
			cfct_builder.editing({
				'module_id':_this.attr('href').slice(_this.attr('href').indexOf('#')+1),
				'block_id':_this.parents('.cfct-block').attr('id'),
				'row_id':_this.parents('.cfct-row').attr('id')
			});
			cfct_builder.confirmRemoveModule();
			return false;
		});
		
		// add new row
		$('#cfct-sortables-add',cfct_build).unbind().click(function() {	
			cfct_builder.addRow();
			return false;
		});
				
		// select row to insert
		$('#cfct-select-new-row ul').find('li > a').unbind().click(function() {
			cfct_builder.insertRow($(this).attr('rel'));
			return false;
		});
		
		// init block sortables
		cfct_builder.initSortables();
		$('.cfct-module').moduleHoverFix('cfct-module-hover');
		
		// global keypress handler
		$(document).bind('keydown', function(evt) {
			switch (evt.which) {
				case 27:
					// bind escape key to closing the DOMWindoow if the DOMWindow is open
					// leaves open possibility of doing other escape actions when DOMWindow is closed
					if ($('#DOMWindow').is(':visible')) {
						$.closeDOMWindow();
					}
					break;
			}
			return true;
		});
		
		// global "click off" handler
		$('body').click(function(e){
			if ($('.cfct-build-options-list').is(':visible')) {
				cfct_builder.toggleOptions();
			}
			if ($('#cfct-select-new-row').is(':visible')) {
				cfct_builder.toggleRowSelect('hide');
			}
		});
		
		return true;
	};

// init post handler - trigger autosave and continue when post_ID has been updated
	cfct_builder.initPost = function(callback,data) {
		jQuery('#title').val($('#cfct-autosave-title').val()).blur();
		setTimeout(function() { cfct_builder.continueInitPost(callback,data); },500);
	};

	cfct_builder.continueInitPost = function(callback,data) {
		if (jQuery('#post_ID').val() < 0) {
			setTimeout(function() { cfct_builder.continueInitPost(callback,data); },500);
		}
		else {
			cfct_builder[callback](data);
		}
		return;
	};
	
// Utility
	cfct_builder.toggleOptionsMenu = function(dir) {
		var _optionsmenu = $('#cfct-build-header .cfct-build-header-group-secondary .cfct-build-options');
		switch(true) {
			case _optionsmenu.is(':visible') && dir == undefined:
			case dir == 'hide':
				func = 'hide';
				break;
			case _optionsmenu.is(':hidden') && dir == undefined:
			case dir == 'show':
				func = 'show';
				break;
		}
		_optionsmenu[func]();		
	};

	cfct_builder.toggleRowSelect = function(dir) {
		var _chooser = $('#cfct-select-new-row');
		
		switch(true) {
			case _chooser.is(':visible'):
			case dir == 'hide':
				func = 'hide';
				break;
			case _chooser.is(':hidden'):
			case dir == 'show':
				cfct_builder.positionRowSelect(_chooser);
				func = 'show';
				break;
		}
				
		_chooser[func](); // Don't give this guy a speed unless you want to make a custom show/hide routine!
	};
	
	cfct_builder.positionRowSelect = function(chooser) {
		var _chooser = $(chooser) || $('#cfct-select-new-row');
		
		// horizontal is straight forward
		_chooser.css({'left':(_chooser.parents('div#cfct-sortables-add-container').width()/2)-(_chooser.width()/2) + 'px'});		

		// vertical requires more work...
		if ($('#cfct-sortables').height() > _chooser.height()) {
			_chooser.find('.cfct-popup-anchored').addClass('cfct-popup-anchored-bottom');
		 	_chooser.css({'top':'-' + (_chooser.height()-6) + 'px','bottom':'auto'});
		}
		else {
			_chooser.find('.cfct-popup-anchored').removeClass('cfct-popup-anchored-bottom');
			_chooser.css({'bottom':'-' + (_chooser.height()-7) + 'px','top':'auto'});
		}
	};
	
	cfct_builder.hasRows = function() {
		return $('#cfct-sortables .cfct-row').size() > 0;
	};
	
	cfct_builder.spinner = function(message) {
		var _message = message || 'Loading&hellip;';
		return '<div id="cfct-spinner-dialog" class="cfct-popup"><div class="cfct-popup-spinner">' + message + '</div></div>';
	};

// Triggered Row Responses 

	$(cfct_builder).bind('row-removed', function(evt) {
		if ($('#cfct-sortables .cfct-row, cfct_build').length == 0) {
			cfct_builder.toggleOptionsMenu('hide');
		}
	});

	$(cfct_builder).bind('new-row-inserted', function(evt, row) {
		cfct_builder.toggleOptionsMenu('show');
	});
	
// Get started
	$(function() {
		cfct_build = $('#cfct-build');
		cfct_builder.opts.build = cfct_build;
		cfct_builder.opts.rich_text = ($('#postdivrich').length > 0 ? true : false);
		cfct_builder.initDialogs();
		cfctAdminEditInit();
	});

})(jQuery);	

