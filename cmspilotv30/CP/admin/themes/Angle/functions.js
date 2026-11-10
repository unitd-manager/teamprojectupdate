Util.createCPObject('cpt.angle');

cpt.angle = {
	init: function(){

        window.onload = getStartedContent();
        function getStartedContent() {
            var popupSession = $('#getStartedPopupOnloadSession').val();

            if(popupSession == ''){
                setTimeout(function() {
                    $("a.getStartedContentTask").trigger('click');
                },100);
            }
        }

        $('.leftNav .hlist ul li.first').livequery('click', function(){
            var parent = $(this).closest('li');
            parent.next('ul.displayNone').slideToggle();
        });

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
    	    $("#col3_content #loginOuter").cp_center();
    	}

    	$("table.search td select").change(function() {
    	    $('#searchTop').submit();
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