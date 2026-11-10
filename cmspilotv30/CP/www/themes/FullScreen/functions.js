Util.createCPObject('cpt.fullScreen');

cpt.fullScreen = {
    init: function(){
        var cpCurrentViewRecType = $('#cpCurrentViewRecType').val();
        
	    $('.listInDetail .inner, ' +
          '.m-gallery_project .projectList .c60r,' +
          '.m-gallery_project .projectDetail .c50l .inner, ' +
          '.fatList, ' + 
          '.p-common-siteSearch'
         ).addClass('scroll-pane');

        var toSubtract = $('#header').outerHeight(true);
        var marginTop = $(window).height() - toSubtract - 70;

	    $('.rt-news .inner.scroll-pane, ' + 
          '.rt-career .inner.scroll-pane, ' + 
          '.p-common-siteSearch.scroll-pane')
        .css('height', marginTop + 'px');
        
        $('ul#thumb-list li:last').livequery(function(){
            $(this).addClass('last');
        });

        cpt.fullScreen.setupRightSlider();
        cpt.fullScreen.setupProjectStuff();
        
        if    (cpCurrentViewRecType != 'News'
            && cpCurrentViewRecType != 'Career'
            && cpCurrentViewRecType != 'Site Search'
            && cpCurrentViewRecType != 'Flipbook Form'
           ){
            cpt.fullScreen.setupMarginForMainContainer();
            $(window).resize(cpt.fullScreen.setupMarginForMainContainer);
        }

	    $('.scroll-pane').jScrollPane({
             verticalDragMinHeight: 50
            ,verticalDragMaxHeight: 100
             ,contentWidth: '0px'
			}
        );
        //---News---//
        if (cpCurrentViewRecType == 'News') {
            cpt.fullScreen.setupNewsStuff();
        }
        if (cpCurrentViewRecType == 'People') {
            cpt.fullScreen.showPeopleName();
            $(window).load(cpt.fullScreen.displayPeoplePhotosInList);
            $(window).resize(cpt.fullScreen.displayPeoplePhotosInList);
        }

        if ($('.rt-home').length > 0){
            cpt.fullScreen.setupHomeStuff();
        }

        $('a.prettyPhoto').prettyPhoto({
             social_tools: false
            ,allow_resize: false
            ,deeplinking: false
        });
    },

    setupRightSlider: function() {
        $('#rightSlide').hover(
            function() {
                $('#siteSearch .keywordWrap').css('left', 0);
                $('#siteSearch div.submit').css('left', 'auto');
                $('#siteSearch div.submit').css('right', 0);
                $('#rightSlide').animate({
                    right:0
                }, 1000);
//                $('#rightSlide .inner .shareIcons').fadeIn();
            },
            function () {
                setTimeout( function () {
                    $('#siteSearch .keywordWrap').css('left', '34px');
                    $('#siteSearch div.submit').css('left', 0);
                    $('#rightSlide').animate({
                        right:'-125px'
                    }, 750);
                }, 1000);
            }
        );

        $('#rightSlide .socialMediaIcons .handle,' + 
          '#rightSlide .socialMediaIcons #showShareIcons').hover(
            function () {
                $('#rightSlide .inner .shareIcons').fadeIn();
            },
            function () {
                setTimeout(function () {
                    if ($('#rightSlide .inner .shareIcons').data('hovering') != 1) {
                        $('#rightSlide .inner .shareIcons').fadeOut();
                    }
                }, 1000);
            }
        );

        $('#rightSlide .inner .shareIcons').hover(
            function() {
                $.data(this, 'hovering', 1);
            },
            function() {
                $.data(this, 'hovering', 0);
                $('#rightSlide .inner .shareIcons').fadeOut();
            }
        );        
    },
    
    setupProjectStuff: function() {
        $('.rt-project .projectList .c60r .projectTitle a').hover(
            function () {
                var pic = $(this).attr('pic');
                if (pic != ''){
                    $('.rt-project .projectList .pic').html("<img src='" + pic + "'>");
                    $('.rt-project .projectList .desc').hide();
                }
            },
            function () {
                $('.rt-project .projectList .pic').html('');
                $('.rt-project .projectList .desc').show();
            }
        );        

        //new thumbnail
        $('.rt-project .projectList .c60r .projectTitle a').hover(
            function () {
                var pic = $(this).attr('pic');
                if (pic != ''){
                    $('.rt-project .projectList .pic').html("<img src='" + pic + "'>");
                    $('.rt-project .projectList .desc').hide();
                }
            },
            function () {
                $('.rt-project .projectList .pic').html('');
                $('.rt-project .projectList .desc').show();
            }
        );

        if ($('.m-gallery_project.rt-project.v-detail').length > 0){
            $.idleTimer(2000);
            $(document).bind("idle.idleTimer", function(){
                $('#nav').hide('fade',{direction:'down'},1000);
                if ($('#main').is(':visible')){
                    $('#main').hide('slide',{direction:'down'},1000);
                }
            });

            $(document).bind("active.idleTimer", function(){
                $('#nav').show('fade',{direction:'down'},1000);
            });

            $('.m-gallery_project.v-detail #projectBtmDummyWrapper').hover(
                function () {
                    if ($('#main').is(':hidden')){
                        $('#main').show('slide',{direction:'down'},1000);
                    }
                },
                function () {
                }
            );
        }
    },
    
    setupHomeStuff: function() {
        $('.rt-home #bottomContentWrapper').hover(
            function(e) {
                var targetId = $(e.target).attr('id');
                if (targetId != 'bottomContentWrapper') {
                    return;
                }
                $('.rt-home #homeBtmOuter, .rt-home .bottomContent')
                .show('slide',{direction:'down'},1500);
            },

            function(e) {
                $('.rt-home #homeBtmOuter, .rt-home .bottomContent')
                .hide('slide',{direction:'down'},1500);
            }
        );
    },
    
    setupNewsStuff: function() {
        if ($('#cpAction').val() == 'detail') {
            return;
        }
        $('.rt-news .newsList a.detailLink').hover(
            function () {
                var pic = $(this).attr('pic');
                if (pic != ''){
                    $('.rt-news .newsList .detail .pic-list').show().html("<img src='" + pic + "'>");
                    $('.rt-news .newsList .detail .inner').hide();
                }
            },
            function () {
                $('.rt-news .newsList .detail .pic-list').hide().html('');
                $('.rt-news .newsList .detail .inner').show();
            }
        );        
    },
    
    setupMarginForMainContainer: function() {
        //set height for Firm > Profile, Firm > Value, Firm > Sustainability
        if ($('body').hasClass('rt-content')) {
            var height = $(window).height() * 0.45;
            $('.scroll-pane').css({'height' : height + 'px'}); 
        }
        // add margin top to main container //
        var toSubtract = $('#header').outerHeight(true) + $('#main').outerHeight(false);
        var marginTop = $(window).height() - toSubtract;
        $('#main').css({'margin-top' : marginTop + 'px'}); 
    },
    
    displayPeoplePhotosInList: function() {
        var screenWidth  = $(window).width();
            
        var totalPics = parseInt($('#peopleCount').val());
        var toSubtract = (totalPics*4) + 3;
        var imgWidth = (screenWidth - toSubtract) / totalPics;
        
        //set width
        css = {
           width: imgWidth
          ,display: 'block'
        };
        $('.rt-people .people-photos-in-list .pic img')
        .css(css);

        //set position
        var screenHeight = $(window).height();
        var mainPanelHeight = $('#main').outerHeight();
        var peopleContainerHeight = $('.people-photos-in-list .pic').outerHeight();
        var heightNoMainPanel = (screenHeight - mainPanelHeight);
        var topPos = heightNoMainPanel - peopleContainerHeight - (heightNoMainPanel * 0.1);
        var css = {
           top: topPos
          ,left: 0
          ,display: 'block'
        };
        $('.rt-people .people-photos-in-list')
        .css(css);
        

        
    },
    
    showPeopleName: function() {
        $('.people-photos-in-list .pic a').hover(
        function() {
            $(this).next('.name').slideDown(500);
        },
        function() {
            $(this).next('.name').slideUp(500);
        });
    }
    
    
}
            
// to remove the url in the supersized images ex: homepage //
$(window).load(function(){
    $('#supersized a').removeAttr('href');
});
