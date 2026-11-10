Util.createCPObject('cpt.carolina');

cpt.carolina.init = function(){
}

LoadReady.makeScrollableTable = function(){
    var toSubtract1 = $('#header').outerHeight(true) + $('#nav').outerHeight(true) + $('#nav2').outerHeight(true) + $('#extended').outerHeight(true) + $('#footer').outerHeight(true);
    var toSubtract2 = parseInt($('#main').css('padding-top')) + parseInt($('#main').css('padding-bottom'));
    var toSubtract3 = parseInt($('#col3_content').css('padding-top')) + parseInt($('#col3_content').css('padding-bottom'));
    var toSubtract4 = parseInt($('#main .page').css('padding-top')) + parseInt($('#main .page').css('padding-bottom'));
    toSubtract4 = (toSubtract4 || 0);
    var toSubtract5 = 40;

    var cpRoom = $('#cpRoom').val();
    if (cpRoom == 'account_reports'){
        var toSubtract5 = 30;
    }

    var mainPanelHt = $(window).height() - toSubtract1 - toSubtract2 - toSubtract3 - toSubtract4 - toSubtract5;
    //alert(mainPanelHt);
    
    if ($('#cpRoom').val() != 'common_dashboard'){
        $('.contentScroller').css({'height' : mainPanelHt + 'px', overflow: 'auto'});
        $('#col1').css({'height' : mainPanelHt + 'px', overflow: 'auto', 'overflow-x': 'hidden'});
    }

    if (cpRoom == 'account_reports'){
        var reportHt = parseInt(mainPanelHt - 100);
        $('#reportContainer').css({'height' : reportHt + 'px', overflow: 'auto'});
    }
}
