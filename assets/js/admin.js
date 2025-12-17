/**
 * WordPress Media Uploader for Directorist Seller Verification
 *
 * @package Directorist_Seller_Verification
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Handle upload button click.
		$(document).on('click', '.directorist-upload-button', function(e) {
			e.preventDefault();

			var button = $(this);
			var fieldName = button.data('field');
			var fieldInput = $('#' + fieldName);
			var previewDiv = $('#' + fieldName + '_preview');
			var removeButton = $('.directorist-remove-button[data-field="' + fieldName + '"]');

			if (typeof wp === 'undefined' || !wp.media) {
				return;
			}

			// Create a new media frame per field click so callbacks bind correctly.
			var mediaUploader = wp.media({
				title: 'Select Document',
				button: {
					text: 'Use this document'
				},
				multiple: false,
				library: {
					type: ['image', 'application/pdf']
				}
			});

			// When a file is selected, run a callback
			mediaUploader.on('select', function() {
				var attachment = mediaUploader.state().get('selection').first().toJSON();
				var attachmentId = attachment.id;
				var attachmentUrl = attachment.url;
				var attachmentType = attachment.type;

				// Set the hidden field value
				fieldInput.val(attachmentId);

				// Show preview
				if (attachmentType === 'image') {
					previewDiv.html('<img src="' + attachmentUrl + '" style="max-width: 300px; margin-top: 10px; display: block;" />');
				} else {
					previewDiv.html('<p style="margin-top: 10px;"><a href="' + attachmentUrl + '" target="_blank">View Document</a></p>');
				}

				previewDiv.show();
				removeButton.show();
			});

			// Open the uploader
			mediaUploader.open();
		});

		// Handle remove button click
		$(document).on('click', '.directorist-remove-button', function(e) {
			e.preventDefault();

			var button = $(this);
			var fieldName = button.data('field');
			var fieldInput = $('#' + fieldName);
			var previewDiv = $('#' + fieldName + '_preview');

			// Clear the field
			fieldInput.val('');
			previewDiv.html('').hide();
			button.hide();
		});
	});

})(jQuery);

