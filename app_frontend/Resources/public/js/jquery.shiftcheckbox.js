(function ($) {
    var prevChecked;
    
    $.fn.shiftcheckbox = function()
    {
        selectorStr = this.selector;
        $(selectorStr).live("click", handleClick);
    };

    function handleClick(event)
    {
        var checkStatus = this.checked;

        if (event.shiftKey) {
            if (prevChecked != 'null') {
                var currentChecked = $(selectorStr).index($(this));

                if (currentChecked < prevChecked) {
                    $(selectorStr).each(function(i) {
                        if (i >= currentChecked && i <= prevChecked) {
                            this.checked = checkStatus;
                            $(this).change();
                        }
                    });
                } else {
                    $(selectorStr).each(function(i) {
                        if (i >= prevChecked && i <= currentChecked) {
                            this.checked = checkStatus;
                            $(this).change();
                        }
                    });
                }

                prevChecked = currentChecked;
            }
        } else {
            if (checkStatus) {
                prevChecked = $(selectorStr).index($(this));
            }
        }
    }
})(jQuery);