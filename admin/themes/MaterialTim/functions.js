cpt.materialTim = $.extend(cpt.materialTim, {
    /*init: function(){
       $(".hasDatepicker").each(function() {
            var maxDate = $(this).attr("maxDate");
            $(this).datepicker("option", "maxDate", maxDate);
        }); 
    },*/
});

$(document).ready(function () {
	$(".ui-datepicker-trigger").livequery('click', function(){
	    var parent = $(this).closest(".type-text.ym-fbox-text");
	    var maxDate = $('input.hasDatepicker', parent).attr("maxDate");
	    $('input.hasDatepicker', parent).datepicker("option", "maxDate", maxDate);
	    $('input.hasDatepicker', parent).datepicker("show");
	});
});
