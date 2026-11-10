Util.createCPObject('cpt.directory');

cpt.directory.init = function(){
    $(function(){
        $('.promotionsGrid ul li:nth-child(2n)').css('margin-right', 0);
        //$('.bodyPanel .w-core-subCat ul li a').addClass('centered');
 
        var toSubtract1 = parseInt($('#header').outerHeight(true)) + parseInt($('#footer').outerHeight(true));
        var toSubtract2 = parseInt($('#main').css('padding-top')) + parseInt($('#main').css('padding-bottom'));
        var mainPanelHt = $(window).height() - toSubtract1 - toSubtract2;
        $('.mainInner').css({'min-height' : mainPanelHt + 'px'});

        $('.businessList table tbody tr:odd').addClass('odd');
        $('.businessList table tbody tr:even').addClass('even');
        $('.contactList .row:nth-child(4n)').css('margin-right', 0);

	    $('.w-social-twitter #tweets').livequery(function(){
	        $(this).jScrollPane({
                 verticalDragMinHeight: 50
                ,verticalDragMaxHeight: 100
                ,contentWidth: '0px'
                ,autoReinitialise: true
			});
        });
        
	    $('.jscroll').livequery(function(){
	        $(this).jScrollPane({
                 verticalDragMinHeight: 50
                ,verticalDragMaxHeight: 100
                ,contentWidth: '0px'
                ,autoReinitialise: true
			});
        });

        if ($(".jsTreeMenu").length > 0){
        	$("#menuCatSubCat, #menuLocation, #menuMyLocations").jstree({ 
                 "plugins" : [ "themes", "html_data"]
                ,"themes" : {
                     "theme" : "apple"
                    ,"dots" : false
                    ,"icons" : false
                }
                ,'core': { 
                    'animation': 0,
                    'open_parents': true,
                    'initially_open': ['menuCatSubCat_html', 'menuLocation_html', 'menuMyLocations_html']
                }
            }).bind("loaded.jstree", function (event, data) { 
                $('>ul', this).slideDown();
                $(this).removeClass('cpLoading');
            });
        }

	    $('select[name=changeBusinessId]').livequery('change', function(){
	        document.location = $('#cpCurrentUriWithNoQstr').val() + '?changeBusinessId=' + $(this).val();
        });

	    $('select[name=_sortOrder]').livequery('change', function(){
	        $('#frmSortOrder').submit();
        });

	    $('#frmNotificationSettings').livequery('change', function(){
	        $('#frmNotificationSettings').submit();
        });

        $('.cpTabs').tabs().show();

	    $('.businessDetail #addReview').livequery('click', function(e){
	        e.preventDefault();
            $('.businessDetail .cpTabs').tabs( 'option', 'active', 1);
            $('a#toggleComments').hide();
            $('#commentForm').show();
        });

	    $('#col1 .menuWrapper span.groupTitle').livequery('click', function(e){
	        e.preventDefault();
            var parent = $(this).closest('li');
            $(this).next().toggle(function(){
                parent.toggleClass('jstree-closed');
            });
        });

	    $('#col1 #menuFeatures span.featureTitle').livequery('click', function(e){
	        e.preventDefault();
            var parent = $('#menuFeatures');
            $(this).next().toggle(function(){
                $('span.featureTitle').toggleClass('closed');
            });
        });
	    $('#frmFeatures input:checkbox').livequery('click', function(e){
	        $('#frmFeatures').submit();
        });

        $('#keyword').autocomplete({
            source: '/index.php?module=directory_search&_spAction=businessAsJSON&showHTML=0',
            select: function(event, ui) {
                $('#frmKeyword').submit();
            }
        });
        
 	    $('.p-common-comment .searchFilter select').livequery('change', function(){
	        cpt.directory.reloadComments.call(this);
        });

 	    $('.businessDetail a.reviewLink').click(function(e){
	        e.preventDefault();
	        var value = $(this).attr('val');
            $('.businessDetail .cpTabs').tabs( 'option', 'active', 1);
            $('.p-common-comment .searchFilter select').val(value).trigger('change');
        });
        
 	    $('#likes a.like').click(function(e){
 	        cpt.directory.likeButton.call(this,e);
        });
        
 	    $('.rt-my-guides a.removeBusiness').livequery('click', function(e){
 	        cpt.directory.removeGuideBusiness.call(this,e);
        });
    });
}

cpt.directory.reloadComments = function(){
    var comment_by = $(this).val();
    var url = $('input[name=p-common-comment_url]').val();
    $.get(url, {comment_by: comment_by}, function(data){
        $('.p-common-comment').addClass('to-remove');
        $(data).insertAfter('.p-common-comment');
        $('.p-common-comment.to-remove').remove();
        $('#frmComment').resetForm();
        $('#commentForm').hide('slow');
        Util.hideProgressInd();
    });
}

cpt.directory.likeButton = function(e){
    e.preventDefault();
    var title = $(this).attr('title');
    var width = $(this).attr('dlg-w');
    var height = $(this).attr('dlg-h');
    var afterOpen = $(this).attr('afterOpen');
    var updateUrl = $(this).attr('updateUrl');
    var exp = {
        onCloseFn: function(){
            $.get(updateUrl, function(html){
                $('#likes').html(html);
            });
        }
    }
    
    Util.openDialogForLink.call(this, title, width, height, true, exp);
}

cpt.directory.removeGuideBusiness = function(e){
    e.preventDefault();
    var business_id = $(this).attr('business_id');
    var guide_id = $('#record_id').val();
    
    var msg = 'Are you sure to remove this?'
    
    Util.confirm(msg, function(){
        var url = "/index.php?module=directory_guide&_spAction=removeBusinessFromGuide&showHTML=0";
        
        $.getJSON(url, {business_id: business_id, guide_id: guide_id}, function(json){
            if (json.success == true){
                $('.businessList .row[business_id=' + business_id + ']').slideUp("normal", function() { 
                    $(this).remove();
                });
            }
        });
    });
}
