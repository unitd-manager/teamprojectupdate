Util.createCPObject('cpt.materialTim');

cpt.materialTim = {
	init: function(){
        $(".fld_date.fld_date.hasDatepicker").each(function() {
            var maxDate = $(this).attr("maxDate");
            $(this).datepicker("option", "maxDate", maxDate);
        });

        $(".niceScroll").niceScroll();

        /*$("table.search td select").change(function(e) {
            e.preventDefault();
            //alert("hi");
            //$('#searchTop').submit();
        });*/

        window.onload = getStartedContent();
        function getStartedContent() {
            /*var popupSession = $('#getStartedPopupOnloadSession').val();

            if(popupSession == ''){
                setTimeout(function() {
                    $("a.getStartedContentTask").trigger('click');
                },100);
            }*/

            $('.v-list table.cpSearch .type-text.ym-fbox-text.dateRange input.fld_date').each(function(){
                var inputname = $(this).attr('name');
                var dateValue = $(this).val();
                var dateValueSplit = dateValue.split("-");
                if(isNaN(dateValueSplit[0])){ 
                    dateValue = '';
                }

                $(this).addClass('MainDateField');
                $(this).after("<input type='text' class='hiddenDateDisplay' name='hidden_date_display' data-onload='"+dateValue+"'>");
            });

            $('.v-list table.cpSearch td.dateRange input.fld_date').each(function(){
                var inputname = $(this).attr('name');
                var dateValue = $(this).val();
                var dateValueSplit = dateValue.split("-");
                if(isNaN(dateValueSplit[0])){ 
                    dateValue = '';
                }
                
                $(this).addClass('MainDateField');
                $(this).after("<input type='text' class='hiddenDateDisplay' name='hidden_date_display' data-onload='"+dateValue+"'>");
            });

            $('.v-list table.cpSearch .type-text.ym-fbox-text.dateRange .hiddenDateDisplay[data-onload]').each(function() {
                var dateCheck = $(this).attr('data-onload');
                
                if(dateCheck != '') {
                    var date      = dateCheck.replace(/-/g, '/');
                    var newdate   = new Date(date);
                    var dd = ('0' + newdate.getDate()).slice(-2);
                    var mm = ('0' + (newdate.getMonth() + 1)).slice(-2)
                    var y  = newdate.getFullYear();
         
                    var endDate = dd + '-'+ mm + '-' + y;
                }else {
                    var endDate = '';
                }

                $(this).val(endDate);
            });

            $('.v-list table.cpSearch td.dateRange .hiddenDateDisplay[data-onload]').each(function() {
                var dateCheck = $(this).attr('data-onload');
                
                if(dateCheck != '') {
                    var date      = dateCheck.replace(/-/g, '/');
                    var newdate   = new Date(date);
                    var dd = ('0' + newdate.getDate()).slice(-2);
                    var mm = ('0' + (newdate.getMonth() + 1)).slice(-2)
                    var y  = newdate.getFullYear();
         
                    var endDate = dd + '-'+ mm + '-' + y;
                }else {
                    var endDate = '';
                }

                $(this).val(endDate);
            });

            var paymentReminder2 = $("input[name='paymentReminder2']").val();
            if(paymentReminder2 == 1){
                Util.showProgressInd();
                var url = 'index.php?plugin=common_login&_spAction=paymentReminder2&showHTML=0';
                var exp = {
                    url: url
                };

                Util.openDialogForLink('Payment Reminder',  600, 200, 0, exp);
            }
        }

        $('.leftNav .hlist ul li.first').livequery('click', function(){
            var parent = $(this).closest('li');
            parent.next('ul.displayNone').slideToggle();
        });

        $('#feedback-form select[name="service_type"]').livequery('change', function(){
            var service_type = $(this).val();
            var email  = $("input[name='email_address']").val();
            var url = 'index.php?module=manPower_candidate&_spAction=leadTypeValidate&showHTML=0';
            $.get(url, {service_type: service_type, email: email}, function(html){
                if(html != ''){
                    Util.alert(html);
                }
            });

        });

        $("#feedback-tab").click(function() {
            $("#feedback-form").animate({
              width: "toggle"
            });
        });

        $("#feedback-form form .cancel").live('click', function() {
            $("#feedback-form").animate({
               width: "toggle"
            });
        });

        $('#feedback-form form').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var options = { direction: "right" };
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                $("#feedback-form").toggle("slide", options).find("textarea").val('');
                $('#quickEnquiryForm input').val('');
                $('#quickEnquiryForm select').val('');
                //window.location.reload(true);
          },'json');
          return false;
       });

        $("input[name='mobile_search']")
        .livequery(cpt.materialTim.mobileSearch);

        $("input[name='email_search']")
        .livequery(cpt.materialTim.emailSearch);

        //Click event to scroll to top
        /*$('.scrollToTop').click(function(){
            $('#main').animate({scrollTop : 0},800);
            return false;
        });*/

    	//show hide description in Help Content - TRADE SMART (USS Product)
        $('.contentTitle').livequery('click', function(){
            //$('.contentDescription').css('display','none');
            var parent = $(this).closest('.helpContentTask');
            $('.contentDescription', parent).slideToggle();
            var parent = $(this).closest('.startedContentTask');
            $('.contentDescription', parent).slideToggle();
        });

		// Adding help button pop window in the content list  - TRADE SMART (USS Product)
		$("a.helpContentTask").livequery('click', function (e){
		    var module_name = $(this).attr('module_name');
		    var url = 'index.php?module=webBasic_content&_spAction=helpContentTask&module_name=' + module_name + '&showHTML=0';
		    var exp = {
		        url: url
		    };
		    Util.openDialogForLink('Help Content',  1000, 635, 0, exp);
		});

    	//show hide description in GET STARTED Content - TRADE SMART (USS Product)
    	$('.contentTitle').livequery('click', function(){
    		var parent = $(this).closest('.getStartedContentTask');
    	    $('.contentDescription', parent).slideToggle();
    	});

		// Adding GET STARTED button pop window in the content list  - TRADE SMART (USS Product)
		$("a.getStartedContentTask").livequery('click', function (e){
		    var module_name = $(this).attr('module_name');
		    var url = 'index.php?module=webBasic_content&_spAction=startedContentTask&module_name=' + module_name + '&showHTML=0';
		    var exp = {
		        url: url
		    };
		    Util.openDialogForLink('Get Started',  1000, 500, 0, exp);
		});

    	$("#nav .hlist ul li a span").addClass('inner');
    	$("#nav .hlist ul li a").blend();

        $("ul.homeTop li").livequery('click', function(){
            $(this).children("ul.sub").slideToggle();
        });

        $("ul.homeTop font a").livequery('click', function(){
            $(this).children("ul.sub").slideToggle();
        });

        $(".leftnavShowHide").livequery('click', function(){
            $('#col1').slideToggle('fast', function() {
                $('.leftnavShowHide').toggleClass('leftnavShowHideicon', $('#col1').is(':hidden'));
            });

            $('#col3').addClass('fullleftlist');

        });


        /*$("ul.homeTop li").hover(function () { //When trigger is hovered...
            //$(this).children("ul.sub").slideDown('fast').show();
            $(this).children("ul.sub").slideToggle()
            }, function () {
            //$(this).children("ul.sub").slideUp('slow');
            //$(this).children("ul.sub").slideUp(100);
        });*/


    	$('.contentScroller, .m-common_dashboard .widget div.tableOuter').addClass('scroll-pane');
    	/*$('.scroll-pane').jScrollPane(
    	    {}
    	);*/

    	if ($('.tplLogin').length > 0){
    	    var toSubtract = $('#header').outerHeight(true) + $('#footer').outerHeight(true);
    	    var mainPanelHt = $(window).height() - toSubtract - 20;
    	    $('#col3_content').css({'height' : mainPanelHt + 'px', overflow: 'auto', 'overflow-x': 'hidden'});
    	    //$("#col3_content #loginOuter").cp_center();
    	}

        $(".heading-title").livequery('click', function(){
            var parent = $(this).closest('.linkPortalWrapper');
            var container = $('.mediaFilesDisplayWrap, .linkPortalDataWrapper', parent);

            $(this).css('background', 'transparent');
            $(this).prev('div.toggle')
            .removeClass('plus, minus')
            .addClass(
                (!container.is(':hidden')) ? 'plus' :
                'minus');

            $('.mediaFilesDisplayWrap, .linkPortalDataWrapper', parent).toggle('fast');
        });

        $('.v-list .cpSearch td.dateRange .fld_date.hasDatepicker').livequery('change', function(e){
            var parent    = $(this).closest(".type-text.ym-fbox-text");
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var date      = dateCheck.replace(/-/g, "/");
                var newdate   = new Date(date);
                var dd = ("0" + newdate.getDate()).slice(-2);
                var mm = ("0" + (newdate.getMonth() + 1)).slice(-2)
                var y  = newdate.getFullYear();
     
                var endDate = dd + '-'+ mm + '-' + y;
            }else {
                var endDate = "";
            }

            $('.hiddenDateDisplay', parent).val(endDate);
        });


        $('.v-list .cpSearch div.type-text.ym-fbox-text.dateRange .fld_date.hasDatepicker').livequery('change', function(e){
            var parent    = $(this).closest(".type-text.ym-fbox-text");
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var date      = dateCheck.replace(/-/g, "/");
                var newdate   = new Date(date);
                var dd = ("0" + newdate.getDate()).slice(-2);
                var mm = ("0" + (newdate.getMonth() + 1)).slice(-2)
                var y  = newdate.getFullYear();
     
                var endDate = dd + '-'+ mm + '-' + y;
            }else {
                var endDate = "";
            }

            $(this).next('.hiddenDateDisplay').val(endDate);
            //$('.hiddenDateDisplay', parent).val(endDate);
        });

        $('.v-list .type-text.ym-fbox-text .fld_date.hasDatepicker, .v-edit .fld_date.hasDatepicker, .v-new .fld_date.hasDatepicker').livequery('change', function(e){
            var parent    = $(this).closest(".type-text.ym-fbox-text");
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var date      = dateCheck.replace(/-/g, "/");
                var newdate   = new Date(date);
                var dd = ("0" + newdate.getDate()).slice(-2);
                var mm = ("0" + (newdate.getMonth() + 1)).slice(-2)
                var y  = newdate.getFullYear();
     
                var endDate = dd + '-'+ mm + '-' + y;
            }else {
                var endDate = "";
            }

            $('.hiddenDateDisplay', parent).val(endDate);
        });

        $('.v-edit input.hiddenDateDisplay, .v-new input.hiddenDateDisplay').livequery('change', function(e){
            var parent    = $(this).closest(".type-text.ym-fbox-text");
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var res = dateCheck.split("-");
                var dd = res[0];
                var mm = res[1];
                var y  = res[2];
     
                var endDate = y + '-'+ mm + '-' + dd;
            }else {
                var endDate = "";
            }

            $('input.MainDateField', parent).val(endDate);
        });

        $('.v-list table.cpSearch .type-text.ym-fbox-text .fld_date.hasDatepicker').livequery('change', function(e){
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var date      = dateCheck.replace(/-/g, "/");
                var newdate   = new Date(date);
                var dd = ("0" + newdate.getDate()).slice(-2);
                var mm = ("0" + (newdate.getMonth() + 1)).slice(-2)
                var y  = newdate.getFullYear();
     
                var endDate = dd + '-'+ mm + '-' + y;
            }else {
                var endDate = "";
            }

            $(this).next("input.hiddenDateDisplay").val(endDate);
        });

        $('.v-list table.cpSearch .type-text.ym-fbox-text input.hiddenDateDisplay').livequery('change', function(e){
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var res = dateCheck.split("-");
                var dd = res[0];
                var mm = res[1];
                var y  = res[2];
     
                var endDate = y + '-'+ mm + '-' + dd;
            }else {
                var endDate = "";
            }

            $(this).prev("input.MainDateField").val(endDate);
        });

        $('.v-list table.cpSearch td.dateRange .fld_date.hasDatepicker').livequery('change', function(e){
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var date      = dateCheck.replace(/-/g, "/");
                var newdate   = new Date(date);
                var dd = ("0" + newdate.getDate()).slice(-2);
                var mm = ("0" + (newdate.getMonth() + 1)).slice(-2)
                var y  = newdate.getFullYear();
     
                var endDate = dd + '-'+ mm + '-' + y;
            }else {
                var endDate = "";
            }

            $(this).next("input.hiddenDateDisplay").val(endDate);
        });

        $('.v-list table.cpSearch td.dateRange input.hiddenDateDisplay').livequery('change', function(e){
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var res = dateCheck.split("-");
                var dd = res[0];
                var mm = res[1];
                var y  = res[2];
     
                var endDate = y + '-'+ mm + '-' + dd;
            }else {
                var endDate = "";
            }

            $(this).prev("input.MainDateField").val(endDate);
        });

        $('#reportSearchPanel table.search td.dateRange .fld_date.hasDatepicker').livequery('change', function(e){
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var date      = dateCheck.replace(/-/g, "/");
                var newdate   = new Date(date);
                var dd = ("0" + newdate.getDate()).slice(-2);
                var mm = ("0" + (newdate.getMonth() + 1)).slice(-2)
                var y  = newdate.getFullYear();
     
                var endDate = dd + '-'+ mm + '-' + y;
            }else {
                var endDate = "";
            }

            $(this).prev("input.hiddenDateDisplay").val(endDate);
        });

        $('#reportSearchPanel table.search td.dateRange input.hiddenDateDisplay').livequery('change', function(e){
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var res = dateCheck.split("-");
                var dd = res[0];
                var mm = res[1];
                var y  = res[2];
     
                var endDate = y + '-'+ mm + '-' + dd;
            }else {
                var endDate = "";
            }

            $(this).next("input.MainDateField").val(endDate);
        });

        // Adding help button pop window in the content list  - TRADE SMART (USS Product)
        $(".takeVirtualTourLink1").livequery('click', function (e){
            var module_name = $(this).attr('module_name');
            var url = 'index.php?module=webBasic_content&_spAction=takeVirtualTour&showHTML=0';
            var exp = {
                url: url
            };
            Util.openDialogForLink('Take a virtual tour',  1000, 635, 0, exp);
        });

        /*$('#vidBox').VideoPopUp({
            // trigger element
            opener: "popupVideoVirtualTour",
            // video ID
            idvideo: "example"
        });*/
    },

    //Auto select patient details
    mobileSearch: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: 'index.php?module=manPower_candidate&_spAction=searchStudentMobile&showHTML=0',
                    dataType: "json",
                    data: request,                    
                    success: function (data) {
                    // No matching result
                    if (data.length == 0) {
                        response("");
                    }
                    else {
                      response(data);
                    }

                    }
                });
            },
            minLength : 1,
            electFirst: true,
            autoFocus: true,
            select: function(event, ui) {
                var selectedObj = ui.item;
                var student_id  = selectedObj.id
                var student_mobile_no = selectedObj.student_mobile_no
                var first_name = selectedObj.first_name
                var last_name = selectedObj.last_name
                var email_address = selectedObj.email_address
                
                $('input[name=mobile_search]').val(student_mobile_no);
                $('input[name=first_name]').val(first_name);
                $('input[name=last_name]').val(last_name);
                $('input[name=email_address]').val(email_address);
                $('input[name=mobile]').val(student_mobile_no);
            }
        });
    },

    emailSearch: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: 'index.php?module=manPower_candidate&_spAction=searchStudentEmail&showHTML=0',
                    dataType: "json",
                    data: request,                    
                    success: function (data) {
                    // No matching result
                    if (data.length == 0) {
                        response("");
                    }
                    else {
                      response(data);
                    }

                    }
                });
            },
            minLength : 1,
            electFirst: true,
            autoFocus: true,
            select: function(event, ui) {
                var selectedObj = ui.item;
                var student_id  = selectedObj.id
                var student_mobile_no = selectedObj.student_mobile_no
                var first_name = selectedObj.first_name
                var last_name = selectedObj.last_name
                var email_address = selectedObj.email_address
                
                $('input[name=email_search]').val(email_address);
                $('input[name=first_name]').val(first_name);
                $('input[name=last_name]').val(last_name);
                $('input[name=email_address]').val(email_address);
                $('input[name=mobile]').val(student_mobile_no);
            }
        });
    },
}

function DropDown(el) {
                this.dd = el;
                this.placeholder = this.dd.children('span');
                this.opts = this.dd.find('ul.dropdown > li');
                this.val = '';
                this.index = -1;
                this.initEvents();
            }
            DropDown.prototype = {
                initEvents : function() {
                    var obj = this;

                    obj.dd.livequery('click', function(event){
                        $(this).toggleClass('active');
                        return false;
                    });

                    obj.opts.livequery('click',function(){
                        var opt = $(this);
                        obj.val = opt.text();
                        obj.index = opt.index();
                        obj.placeholder.text(obj.val);
                    });
                },
                getValue : function() {
                    return this.val;
                },
                getIndex : function() {
                    return this.index;
                }
            }

            $(function() {

                var dd = new DropDown( $('#dd') );

                $(document).click(function() {
                    // all dropdowns
                    $('.wrapper-dropdown-3').removeClass('active');
                });

            });