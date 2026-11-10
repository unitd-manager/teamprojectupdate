Util.createCPObject('cpw.ui.accordian');

cpw.ui.accordian.run = function(exp){
    var handle = exp.handle;
    var fadeInSpeed = exp.fadeInSpeed;
    var toggleOnSecondClick = Util.stringToBoolean(exp.toggleOnSecondClick)
    
    if (toggleOnSecondClick){
        $('#' + handle + ' h2').click(function(){
            $(this).next().slideToggle(exp.fadeInSpeed);
        });

    } else {
        $('#' + handle).tabs('#' + handle + ' div.pane', {
             tabs: 'h2'
            ,effect: 'slide'
            ,initialIndex: null
            ,fadeInSpeed: exp.fadeInSpeed
            ,onClick: function(){
                //alert(this.getCurrentPane().css('display'));
            }
        });
    }
}
