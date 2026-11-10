Util.createCPObject('cpt.matrix');

cpt.trade = {
	init: function(){
    	//show hide description in Help Content - TRADE SMART (USS Product)
    	$('.contentTitle').livequery('click', function(){
    		var parent = $(this).closest('.helpContentTask');
    	    $('.contentDescription', parent).slideToggle();
    	});

		// Adding help button pop window in the content list  - TRADE SMART (USS Product)
		$("a.helpContentTask").livequery('click', function (e){
		    var module_name = $(this).attr('module_name');
		    var url = 'index.php?module=webBasic_content&_spAction=helpContentTask&module_name=' + module_name + '&showHTML=0';
		    var exp = {
		        url: url
		    };
		    Util.openDialogForLink('Help Content',  1000, 500, 0, exp);
		});

    	//show hide description in GET STARTED Content - TRADE SMART (USS Product)
    	$('.contentTitle').livequery('click', function(){
    		var parent = $(this).closest('.getStartedContentTask');
    	    $('.contentDescription', parent).slideToggle();
    	});

		// Adding GET STARTED button pop window in the content list  - TRADE SMART (USS Product)
		$("a.getStartedContentTask").livequery('click', function (e){
		    var module_name = $(this).attr('module_name');
		    var url = 'index.php?module=webBasic_content&_spAction=getStartedContentTask&module_name=' + module_name + '&showHTML=0';
		    var exp = {
		        url: url
		    };
		    Util.openDialogForLink('Get Started',  1000, 500, 0, exp);
		});

    	$("#nav .hlist ul li a span").addClass('inner');
    	$("#nav .hlist ul li a").blend();

    	$(".actionBtns li a").wrap("<div class='button'>");

    	$('.contentScroller, .m-common_dashboard .widget div.tableOuter').addClass('scroll-pane');
    	/*$('.scroll-pane').jScrollPane(
    	    {}
    	);*/

    	if ($('.tplLogin').length > 0){
    	    var toSubtract = $('#header').outerHeight(true) + $('#footer').outerHeight(true);
    	    var mainPanelHt = $(window).height() - toSubtract - 20;
    	    $('#col3_content').css({'height' : mainPanelHt + 'px', overflow: 'auto', 'overflow-x': 'hidden'});
    	    $("#col3_content #loginOuter").cp_center();
    	}

    	(function( $ ) {
    	     $.widget( "ui.combobox", {
    	         _create: function() {
    	             var self = this,
    	                 select = this.element.hide(),
    	                 selected = select.children( ":selected" ),
    	                 value = selected.val() ? selected.text() : "";
    	             var input = this.input = $( "<input>" )
    	                 .insertAfter( select )
    	                 .val( value )
    	                 .autocomplete({
    	                     delay: 0,
    	                     minLength: 0,
    	                     source: function( request, response ) {
    	                         var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
    	                         response( select.children( "option" ).map(function() {
    	                             var text = $( this ).text();
    	                             if ( this.value && ( !request.term || matcher.test(text) ) )
    	                                 return {
    	                                     label: text.replace(
    	                                         new RegExp(
    	                                             "(?![^&;]+;)(?!<[^<>]*)(" +
    	                                             $.ui.autocomplete.escapeRegex(request.term) +
    	                                             ")(?![^<>]*>)(?![^&;]+;)", "gi"
    	                                         ), "<strong>$1</strong>" ),
    	                                     value: text,
    	                                     option: this
    	                                 };
    	                         }) );
    	                     },
    	                     select: function( event, ui ) {
    	                         ui.item.option.selected = true;
    	                         self._trigger( "selected", event, {
    	                             item: ui.item.option
    	                         });
    	                         select.trigger("change");
    	                     },
    	                     change: function( event, ui ) {
    	                         if ( !ui.item ) {
    	                             var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
    	                                 valid = false;
    	                             select.children( "option" ).each(function() {
    	                                 if ( $( this ).text().match( matcher ) ) {
    	                                     this.selected = valid = true;
    	                                     return false;
    	                                 }
    	                             });
    	                             if ( !valid ) {
    	                                 // remove invalid value, as it didn't match anything
    	                                 $( this ).val( "" );
    	                                 select.val( "" );
    	                                 input.data( "autocomplete" ).term = "";
    	                                 return false;
    	                             }
    	                         }
    	                     }
    	                 })
    	                 .addClass( "ui-widget ui-widget-content ui-corner-left" );

    	             input.data( "autocomplete" )._renderItem = function( ul, item ) {
    	                 return $( "<li></li>" )
    	                     .data( "item.autocomplete", item )
    	                     .append( "<a>" + item.label + "</a>" )
    	                     .appendTo( ul );
    	             };

    	             input.val($(select).find("option:selected").text());

    	             this.button = $( "<button type='button'>&nbsp;</button>" )
    	                 .attr( "tabIndex", -1 )
    	                 .attr( "title", "Show All Items" )
    	                 .insertAfter( input )
    	                 .button({
    	                     icons: {
    	                         primary: "ui-icon-triangle-1-s"
    	                     },
    	                     text: false
    	                 })
    	                 .removeClass( "ui-corner-all" )
    	                 .addClass( "ui-corner-right ui-button-icon" )
    	                 .click(function() {
    	                     // close if already visible
    	                     if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
    	                         input.autocomplete( "close" );
    	                         return;
    	                     }

    	                     // pass empty string as value to search for, displaying all results
    	                     input.autocomplete( "search", "" );
    	                     input.focus();
    	                 });
    	         },

    	         destroy: function() {
    	             this.input.remove();
    	             this.button.remove();
    	             this.element.show();
    	             $.Widget.prototype.destroy.call( this );
    	         }
    	     });
    	 })( jQuery );

    	$("a.checkProductPrice").livequery('click', function (e){
    	   Util.showProgressInd();
    	   var url = 'index.php?module=tradingsg_pos&_spAction=productPrice&showHTML=0';
    	   var exp = {
    	       url: url
    	   };
    	   Util.openDialogForLink('Check Product Price',  900, 400, 0, exp);
    	});

    $(".actionBtns li a").wrap("<div class='button'>");

    $('.contentScroller, .m-common_dashboard .widget div.tableOuter').addClass('scroll-pane');
    /*$('.scroll-pane').jScrollPane(
        {}
    );*/


	// THE CHECK PRODUCT PRICE BUTTON HAS BEEN ADDED IN THE BLOSSOMS SECTION ONLY.
   		$(".checkProductPrice input[name='product_title']")
   		.livequery(cpt.trade.checkProductPrice);

    	$( "table.search td select" ).combobox(
    	);

    	$("table.search td select").change(function() {
    	    $('#searchTop').submit();
    	});
 	},
  	// THE CHECK PRODUCT PRICE BUTTON HAS BEEN ADDED IN THE BLOSSOMS SITE ONLY.
	checkProductPrice: function() {
	    var titleObj = this;
	  	$(titleObj).autocomplete({
		source : 'index.php?module=tradingsg_pos&_spAction=searchProductTitle&showHTML=0'
		,minLength : 2
	  	,select: function(event, ui) {
	 	var selectedObj = ui.item;
	  	var product_id = selectedObj.id
	  	//alert (product_id);
	  	$(this).after("<input type='hidden' name='product_id' value=" + product_id + ">");

	              //--------------------------------------------
	              Util.showProgressInd();
	          	var url = 'index.php?module=tradingsg_pos&_spAction=productPriceDisplay&showHTML=0';
	              $.get(url, {product_id: product_id}, function(html){
	               //cpm.tradingsg.pos.reloadOrderItems();
	               $(".checkProductPrice input[name='product_title']").val('');
	                  $('#productDisplay').html(html);
	                  Util.hideProgressInd();
	              });
			}
		});
	}
},

$(".nextQueueNo").livequery('click', function (e){
    var url         = 'index.php?_theme=matrix&_spAction=updateQueueNoNext&showHTML=0';
    var queue_no    = $(this).attr('queue_no');
    var employee_id = $(this).attr('employee_id');
    $.get(url, {queue_no: queue_no, employee_id:employee_id}, function(html){
        cpt.trade.reloadQueueno();
    });
});

cpt.trade.reloadQueueno = function(){
    var url = 'index.php?_theme=matrix&_spAction=patientQueueNo&showHTML=0';
    $.get(url,  function(html){
        $('.queueNumberDisplay').html(html);
    });
}


