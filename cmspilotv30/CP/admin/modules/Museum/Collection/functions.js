Util.createCPObject('cpm.museum.collection');

cpm.museum.collection = {
    init: function(){
        $('#frmEdit select#fld_category_id').livequery('change', function(){
            Util.loadSubCategoryDropdown.call(this);
        });
    },
    
    updateFlickrCache: function(){
        url = "index.php?_spAction=updateFlickrCache&module=museum_collection&showHTML=0";
        w = 400;
        h = 400;
        windowString = "height=" + h + ",width=" + w + ",scrollbars=yes," +
        "resizable=yes,left=" + (screen.width-w)/2 + ",top=" +
        (screen.height-h)/2
        wind = window.open( url , "updateFlickrCache", windowString);
    }
}