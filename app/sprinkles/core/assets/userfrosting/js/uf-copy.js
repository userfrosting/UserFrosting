/**
 * Copies text or control fields to clipboard.  Wrap a .js-copy-target and .js-copy-trigger inside a common .js-copy-container.
 */

if (typeof $.uf === 'undefined') {
    $.uf = {};
}

$.uf.copy = function (button) {
    var _this = this;

    var clipboard = new Clipboard(button, {
        text: function(trigger) {
            var el = $(trigger).closest('.js-copy-container').find('.js-copy-target');
            if (el.is(':input')) {
                return el.val();
            } else {
                return el.html();
            }
        }
    });

    clipboard.on('success', function(e) {
        setTooltip(e.trigger, 'Copied!');
        hideTooltip(e.trigger);
    });

    clipboard.on('error', function(e) {
        setTooltip(e.trigger, 'Failed!');
        hideTooltip(e.trigger);
    });

    function setTooltip(btn, message) {
        $(btn)
        .attr('data-original-title', message)
        .tooltip('show');
    }
    
    function hideTooltip(btn) {
        setTimeout(function() {
            $(btn).tooltip('hide')
            .attr('data-original-title', "");
        }, 1000);
    }

    // Tooltip
    $(button).tooltip({
        trigger: 'click'
    });
};
