Util.createCPObject('cpt.korban');

cpt.korban.init = function(){      
   $('.rt-gallery .galleryList ul li:nth-child(3n) .inner').css('margin-right', '0');
   $('.loginInfoText').addClass('button');
   $('.readMore').addClass('button');

    $(function () {
        $('.btnProceedToConfirm a').unbind('click');

        $('.btnProceedToConfirm a').click(function(e){
            e.preventDefault();
            
            if ($('.w-ecommerce-productList input[type=checkbox]:checked').length == 0){
                Util.alert('Please check atleast one of the items before proceed.', function(){
                    $('html,body').animate({scrollTop: $('#header').offset().top},'slow');
                })
                return false;
            }
            
            var hasError = false;
            $('.w-ecommerce-productList input[type=checkbox]:checked').each(function(){
                var parent = $(this).closest('tr');
                var notesObj = $('.notesWrap textarea', parent);
                if(notesObj.val() == ''){
                    hasError = true;
                }
            });
            
            if (hasError){
                Util.alert('Please enter the name(s) of individuals', function(){
                    $('html,body').animate({scrollTop: $('#header').offset().top},'slow');
                })
            } else {
                $('form#frmShippingDetails').submit();
            }
        });
    });
}