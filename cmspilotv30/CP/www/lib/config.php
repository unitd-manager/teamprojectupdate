<?
$cpCfg = array();

$cpCfg['cp.multiLang']          = 1;
$cpCfg['cp.siteLoginProtected'] = false;
$cpCfg['cp.useSEOUrl']          = 1;
$cpCfg['cp.showAnchorInLinkPortal'] = false;
$cpCfg['cp.assetVersion'] = '100';

/****************************************************/
$cpCfg['cp.availableLanguages'] = array(
    'eng' => 'English'
);

$cpCfg['cp.detectBrowserDefaultLang']  = false;
//http://msdn.microsoft.com/en-us/library/ms533052%28v=vs.85%29.aspx
$cpCfg['cp.browserLangCodeToCPLangCode'] = array(
	 'en' => 'eng'
        ,'zh' => 'chi'
        ,'zh-hk' => 'chi'
        ,'zh-cn' => 'chi'
        ,'zh-tw' => 'chi'
        ,'zh-sg' => 'chi'
);

define('CP_REQUEST_URI', $_SERVER['REQUEST_URI']);
$cpCfg['cp.prefixCountryCodeInUrl']     = false;

$cpCfg['cp.hasMemberOnlySections']      = 0;
$cpCfg['cp.hasPublicOnlySections']      = 0;
$cpCfg['cp.hasMemberOnlyCategories']    = 0;
$cpCfg['cp.hasNonMemberOnlyCategories'] = 0;
$cpCfg['cp.hasMemberOnlySubCategories']    = 0;
$cpCfg['cp.hasNonMemberOnlySubCategories'] = 0;
$cpCfg['cp.hasProtectedCategories']     = 0;
$cpCfg['cp.hasSectionsByUserType']      = 0;
$cpCfg['cp.excludeRestrictedNavItems']  = 0;
$cpCfg['cp.hasCategoriesByUserType']    = 0;
$cpCfg['cp.memberOnlySite']             = 0;

$cpCfg['cp.hasPasswordSalt']            = false;
$cpCfg['cp.useRememberMeTokenForCookieLogin']  = false;

$cpCfg['cp.hasLoginHistory']            = false; // if true, a record will be created in login history
$cpCfg['cp.maxConcurrentLoginsAllowed'] = 10; // to restrict the same login been used in multiple computers

$cpCfg['cp.autoSelectFirstCategory']    = 1;
$cpCfg['cp.autoSelectFirstSubCategory'] = true;
$cpCfg['cp.showSubCatBelowCat']         = 1;
$cpCfg['cp.hasAliasSectionsAcrossMUSites'] = false; // if categories of one section is completely used for another in Multi Unique Sites

// if there is a param called cpi in the query string, append that to the actual category url
// this is useful when you need to focus the data in all the categories based on the id passed
// ex: public profile page with various categories such as photos, reviews etc..
$cpCfg['cp.appendCpiWhenAvailable'] = true;

$cpCfg['cp.allowCKFinderFileUpload']    = isset($cpCfg['cp.allowCKFinderFileUpload']) ? $cpCfg['cp.allowCKFinderFileUpload'] : false;
$cpCfg['cp.defaultPageCssClass']        = 'hidecol2';

$cpCfg['cp.defaultShoppingCartModule']  = 'ecommerce_product';

/******************* Mobile *******************/
$cpCfg['cp.hasWebOnlySections']         = false;
$cpCfg['cp.hasMobileOnlySections']      = false;
$cpCfg['cp.hasWebOnlyCategories']       = false;
$cpCfg['cp.hasMobileOnlyCategories']    = false;
$cpCfg['cp.hasWebOnlySubCategories']    = false;
$cpCfg['cp.hasMobileOnlySubCategories'] = false;
$cpCfg['cp.hasWebOnlyContent']          = false;
$cpCfg['cp.hasMobileOnlyContent']       = false;
/****************************************************/

