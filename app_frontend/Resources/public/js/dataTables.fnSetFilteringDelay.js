$.fn.dataTableExt.oApi.fnSetFilteringDelay = function(oSettings, iDelay) {
    
    var _that = this;
    var iDelay = (typeof iDelay == 'undefined') ? 250 : iDelay;
     
    this.each(function(i) {
        $.fn.dataTableExt.iApiIndex = i;
        var oTimerId = null;
        var sPreviousSearch = null;
        var anControls = $('input', _that.fnSettings().aanFeatures.f);
         
        anControls.unbind('keyup').bind('keyup', function() { 
            var anControl = $(this);
            if (sPreviousSearch === null || sPreviousSearch != anControl.val()) {
                window.clearTimeout(oTimerId);
                sPreviousSearch = anControl.val();  
                oTimerId = window.setTimeout(function() {
                    $.fn.dataTableExt.iApiIndex = i;
                    _that.fnFilter(anControl.val());
                }, iDelay);
            }
        });
         
        return this;
    });
    return this;
}