<?
class CP_Www_Widgets_Core_SubNav_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $class      = 'vlist';
    var $ulClass    = '';
    var $surroundUl = true;
    var $title      = '';
    var $showSubCat = true;
    var $section_id = '';
    var $category_id = ''; // used when you need just one category record for different purpose ex: to get the url
    var $category_type = '';
    var $noSectionFilter = false; //by default it has section_id filter
    var $useUiTabsStyle = false;
    var $displayShowAllMenu = false;
    var $showAllMenuText = '';
    var $showAllMenuUrl = '';
    var $includeCatID = true;
    var $autoSelectFirstSubCategory = null;
    var $includeDescription = false;
}