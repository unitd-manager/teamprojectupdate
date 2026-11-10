Util.createCPObject('cpm.wine.product');

cpm.wine.product = {
    init: function(){
        $('#frmEdit select#fld_country_code').livequery('change', function(){
            Util.loadDropdownByJSON('country_code', $(this).val(), 'fld_region_id', 'common_region');
        });

        $('#frmEdit select#fld_region_id').livequery('change', function(){
            Util.loadDropdownByJSON('region_id', $(this).val(), 'fld_appellation_id', 'wine_appellation');
        });

        $(window).load(function(){
            $('.wine_product__ecommerce_countryLink select[name=country_id]').livequery(function(){
               cpm.wine.product.populateCityByCountry.call(this);
            });

            $('.wine_product__ecommerce_countryLink select[name=country_id]').livequery('change', function(){
                cpm.wine.product.populateCityByCountry.call(this);
            });
        });

        $('#frmEdit select#fld_category_id').livequery('change', function(){
           Util.loadSubCategoryDropdown.call(this);
        });

    } ,
    
    populateCityByCountry: function(){
        $(this).each(function(){
            var parent = $(this).closest('tr');
            var parentId = $(this).val();
            var childWrapper = parent.next();

            $('select[name=city_id]', childWrapper ).each(function(){
                var childObj = $(this);
                var childId = childObj.val();
                var url = $('#scopeRootAlias').val() + 'index.php?_spAction=jsonForDropdown&showHTML=0'

                $.ajax({
                    type: "POST",
                    url: url,
                    async: false,
                    dataType: 'json',
                    success: function(json){
                        childObj.empty();
                        $.each(json, function() {
                            childObj.append(new Option(this.caption, this.value));
                            childObj.val(childId);
                        });
                    },
                    data: {room: 'directory_city', srcFld: 'country_id', srcValue: parentId}
                });             
            });
        });
    },
    
    importFromJDE: function (room_name, importType) {
        w = 700;
        h = 600;
        windowString = "height=" + h + ",width=" + w + ",scrollbars=yes," +
        "resizable=yes,left=" + (screen.width-w)/2 + ",top=" +
        (screen.height-h)/2;

        var url = "index.php?_spAction=importFromJDE&module=wine_product&showHTML=0";
        wind = window.open(url, "import from JDE", windowString);
    }
}


var Links = $.extend(Links, {
    addNewGridRecord: function(e) {
        e.preventDefault();
        var linkName      = $(this).closest('.linkPortalWrapper').attr('id');
        var lnkRoomActual = $(this).closest('.linkPortalWrapper').attr('lnkRoomActual');
        var url = $(this).attr('link');
        var recId = $(this).attr('recId');
        var exp = {
            portalDiv: $(this).closest('.linkPortalWrapper')
        }

        $.post(url, function(data){
            if(linkName == 'wine_product#ecommerce_countryLink' || linkName == 'ecommerce_countryLink#ecommerce_cityLink'){
                location.reload(true);
            } else {
                Links.reloadPortalRecords(linkName, lnkRoomActual, recId, 'edit', exp);
            }
//            Links.reloadPortalRecords(linkName, lnkRoomActual, recId, 'edit', exp);
//            $('.wine_product__ecommerce_countryLink select[name=country_id]').livequery(function(){
//                cpm.wine.product.populateCityByCountry.call(this);
//            });
//
//            $('.wine_product__ecommerce_countryLink select[name=country_id]').livequery('change', function(){
//                cpm.wine.product.populateCityByCountry.call(this);
//            });            
        });
    },    

    updateGridData: function(){
        var linkName      = $(this).closest('.linkPortalWrapper').attr('id');
        var lnkRoomActual = $(this).closest('.linkPortalWrapper').attr('lnkRoomActual');
        var url = $(this).attr('link');
        var recId = $(this).attr('recId');
        var exp = {
            portalDiv: $(this).closest('.linkPortalWrapper')
        }
        
        var tbl = $(this).closest('table.grid');
        var fldObj = $(this);
        if (fldObj.attr('validation') !== undefined) {
           var validationType = fldObj.attr('validation');
           var fldVal = fldObj.val();
            if (validationType == 'number' && isNaN(fldVal)){
                Util.alert('Please input a valid numeric value');
                fldObj.focus();
                return false;
            }

            if (validationType == 'integer' && (isNaN(fldVal) || !(fldVal+"").match(/^\d+$/))){
                Util.alert('Please input a valid integer value');
                fldObj.focus();
                return false;
            }
        }

        var tr  = $(this).closest('tr');
        var frmObj  = $(this).closest('form');
        var saveUrl = tbl.attr('saveUrl');
        var keyFld  = tbl.attr('keyFld');
        var id  = tr.attr('recId');
        var url = saveUrl + '&' + keyFld + '=' + id;

        //var row={};
        //$(tr).find('input,select,textarea').each(function(){
        //    row[$(this).attr('name')]=$(this).val();
        //});

        row = $(tr).serializeAnything()+ '&' + keyFld + '=' + id;
        Util.showProgressInd('Saving data...');
        if(linkName == 'wine_product#ecommerce_countryLink' || linkName == 'ecommerce_countryLink#ecommerce_cityLink'){
            $.post(url, row, function(data){
                var errorCount = data.errorCount;

                if(errorCount != 0 && data.msg != ''){
    //                Links.reloadPortalRecords(linkName, lnkRoomActual, recId, 'edit', exp);
    //                $('.wine_product__ecommerce_countryLink select[name=country_id]').livequery(function(){
    //                    cpm.wine.product.populateCityByCountry.call(this);
    //                });
    //
    //                $('.wine_product__ecommerce_countryLink select[name=country_id]').livequery('change', function(){
    //                    cpm.wine.product.populateCityByCountry.call(this);
    //                });                   
                    Util.showSimpleMessageInDialog(data.msg, true);
                }
                Util.hideProgressInd();
            }, 'json');
        } else {
            $.post(url, row, function(data){
                Util.hideProgressInd();
            });            
        }
    }    
});

