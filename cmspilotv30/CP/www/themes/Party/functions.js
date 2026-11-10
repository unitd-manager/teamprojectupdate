Util.createCPObject('cpt.party');

cpt.party = {
    isOnHoverImage: false,

    init: function(){
        $('.thumb-image-list .inner .pic img')
        .click(cpt.party.showHidePopImage);

        $('.hover-container .hover')
        .click(cpt.party.hideLargeHoverImg);

        $('body')
        .click(cpt.party.hideAllPopupImgs);

        $('.btn-remindme')
        .click(cpt.party.remindMeForm);

        $('.ui-dialog #frmRemindMe #cancelBtn')
        .live('click', function() {
            Util.closeAllDialogs();
        });
    
        $('.ui-dialog #frmRemindMe #remindMeBtn')
        .live('click', cpt.party.remindMe);

        //if payment is done then show reminder dialog
        //Also show the same dialog for if remind me popup link is clicked (ex: from an invite email)
        if ($('#paymentThanks').val() === '1' || $('#remindMePopup').val() === '1') {
            $('.btn-remindme').click();
        }
    },
        
    howItWorks: function(e){
        $('.w-media-carousel .prev').hide();
        $('.w-media-carousel .next').hide();
        $('.w-media-carousel').hover(function() {
            $('.w-media-carousel .prev').show();
            $('.w-media-carousel .next').show();
        }, function() {
            $('.w-media-carousel .prev').hide();
            $('.w-media-carousel .next').hide();
        });
    },

    showHidePopImage: function(e){
        var thumbImg = $(this);
        var imgId = thumbImg.attr('image_id');
        
        //current thumbnail's popup image
        var currPopupImg = $('.hover-container').find('[image_id=' + imgId + ']').filter(':visible');
        if (currPopupImg.length === 0) {//if no popup image of current thumb is showing
            //hide previous popup image. Call the function as if clicking from the body to satisfy the logic there.
            $('.hover-container .hover').filter(':visible').fadeOut();

            //show the popup image for the current thumb
            cpt.party.showPopupImg.call(thumbImg);
        } else {
            //hide the popup image for the current thumb
            cpt.party.hideLargeHoverImg.call(currPopupImg);
        }
    },
        
    showPopupImg: function(e){ 
        cpt.party.isOnHoverImage = false;
        var image_id = $(this).attr('image_id');
        var top = 140;
        var left = -20;
        //var hover = $(pic.parent().siblings('.hover'));
        var hover = $('.hover-container').find('[image_id=' + image_id + ']');
        if (hover.css('display') === 'block') {
            return;
        }

        $(hover)
        .css({'top': top + 'px', 'left': left + 'px'})
        .hide()
        .fadeIn(250);
    },

    hideAllPopupImgs: function(e){
        // console.log(e && e.target);
        // console.log(this);
        // console.log(e.target.tagName);
        
        //this = body tag which is the selector
        //target = the actual object clicked (thumb image or any div in the window)
        //if <body> != thumb <img> (or a DIV)
        if(e && e.target !== this) {
            //check if the target is a thumb <img> then do not proceed (otherwise the popup hides immediately)
            //else if the target is a <div> or anything else then proceed below
            if(e.target.tagName.toUpperCase() === 'IMG') {
                return;
            }
        }
        var prevPopupImg = $('.hover-container .hover').filter(':visible');
        prevPopupImg.fadeOut();
    },
        
    hideLargeHoverImg: function(e){
        var picCont = $(this);
        if (picCont.css('display') === 'block') {
            picCont.fadeOut();
        }
    },
        
    remindMeForm: function(e) {
        e.preventDefault();

        var paymentThanks = $('#paymentThanks').val();
        var url = '/index.php?module=party_partySetup&_spAction=remindMeForm' +
                  '&showHTML=0&paymentThanks=' + paymentThanks;
        var exp = {
            url: url
        };
        Util.openDialogForLink('', 610, 420, 0, exp);    
    },
        
    remindMe: function(e) {
        e.preventDefault();

        var selector = '#frmRemindMe input';
        var data = $(selector).serialize();

        var url = '/index.php?module=party_partySetup&_spAction=remindMe' +
                  '&showHTML=0';

        Util.showProgressInd();
        $.post(url, data, function (json) {
            Util.hideProgressInd();
            if (json.status === 'error') {
                Util.alert(json.errorMsg);
                return;
            }
            Util.alert(json.html, function() {
                Util.closeAllDialogs();
            });
        }, 'json');
    }
};

$(function() {
    if ($('#cpCurrentViewRecType').val() === 'How It Works') {
        var callHowItWorks = function() {
            if ($('.w-media-carousel .caroufredsel_wrapper').length > 0) {
                cpt.party.howItWorks();
            }
        };
        setTimeout(callHowItWorks, 1000);
    }
});

$(window).load(function() {
    if ($('#cpCurrentViewRecType').val() === 'How It Works') {
        //once all the images are loaded then make them visible
        $('.rt-how-it-works .w-media-carousel .carousel li img').css('display', 'block');
    }
});
