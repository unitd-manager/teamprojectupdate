Util.createCPObject('cpm.payroll.dashboard');

cpm.payroll.dashboard.init = function(){
    $('.m-payroll_dashboard .tableOuter table.list tr:odd').addClass('odd');
    $('.m-payroll_dashboard .tableOuter table.list tr:even').addClass('even');

    $(".m-payroll_dashboard .widget").sortable({
    	connectWith: '.widget',
    	// We make the .portlet-header to act as a handle for moving portlets //
    	handle: 'h2'
    });

    // We create the protlets and style them accordingly by script //
    $(".m-payroll_dashboard .widget").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
    	.find("h2")
    		.addClass("ui-widget-header ui-corner-top")
    		.prepend('<span class="ui-icon ui-icon-triangle-1-n"></span>')
    		.end()
    	.find(".portlet-content");
    // We make arrow button on any portlet header to act as a switch for sliding up and down the portlet content //
    $("h2 .ui-icon").click(function() {
    	$(this).parents(".widget:first").find(".tableOuter").slideToggle("fast");
    	$(this).toggleClass("ui-icon-triangle-1-s");
    	return false;
    });
}