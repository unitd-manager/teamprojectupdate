Util.createCPObject('cpm.ecommerce.order');

cpm.ecommerce.order = {
    init: function(){
    },
    
    uploadToDHL: function(){
        url = "index.php?_spAction=uploadToDHL&module=ecommerce_order&showHTML=0";
        w = 400;
        h = 400;
        windowString = "height=" + h + ",width=" + w + ",scrollbars=yes," +
        "resizable=yes,left=" + (screen.width-w)/2 + ",top=" +
        (screen.height-h)/2
        wind = window.open( url , "uploadToDHL", windowString);
    }
}