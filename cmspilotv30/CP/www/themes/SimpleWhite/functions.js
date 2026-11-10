Util.createCPObject('cpt.simpleWhite');

cpt.simpleWhite = {
    init: function(){
        $('.m-webBasic_content.rt-event .showHideDesc,' +
          '.m-webBasic_content.rt-news .showHideDesc')
        .click(cpt.simpleWhite.showLongDescription);
        $('ul.sf-menu').superfish();

        //slide in events/news
        var content_id = $('#fld_content_id').val();
        if (content_id) {
            $('div[content_id=' + content_id + '] span.more').click();
        }
    },

    showLongDescription: function(e) {
        e.preventDefault();

        //<div class='long-description'></div>
        //<div><a></a></div>
        $(this).parent().siblings('.long-description')
        .slideToggle(function() {
            var top = $(this).parents('.subcolumns').offset().top;
            $('html, body').animate({scrollTop: top}, 1000);
        });
        $(this).parent().siblings('.short-description')
        .slideToggle();
        $(this).find('span').toggle();
    }

}
