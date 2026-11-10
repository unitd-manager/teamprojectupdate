Util.createCPObject('cpm.lawNews.jurisdiction');

cpm.lawNews.jurisdiction = {
    showSecondLogoNearWHeader:function(logoFile){
        var background = "transparent url(" + logoFile + ") no-repeat scroll right top";
        $('.showSecondLogo .newsAndAnalysis .w-content-record h2').css('background', background);
        $('.showSecondLogo .externalLinks   .w-content-record h2').css('background', background);
        $('.showSecondLogo .newsInBrief     .w-content-record h2').css('background', background);
    }
}

