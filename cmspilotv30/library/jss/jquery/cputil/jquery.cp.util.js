(function($) {
    $.fn.cp_emptySelect = function() {
      return this.each(function(){
        if (this.tagName=='SELECT') this.options.length = 0;
      });
    }
    
    $.fn.cp_loadSelect = function(optionsDataArray) {
      return this.cp_emptySelect().each(function(){
        if (this.tagName=='SELECT') {
          var selectElement = this;
          $.each(optionsDataArray,function(index,optionData){
            var option = new Option(optionData.caption,
                                    optionData.value);
            if ($.browser.msie) {
              selectElement.add(option);
            }
            else {
              selectElement.add(option,null);
            }
          });
        }
      });
    }

    $.fn.cp_openWindow = function(settings) {
        
        settings = $.extend({
            url: '',
            resizable: 'yes',
            scrollbars: 'yes',
            width: 800,
            height: 600,
            posX: (screen.width-800)/2,
            posY: (screen.height-600)/2
        }, settings || {});

        settings.posX = (screen.width-settings.width)/2;
        settings.posY = (screen.height-settings.height)/2;
        
        return this.each(function(){
            a = window.open(settings.url,"","height=" + settings.height + ",width=" + settings.width + ",scrollbars=" + settings.scrollbars +
             ",resizable=" + settings.resizable + ",left=" + settings.posX + ",top=" + settings.posY);
        });
    }  

    /*
     * Centers a an element within a container div vertically or horizontally
     * Example: 
     * <div>
     *    <div class="centered"></div>
     * </div>
     * $(".centered").center();
     * 
     * For vertical alignment only:
     * $(".centered").center({
     *    vertical: true
     * });
     */
     
    $.fn.cp_center = function(params) {
        var options = {
            vertical: true,
            horizontal: true
        }
        op = jQuery.extend(options, params);
        
        return this.each(function(){
            //initializing variables
            var $self = jQuery(this);

            //to get around the bug in Chrome/Safari
            $self.css({ width: "", height: "" });

            //get the dimensions using dimensions plugin
            var width = $self.width();
            var height = $self.height();
            //get the paddings
            var paddingTop = parseInt($self.css("padding-top"));
            var paddingBottom = parseInt($self.css("padding-bottom"));
            //get the borders
            var borderTop = parseInt($self.css("border-top-width"));
            var borderBottom = parseInt($self.css("border-bottom-width"));
            
            //get the paddings
            var paddingLeft  = parseInt($self.css("padding-left"));
            var paddingRight = parseInt($self.css("padding-right"));
            //get the borders
            var borderLeft = parseInt($self.css("border-left-width"));
            var borderRight = parseInt($self.css("border-right-width"));
            
            borderTop    = isNaN(borderTop)    ? 0 : borderTop;
            borderBottom = isNaN(borderBottom) ? 0 : borderBottom;
            borderLeft   = isNaN(borderLeft)   ? 0 : borderLeft;
            borderRight  = isNaN(borderRight)  ? 0 : borderRight;
            
            //get the media of padding and borders
            var mediaBorder = (borderTop+borderBottom)/2;
            var mediaPadding = (paddingTop+paddingBottom)/2;
            
            var mediaBorderH = (borderLeft+borderRight)/2;
            var mediaPaddingH = (paddingLeft+paddingRight)/2;
            //get the type of positioning
            var positionType = $self.parent().css("position");
            // get the half minus of width and height
            var halfWidth = ((width/2)*(-1))-mediaPaddingH-mediaBorderH;
            var halfHeight = ((height/2)*(-1))-mediaPadding-mediaBorder;
            // initializing the css properties
            var cssProp = {
             position: 'absolute'
            };
            
            if(op.vertical) {
                cssProp.height = height;
                cssProp.top = '50%';
                cssProp.marginTop = halfHeight;
            }
            if(op.horizontal) {
                cssProp.width = width;
                cssProp.left = '50%';
                cssProp.marginLeft = halfWidth;
            }
            
            //check the current position
            if(positionType == 'static') {
                $self.parent().css("position","relative");
            }
            //aplying the css
            $self.css(cssProp);
            
            
        });
    }

    $.fn.cp_ie6UpgardeWarning = function(params) {
        
        var msg = "You are using an unsupported browser. Please switch to " + 
                  "<a href='http://getfirefox.com'>FireFox</a>, "           + 
                  "<a href='http://www.microsoft.com/windows/downloads/ie/getitnow.mspx'>Internet Explorer 7</a>, " +
                  "<a href='http://www.opera.com/download/'>Opera</a>, or "    + 
                  "<a href='http://www.apple.com/safari/'>Safari</a>. "   + 
                  "Thanks!&nbsp;&nbsp;&nbsp;[<a href='#' id='warningClose'>close</a>]";

        var cssDef = {
            'background-color': '#fcfdde',
            'width': '100%',
            'border-top': 'solid 1px #000',
            'border-bottom': 'solid 1px #000',
            'text-align': 'center',
            padding:'5px 0px 5px 0px',
            display:'none'
        }                  

        if (params && params.css){
            var css = jQuery.extend(cssDef, params.css);
            params.css = css;
        } else {
            var css = cssDef;
        }
        
        var options = {
            cookieName: 'ie6Warning',
            expiredays: '1',
            message: msg,
            css: css
        }

        op = jQuery.extend(options, params);

        function badBrowser(){
            if($.browser.msie && parseInt($.browser.version) <= 6){return true;}
            
            return false;
        }
        
        function getBadBrowser(c_name){
            if (document.cookie.length>0)
            {
            c_start=document.cookie.indexOf(c_name + "=");
            if (c_start!=-1)
                { 
                c_start=c_start + c_name.length+1; 
                c_end=document.cookie.indexOf(";",c_start);
                if (c_end==-1) c_end=document.cookie.length;
                return unescape(document.cookie.substring(c_start,c_end));
                } 
            }
            return "";
        }   
        
        function setBadBrowser(c_name,value,expiredays){
            var exdate=new Date();
            exdate.setDate(exdate.getDate()+expiredays);
            document.cookie=c_name+ "=" +escape(value) + ((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
        }
        
        if(badBrowser() && getBadBrowser(op.cookieName) != 'seen' ){
            $(function(){
                $("<div id='browserWarning'>" + op.message + "</div> ")
                    .css(op.css)
                    .prependTo("body")
                    .slideDown(1000);
                
                $('#warningClose').click(function(){
                    setBadBrowser(op.cookieName, 'seen', op.expiredays);
                    $('#browserWarning').slideUp(1000);
                    return false;
                });
            }); 
        }
    }

})(jQuery);
 
(function( $ ) {

  $.fn.cp_resizeImage = function( params ) {

    var aspectRatio = 0
      // Nasty I know but it's done only once, so not too bad I guess
      // Alternate suggestions welcome :)
      ,	isIE6 = $.browser.msie && (6 == ~~ $.browser.version)
      ;

    // We cannot do much unless we have one of these
    if ( !params.height && !params.width ) {
      return this;
    }

    // Calculate aspect ratio now, if possible
    if ( params.height && params.width ) {
      aspectRatio = params.width / params.height;
    }

    // Attach handler to load
    // Handler is executed just once per element
    // Load event required for Webkit browsers
    return this.one( "load", function() {

      // Remove all attributes and CSS rules
      this.removeAttribute( "height" );
      this.removeAttribute( "width" );
      this.style.height = this.style.width = "";

      var imgHeight = this.height
        , imgWidth = this.width
        , imgAspectRatio = imgWidth / imgHeight
        , bxHeight = params.height
        , bxWidth = params.width
        , bxAspectRatio = aspectRatio;
				
      // Work the magic!
      // If one parameter is missing, we just force calculate it
      if ( !bxAspectRatio ) {
        if ( bxHeight ) {
          bxAspectRatio = imgAspectRatio + 1;
        } else {
          bxAspectRatio = imgAspectRatio - 1;
        }
      }

      // Only resize the images that need resizing
      if ( (bxHeight && imgHeight > bxHeight) || (bxWidth && imgWidth > bxWidth) ) {

        if ( imgAspectRatio > bxAspectRatio ) {
          bxHeight = ~~ ( imgHeight / imgWidth * bxWidth );
        } else {
          bxWidth = ~~ ( imgWidth / imgHeight * bxHeight );
        }

        this.height = bxHeight;
        this.width = bxWidth;
      }
    })
    .each(function() {

      // Trigger load event (for Gecko and MSIE)
      if ( this.complete || isIE6 ) {
        $( this ).trigger( "load" );
      }
    });
  };
})( jQuery );