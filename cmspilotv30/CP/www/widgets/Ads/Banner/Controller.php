<?
class CP_Www_Widgets_Ads_Banner_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $global = false; // if true: will display the banner from content (content type = Global Banner) throughout the site
    var $strictToPage = false; // if false: will display the banner only if found for the exact section or category (no fallback)

    var $position   = 'Right';
    var $module     = 'webBasic_section';
    var $record_id  = '';
    var $sort_order = 'bl.sort_order ASC';
    var $addSearchCondArr = array(); //additional search condition like "bl.sort_order > 1"
    var $displayLimit  = 1;
}