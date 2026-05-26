jQuery(document).ready(function($) {
	var $adminForm = $('form.wptoc-admin-form');
	var $successAlert = $('.wptoc-admin-alert.is-success');
	var $tabItems = $('ul#tabbed-nav li').not('.url');
	var $tabContents = $('.tab_content');
	var $activeTabField = $('#wptoc_active_tab');
	var $overlayTitle = $('.wptoc-admin-save-overlay__title');
	var $overlayText = $('.wptoc-admin-save-overlay__text');
	var defaultOverlayTitle = $overlayTitle.data('default-title') || $overlayTitle.text();
	var defaultOverlayText = $overlayText.data('default-text') || $overlayText.text();

	function activateTab(tabSelector) {
		var $targetLink = $('ul#tabbed-nav a[href="' + tabSelector + '"]').first();

		if ( !$targetLink.length ) {
			tabSelector = '#tab1';
			$targetLink = $('ul#tabbed-nav a[href="#tab1"]').first();
		}

		$tabItems.removeClass('active');
		$targetLink.parent('li').addClass('active');
		$('ul#tabbed-nav a').removeClass('active');
		$targetLink.addClass('active');
		$tabContents.hide();
		$(tabSelector).show();
		$activeTabField.val(tabSelector.replace('#', ''));
	}

	$('.tab_content, #toc_for_developers, div.more_toc_options.disabled, tr.disabled').hide();
	activateTab('#' + ($activeTabField.val() || 'tab1'));

	$('ul#tabbed-nav li').click(function(event) {
		if ( !$(this).hasClass('url') ) {
			event.preventDefault();

			var activeTab = $(this).find('a').attr('href');
			activateTab(activeTab);
			$(activeTab).hide().fadeIn();
		}
	});
	
	$('input#show_heading_text, input#visibility').click(function() {
		$(this).siblings('div.more_toc_options').toggle('fast');
	});
	
	$('input#smooth_scroll').click(function() {
		$('#smooth_scroll_offset_tr').toggle('fast');
	});
	
	$('input[name="theme"]').click(function() {
		// check custom theme selection
		if ( $(this).val() == 100 ) {
			$(this).parent().siblings('div.more_toc_options').show('fast');
		}
		else
			$(this).parent().siblings('div.more_toc_options').hide('fast');
	});
	
	/* width drop down */
	$('select#width').change(function() {
		if ( $(this).find('option:selected').val() == 'User defined' ) {
			$(this).siblings('div.more_toc_options').show('fast');
			$('input#width_custom').focus();
		}
		else
			$(this).siblings('div.more_toc_options').hide('fast');
	});
	$('input#width_custom, input#font_size, input#smooth_scroll_offset, input#display_top_offset').keyup(function() {
		var value = $(this).val();
		$(this).val( value.replace(/[^0-9\.]/, '') );
	});

	$adminForm.on('submit', function() {
		var $form = $(this);
		var $submit = $form.find('.wptoc-admin-submit-row .button-primary').first();
		var submitter = $form.data('submitter') || {};
		var originalLabel = $submit.data('original-label');

		if ( ! originalLabel ) {
			$submit.data('original-label', $submit.val());
		}

		if ( $form.hasClass('is-saving') ) {
			return false;
		}

		$form.addClass('is-saving');
		$overlayTitle.text(submitter.busyTitle || defaultOverlayTitle);
		$overlayText.text(submitter.busyText || defaultOverlayText);
		$submit.prop('disabled', true).val('Saving...');
	});

	$adminForm.find(':submit').on('click', function() {
		var $button = $(this);

		$adminForm.data('submitter', {
			busyTitle: $button.data('busy-title') || defaultOverlayTitle,
			busyText: $button.data('busy-text') || defaultOverlayText
		});
	});

	if ( $successAlert.length ) {
		window.setTimeout(function() {
			$successAlert.slideUp(220, function() {
				$(this).remove();
			});
		}, 2600);
	}
	
	if ( $.farbtastic ) {
		var f = $.farbtastic('#farbtastic_colour_wheel');
		var selected;
		$('#farbtastic_colour_wheel').css('opacity', 0.5).hide();
		$('input.custom_colour_option')
			.each(function() { f.linkTo(this); $(this).css('opacity', 0.5); })
			.keyup(function() {
				var hex = $(this).val();
				hex = hex.replace(/[^a-fA-F0-9]/g, '');
				if ( hex.length > 6 ) hex = hex.substr(0, 6);
				$(this).val( '#' + hex );
			})
			.focus(function() {
				if (selected) {
					$(selected).css('opacity', 0.5);
					$(selected).siblings('img').css('opacity', 0.4);
				}
				f.linkTo(this);
				$('#farbtastic_colour_wheel').css('opacity', 1).show('fast');
				$(this).siblings('img').css('opacity', 1);
				$(selected = this).css('opacity', 1);
			});
		$('table#theme_custom img').click(function() {
			$(this).siblings('input.custom_colour_option').focus();
		});
	}
});