<?
class CP_Www_Widgets_Menu_MegaMenu_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $btnPos       = 'Top';
    var $includeSecID = true;

    var $classParent    = 'dc-mega';
    var $classContainer = 'sub-container';    // Allows you to change the class given to the mega sub-menu container
    var $classSubLink   = 'mega-hdr'; // Set class given to mega menu parent menu items
    var $rowItems  = 3; // Number of sub-menus in each row
    var $speed     = 'fast';  // Speed of drop down animation
    var $effect    = 'fade';  // Type of drop down animation - 'slide' or 'fade'
    var $event     = 'hover';  // Use either 'hover' or 'click'
    var $fullWidth = false;  // Set to true to always show sub-menus at 100%

    var $noOfSecToDisplay = 20;
    var $noOfCatToDisplay = 20;
    var $noOfSubCatToDisplay = 20;
    var $hideWidgetOnLoading = true;
}