$cpCfg['cp.moduleNamesByTypeArr'] = array(
     'Home'                => 'webBasic_home'
    ,'Content'             => 'webBasic_content'
    ,'Site Search'         => 'webBasic_content'
    ,'Site Map'            => 'webBasic_content'
    ,'Enquiry Form'        => 'webBasic_contactUs'
    ,'Contact Us'          => 'webBasic_contactUs'
    ,'Newsletter Signup'   => 'membership_contact'
    ,'Unsubscribe'         => 'membership_contact'
    ,'News'                => 'webBasic_news'
    ,'News Fat'            => 'webBasic_news'
    ,'News List in Detail' => 'webBasic_news'
    ,'Career'              => 'webBasic_career'
    ,'Event'               => 'event_event'
    ,'Blog'                => 'web2_blog'

    ,'Document'            => 'dms_document'
    ,'Gallery'             => 'webBasic_gallery'
    ,'Picture Grid'        => 'webBasic_gallery'
    ,'Product'             => 'ecommerce_product'
    ,'Project'             => 'gallery_project'

    ,'Login'               => 'membership_contact'
    ,'Register'            => 'membership_contact'
    ,'My Profile'          => 'membership_contact'
    ,'My Orders'           => 'membership_contact'

    ,'Basket'              => 'ecommerce_basket'
    ,'Order Form'          => 'ecommerce_basket'
    ,'Stockist'            => 'ecommerce_stockist'
);

$cpCfg['cp.assetsByPageTypeArr'] = array();

$cpCfg['cp.redirectToHTTPSByTypeArr'] = array();
$cpCfg['cp.redirectBackToHTTPFromHTTPS'] = false;

$cpCfg['cp.orderInvoiceNoPrefix'] = 'INV_';

/** TEMPLATE - turn this off at theme level if you do not want to show the nav panel at the top **/
$cpCfg['cp.showMainNavPanelAtTop']     = true;
$cpCfg['cp.showNavAsMenu']             = false;
$cpCfg['cp.showNavAsMegaMenu']         = false;
$cpCfg['cp.showSubNavAsMenu']          = false;
$cpCfg['cp.showTopMostSections']       = false;
$cpCfg['cp.showBannerBelowHeader']     = false;
$cpCfg['cp.showSocialIconsInHeader']   = true;
$cpCfg['cp.showBannerInCol3Top']       = false;
$cpCfg['cp.placeFooterOutsidePageTag'] = false; //place footer outside page div
$cpCfg['cp.placeNavInsideHeaderTag']   = false;
$cpCfg['cp.showLogoText']              = false;
$cpCfg['cp.showExtendedPanel']         = true;
$cpCfg['cp.showSlideShowBelowHeader']  = false;
$cpCfg['cp.showLoginInfoAtTheTop']     = false;
$cpCfg['cp.showRegisterInfoAtTheTop']  = false;
$cpCfg['cp.showViewCartAtTheTop']      = false;
$cpCfg['cp.showSiteSearchAtTheTop']    = true;
$cpCfg['cp.showLangToggleAtTheTop']    = false;
$cpCfg['cp.hideCurrentLang']           = false;
$cpCfg['cp.fullWidthTemplte']          = false;
$cpCfg['cp.showFBShareTagsInDetail']   = false;

$cpCfg['w.core_mainNav.hasSlidingDoorBtn'] = false;
$cpCfg['cp.roomsWithNoAutoSelctCategory'] = array();

$cpCfg['w.core.subNav.hasGrouping'] = false; // if you want to sub group the category for display purpose
$cpCfg['w.core.subCat.hasGrouping'] = false; // -- do --

$cpCfg['p.member.login.updateLoginCount'] = false;
$cpCfg['p.member.login.updateLastLoggedIn'] = false;
$cpCfg['p.paymentMethods.paypal.paymentItemName'] = '';

//version for img/css/javascripts and any other asset files
$cpCfg['cp.assetVersion'] = '';
$cpCfg['cp.superFishMenuVersion'] = '1.4.8';

return $cpCfg;