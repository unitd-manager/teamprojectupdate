Util.createCPObject('cpm.webBasic.content');

cpm.webBasic.content = {
    init: function(){
        $('.m-webBasic_content.rt-list-detail-combo .showHideDesc')
        .click(cpm.webBasic.content.showLongDescription);
    },

    showLongDescription: function(e) {
        e.preventDefault();
        
        //<div class='long-description'>lorem ipsum</div>
        //<div><a>show more</a></div>
        $(this).parent().siblings('.long-description').slideToggle(function() {
            var top = $(this).parents('.subcolumns').offset().top;
            $('html, body').animate({scrollTop: top}, 1000);
        });
        $(this).parent().siblings('.short-description').slideToggle();
        $(this).find('span').toggle();
    }
}
