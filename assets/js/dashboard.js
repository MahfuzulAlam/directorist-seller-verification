/**
 * Frontend dashboard uploader (WP Media) for Directorist - Seller Verification.
 *
 * @package Directorist_Seller_Verification
 */

(function ($) {
	'use strict';

	function getI18n() {
		if (typeof window.DirectoristSellerVerification === 'object' && window.DirectoristSellerVerification) {
			return window.DirectoristSellerVerification;
		}
		return {
			ajaxurl: '',
			nonce: '',
			title: 'Select Document',
			button_text: 'Use this document',
			view_text: 'View Document',
			saving_text: 'Saving...',
			saved_text: 'Saved successfully.',
			error_text: 'Something went wrong. Please try again.',
		};
	}

	function renderPreview($preview, attachment) {
		var url = attachment.url || '';
		var type = attachment.type || '';
		var subtype = attachment.subtype || '';

		if (!url) {
			$preview.html('').addClass('directorist-sv-hidden');
			return;
		}

		if (type === 'image') {
			$preview.html('<img class="directorist-sv-preview-image" src="' + url + '" alt="" />').removeClass('directorist-sv-hidden');
			return;
		}

		// PDFs typically come through as { type: "application", subtype: "pdf" }.
		if (type === 'application' && subtype === 'pdf') {
			$preview
				.html('<p class="directorist-sv-preview-link"><a href="' + url + '" target="_blank" rel="noopener noreferrer">' + getI18n().view_text + '</a></p>')
				.removeClass('directorist-sv-hidden');
			return;
		}

		$preview
			.html('<p class="directorist-sv-preview-link"><a href="' + url + '" target="_blank" rel="noopener noreferrer">' + getI18n().view_text + '</a></p>')
			.removeClass('directorist-sv-hidden');
	}

	$(document).on('click', '.directorist-sv-upload', function (e) {
		e.preventDefault();

		if (typeof wp === 'undefined' || !wp.media) {
			return;
		}

		var $this = $(this);
		var fieldName = $this.data('field');
		var $field = $('#' + fieldName);
		var $preview = $('#' + fieldName + '_preview');
		var $remove = $('.directorist-sv-remove[data-field="' + fieldName + '"]');

		var frame = wp.media({
            title: getI18n().title,
            button: { text: getI18n().button_text },
            multiple: false,
            library: { 
                type: ['image', 'application/pdf'],
                author: wp.media.view.settings.post.authorId // Only show current user's uploads
            },
        });

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			$field.val(attachment.id || '');
			renderPreview($preview, attachment);
			$remove.removeClass('directorist-sv-hidden');
		});

		frame.open();
	});

	$(document).on('click', '.directorist-sv-remove', function (e) {
		e.preventDefault();

		var $this = $(this);
		var fieldName = $this.data('field');
		var $field = $('#' + fieldName);
		var $preview = $('#' + fieldName + '_preview');

		$field.val('');
		$preview.html('').addClass('directorist-sv-hidden');
		$this.addClass('directorist-sv-hidden');
	});

	function setMessage(type, html) {
		var $msg = $('#directorist_sv_message');
		if (!$msg.length) {
			return;
		}

		$msg.removeClass('directorist-alert-success directorist-alert-danger');
		if (type === 'success') {
			$msg.addClass('directorist-alert-success');
		} else if (type === 'error') {
			$msg.addClass('directorist-alert-danger');
		}

		$msg.html(html).removeClass('directorist-sv-hidden');
	}

	function saveDocumentsAjax() {
		var i18n = getI18n();

		if (!i18n.ajaxurl) {
			setMessage('error', i18n.error_text || 'Something went wrong.');
			return;
		}

		var payload = {
			action: 'directorist_sv_save_documents',
			nonce: i18n.nonce || '',
			seller_document_type: $('#seller_document_type').val() || '',
			seller_document_front: $('#seller_document_front').val() || '',
			seller_document_back: $('#seller_document_back').val() || '',
		};

		var $btn = $('#directorist_sv_save');
		$btn.prop('disabled', true);
		setMessage('info', i18n.saving_text || 'Saving...');

		$.post(i18n.ajaxurl, payload)
			.done(function (res) {
				if (res && res.success) {
					setMessage('success', (res.data && res.data.message) ? res.data.message : (i18n.saved_text || 'Saved successfully.'));
				} else {
					setMessage('error', (res && res.data && res.data.message) ? res.data.message : (i18n.error_text || 'Something went wrong.'));
				}
			})
			.fail(function () {
				setMessage('error', i18n.error_text || 'Something went wrong.');
			})
			.always(function () {
				$btn.prop('disabled', false);
			});
	}

	$(document).on('click', '#directorist_sv_save', function (e) {
		e.preventDefault();
		saveDocumentsAjax();
	});

	$(document).on('submit', '.directorist-seller-verification-form', function (e) {
		// Prevent normal POST submit and save via AJAX instead.
		e.preventDefault();
		saveDocumentsAjax();
	});
})(jQuery);


