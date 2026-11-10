<?
class CP_Www_Widgets_Content_Record_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $contentType       = 'Record';
    var $global            = true;
    var $strictToPage      = false;
    var $globalForAllSites = false; // when multiunique site is enabled, make it true for content like social media icons

    var $showHeading       = true;
    var $heading           = '';
    var $headingTag        = 'h2';
    var $recordTitleHeadTag= 'h3';
    var $showTitleBelowPic = false;
    var $showRecordTitle   = true;

    var $showShortDesc     = true;
    var $showDesc          = true;
    var $showDate          = false;
    var $dateFormat        = ''; //'Y年m月d日' for chinese dates

    var $showPic           = true;
    var $mediaExp          = array();
    var $showPicInDesc     = true;
    var $showPicAsBg       = false; // if you need to show the picture as a background for the div or li
    var $includeMediaRec   = false;
    var $additionalMediaRecType = '';

    var $sectionId         = '';
    var $categoryId        = '';
    var $contentId         = '';

    var $sectionType       = '';
    var $categoryType      = '';

    var $specialFilter     = ''; // eg: Latest, Favourite, By Section Type, By Category Type etc..
    var $displayLimit      = 5;
    var $orderBy           = 'content_date DESC';
    var $addUrlForTitle    = false;  // for linking to internal news record
    var $addSearchCond     = ''; //additional search condition like " AND editors_pick = 1"

    var $showReadMore      = false; // if you need to show a read more in each record
    var $readMoreLbl       = 'cp.lbl.readMore';
    var $showGroupReadMore = false; // if you need to show a read more at the end of the widget
    var $groupReadMoreUrl  = '';
    var $groupReadMoreLbl  = 'cp.lbl.readMore';

    var $useDivContainer   = false;
    var $hasPrintBtn       = false;
    var $blockQuote        = false;

    var $truncateDesc        = false;
    var $truncateDescLength  = 200;
    var $truncateDescEnding  = '...';

    var $truncateShortDesc   = false;
    var $truncateShortDescLength  = 100;
    var $truncateShortDescEnding  = '...';

    var $scrollContent     = false;
    var $scrollHandle      = 'simplyScroll1';
    var $scrollSpeed       = 1;
    var $scrollFrameRate   = 20;
    var $scrollDirection   = 'vertical';
    var $scrollAutoMode    = 'loop'; //loop, off, bounce
    var $scrollClass       = 'simply-scroll-vert'; // simply-scroll-vert, simply-scroll

    var $template = "
    <[[rowTag]]>
        <div class='inner' [[picAsBgStyle]]>
            [[pic]]
            [[titleAbovePic]]
            [[date]]
            [[shortDesc]]
            [[desc]]
            [[readMore]]
        </div>
    </[[rowTag]]>        
    ";
    
    //callback function to manipulate $dataArray after it has been generated
    //array[0] = object, array[1] = methodName
    //the callback function should define a parameter $dataArray by reference as below:
    //ex: function myDataArrayPostCallbackFn(&$dataArray)
    //this is required since you would want to amend the $dataArray in your callback
    //which is the whole point of it
    var $dataArrayPostCallback = null;
    var $dataArrayPostCallbackParamArr = array();
    var $extraSqlCondn = '';
    var $includeUrlFld = true; // default url $arrTemp['url'] 
}