<?
class CP_Www_Widgets_Media_Banner_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $global = false; // if true: will display the banner from content (content type = Global Banner) throughout the site
    var $strictToPage = false; // if false: will display the banner only if found for the exact section or category (no fallback)
    var $superSized = false; // if true: the superized jquery plugin will be used http://buildinternet.com/project/supersized/
    var $showNavForSuperSized = true; // if true: navigation will be shown as in http://buildinternet.com/project/supersized/slideshow/3.2/demo.html
    var $calloutContentType = 'Banner Callout';

    var $autoSlideShowForMultiBanners = true;
    var $autoSlideShowWidget = 'media_anythingSlider';
    var $autoSlideShowHeight = '';
    var $autoSlideShowWidth = '';
    var $showCaption = true;
    var $captionPosition = 'bottom';

    var $showDefaultBanner = false;
    var $defaultBanner  = '';
    var $defaultBannerCaption = '';
    var $mediaRecordType = 'banner'; // or picture
    
    var $subColLeft   = 'c50l'; // this will be used when description is available for picture
    var $subColRight  = 'c50r'; // this will be used when description is available for picture
}