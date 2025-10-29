/**
 * ChatCommerce AI Admin Scripts
 *
 * @package ChatCommerceAI
 */

(function($) {
    'use strict';

    /**
     * Handle notice dismissal.
     */
    function handleNoticeDismissal() {
        // Listen for dismiss button clicks on ChatCommerce AI notices
        $(document).on('click', '.chatcommerce-ai-notice .notice-dismiss', function() {
            var $notice = $(this).closest('.chatcommerce-ai-notice');
            var noticeId = $notice.data('notice-id');

            if (!noticeId) {
                return;
            }

            // Send AJAX request to save dismissed state
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'chatcommerce_ai_dismiss_notice',
                    notice_id: noticeId,
                    nonce: chatcommerceAIAdmin.dismissNonce
                },
                success: function(response) {
                    console.log('[ChatCommerce AI] Notice dismissed:', noticeId);
                },
                error: function(xhr, status, error) {
                    console.error('[ChatCommerce AI] Failed to dismiss notice:', error);
                }
            });
        });
    }

    /**
     * Handle API key visibility toggle.
     */
    function handleApiKeyToggle() {
        $('.cc-toggle-api-key').on('click', function() {
            var $button = $(this);
            var $input = $button.siblings('.cc-api-key-input');
            var $icon = $button.find('.dashicons');
            var isPassword = $input.attr('type') === 'password';

            if (isPassword) {
                // Show the key
                $input.attr('type', 'text');
                $icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
                $button.attr('aria-label', 'Hide API key');
            } else {
                // Hide the key
                $input.attr('type', 'password');
                $icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
                $button.attr('aria-label', 'Show API key');
            }
        });
    }

    /**
     * Update color display when color picker changes.
     */
    function handleColorPicker() {
        $('.cc-color-input').on('input change', function() {
            var $input = $(this);
            var $display = $input.siblings('.cc-color-display');
            $display.val($input.val());
        });
    }

    /**
     * Initialize admin scripts.
     */
    $(document).ready(function() {
        handleNoticeDismissal();
        handleApiKeyToggle();
        handleColorPicker();

        // Log initialization
        console.log('[ChatCommerce AI] Admin scripts initialized');
    });

})(jQuery);
