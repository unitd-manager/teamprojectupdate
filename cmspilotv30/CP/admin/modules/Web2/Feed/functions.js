Util.createCPObject('cpm.web2.feed');

cpm.web2.feed = {
    updateFeed: function(){
        url = "index.php?_spAction=updateFeed&module=web2_feed&showHTML=0";
        w = 400;
        h = 400;
        windowString = "height=" + h + ",width=" + w + ",scrollbars=yes," +
        "resizable=yes,left=" + (screen.width-w)/2 + ",top=" +
        (screen.height-h)/2
        wind = window.open( url , "updateFeed", windowString);
    }
}