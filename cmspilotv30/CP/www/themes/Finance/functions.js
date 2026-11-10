Util.createCPObject('cpt.finance');

cpt.finance.init = function(){
    $("#col1_content #btnsBottom li a").cp_center();

    if(
        ($(document).height() > $(window).height() + 200) ||
        $('.rt-accordian-content').length > 0
       ){ 
        $('.toTop').show();
    } 

    $(".rt-corporate-governance table tr:first-child").addClass('first');

    //$('#jqToolsAccordian1 h2.current').livequery('click', function(){
    //    $(this).removeClass('current').next().hide();
    //});

}

cpt.finance.afterEnquiry = function(json, statusText, jqFormObj, extraParamOb){
    var formHt = $(jqFormObj).height();
    var successMsgFld = $('input[name=successMsg1]', jqFormObj);
    var successHeading = $('input[name=successHeading]', jqFormObj);

    $(jqFormObj).css('height', formHt + 'px');
    $('.pageTitle').html($(successHeading).val());
    $(jqFormObj).html($(successMsgFld).val());
    $('html,body').animate({scrollTop: $('body').offset().top},'slow');
    Util.hideProgressInd();
}
