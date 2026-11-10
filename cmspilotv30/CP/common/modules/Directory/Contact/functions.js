Util.createCPObject('cpm.directory.contact');

cpm.directory.contact.init = function(){
    $(window).load(function(){
        $('.directory_contact__directory_preferenceLink select[name=category_id]').livequery(function(){
            cpm.directory.contact.populateSubCategory.call(this);
        });

        $('.directory_contact__directory_preferenceLink select[name=category_id]').livequery('change', function(){
            cpm.directory.contact.populateSubCategory.call(this);
        });

        $('.directory_contact__directory_areaLink select[name=country_id]').livequery(function(){
            cpm.directory.contact.populateArea.call(this);
        });

        $('.directory_contact__directory_areaLink select[name=country_id]').livequery('change', function(){
            Util.showProgressInd();
            cpm.directory.contact.populateArea.call(this);
        });
    });
}

cpm.directory.contact.populateSubCategory = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
        catId = $(this).val();
        subCatObj = $('select[name=sub_category_id]', parent);
        subCatId = subCatObj.val();
        var url = $('#scopeRootAlias').val() + 'index.php?module=webBasic_subCategory&_spAction=getSubcatJsonByCatId&showHTML=0'

        $.ajax({
            type: "POST",
            url: url,
            async: false,
            dataType: 'json',
            success: function(json){
                firstOptObj = $('option:eq(0)', subCatObj);
                firstLabel = firstOptObj.text();
                firstVal = firstOptObj.val();
                subCatObj.empty();
                
                if(firstVal == ''){
                    subCatObj.append(new Option(firstLabel, firstVal));
                }
                
                $.each(json, function() {
                    subCatObj.append(new Option(this.caption, this.value));
                    subCatObj.val(subCatId);
                });
            },
            data: {category_id: catId, sub_category_id: subCatId}
        });
    });
}

cpm.directory.contact.populateArea = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
        countryId = $(this).val();
        areaObj = $('select[name=area_id]', parent);
        areaId = areaObj.val();
        var url = $('#scopeRootAlias').val() + 'index.php?_spAction=jsonForDropdown&showHTML=0'

        $.ajax({
            type: "POST",
            url: url,
            async: false,
            dataType: 'json',
            success: function(json){
                areaObj.empty();
                //var json = '[{"value":"1","label":"xyz"}, {"value":"2","label":"abc"}]';
                //jQuery.parseJSON(json).map(function (){
                //    return $('<option>').val(this.value).text(this.label);
                //}).appendTo(areaObj);

                //$.each(json, function() {
                //    areaObj.append(new Option(this.caption, this.value));
                //    areaObj.val(areaId);
                //});
                for (var i = 0; i < json.length; i++) {
                    var item = json[i];
                    areaObj.append(
                        new Option(item.caption, item.value)
                    );

                    //$('option', areaObj).map(function() {
                    //    if ($(this).value() == areaId) return this;
                    //}).attr('selected', 'selected');

                    areaObj.val(areaId);
                }
                
            },
            data: {room: 'directory_area', srcFld: 'country_id', srcValue: countryId}
        });

        Util.hideProgressInd();
    });
}