(function ($) {
	"use strict";

	/**
	 * ACF Default Value Initializer Field Settings
	 */
	const ACFDefaultValueInitializer = {
		init() {
			this.bindEvents();
		},

		bindEvents() {
			// Show/hide warning when init_default_values is toggled
			$(document).on(
				"change",
				'.acf-field-setting-init_default_values input[type="checkbox"]',
				this.handleInitToggle
			);

			// Add processing button to field group
			this.addProcessingButton();
		},

		handleInitToggle(e) {
			// Warning functionality removed - checkbox now works without displaying warnings
		},



		addProcessingButton() {
			if ($("#acf-dvi-process-btn").length > 0) {
				return;
			}

			// Try multiple selectors for different WordPress/ACF versions
			let $targetContainer = null;
			const possibleContainers = [
				"#submitdiv .submitbox",
				"#submitdiv",
				".acf-postbox-actions",
				"#acf-group-options .inside",
				".postbox-container .postbox:first-child .inside"
			];

			for (const selector of possibleContainers) {
				const $container = $(selector);
				if ($container.length > 0) {
					$targetContainer = $container;
					break;
				}
			}

			if (!$targetContainer) {
				// Fallback: add to the main content area
				$targetContainer = $('.acf-input-wrap').first().parent();
				if ($targetContainer.length === 0) {
					return;
				}
			}

			const buttonHtml = `
                  <div id="acf-dvi-process-section" style="margin-top: 15px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                      <button type="button" id="acf-dvi-process-btn" class="button button-secondary" style="width: 100%;">
                          🔄 Initialize Default Values
                      </button>
                      <p style="margin: 8px 0 0 0; font-size: 11px; color: #666;">
                          Manually trigger default value initialization for this field group.
                      </p>
                  </div>
              `;

			$targetContainer.append(buttonHtml);

			$("#acf-dvi-process-btn").on("click", this.processFieldGroup);
		},

		processFieldGroup() {
			const $btn = $("#acf-dvi-process-btn");
			const originalText = $btn.text();
			const fieldGroupKey = $("#post_name").val();

			if (!fieldGroupKey) {
				alert("Please save the field group first.");
				return;
			}

			$btn
				.prop("disabled", true)
				.text("⏳ " + acfDviSettings.processing);

			$.ajax({
				url: acfDviSettings.ajaxUrl,
				type: "POST",
				data: {
					action: "acf_dvi_process_field_group",
					field_group_key: fieldGroupKey,
					nonce: acfDviSettings.nonce,
				},
				success(response) {
					if (response.success) {
						$btn.text("✅ " + acfDviSettings.completed);
						setTimeout(() => {
							$btn.text(originalText).prop("disabled", false);
						}, 3000);
					} else {
						alert(acfDviSettings.error + " " + (response.data || ""));
						$btn.text(originalText).prop("disabled", false);
					}
				},
				error() {
					alert(acfDviSettings.error);
					$btn.text(originalText).prop("disabled", false);
				},
			});
		},
	};

	// Initialize when ACF field group page is ready
	$(document).ready(() => {
		if (
			typeof acf !== "undefined" &&
			$("body").hasClass("post-type-acf-field-group")
		) {
			ACFDefaultValueInitializer.init();

			// Try adding button after a slight delay to ensure DOM is ready
			setTimeout(() => {
				if ($("#acf-dvi-process-btn").length === 0) {
					ACFDefaultValueInitializer.addProcessingButton();
				}
			}, 1000);
		}
	});
})(jQuery);
