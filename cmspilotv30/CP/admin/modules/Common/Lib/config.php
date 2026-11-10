<?
$cpCfg = array();

//------------ CONTACT -------------//
$cpCfg['m.common.contact.showEvent']        = 0;
$cpCfg['m.common.contact.showInterest']     = 1;
$cpCfg['m.common.contact.showAttachment']   = 0;
$cpCfg['m.common.contact.showOrders']       = false;
$cpCfg['m.common.contact.hasWebLogin']      = 1;
$cpCfg['m.common.contact.showUsername']     = 0;
$cpCfg['m.common.contact.showStaffDetail']  = 0;
$cpCfg['m.common.contact.hasCompanyTable']  = 0;
$cpCfg['m.common.contact.showContentLink']  = false;
$cpCfg['m.common.contact.hasSalutation']    = false;
$cpCfg['m.common.contact.showInterestInImport'] = false;
$cpCfg['m.common.contact.showLangPrefernce'] = false;
$cpCfg['m.common.contact.flagInvalidEmails'] = false;
$cpCfg['m.common.contact.specialSearchArr'] = array(
     "Subscribed"
    ,"Not-Subscribed"
    ,"Flagged"
    ,"Not-Flagged"
);

//------------ SERVICE -------------//
$cpCfg['showCountryInService'] = 0;

//------------ CAREERS -------------//
$cpCfg['m.common.career.showCountry'] = 0;

//------------ BROADCAST -------------//
$cpCfg['m.common.broadcast.showRecordType'] = 0;

//------------ GEO COUNTRY -------------//
$cpCfg['m.common.geoCountry.showPublished'] = false;
$cpCfg['m.common.geoCountry.hasPaypalEmail'] = false;
$cpCfg['m.common.geoCountry.hasShippingCharge'] = false;
$cpCfg['m.common.geoCountry.hasStockist'] = false;
$cpCfg['m.common.geoCountry.displayShowInNavFld'] = false;

//------------ COUNTRY -------------//
$cpCfg['m.common.country.hasIddCode'] = false;
$cpCfg['m.common.country.hasCurrency'] = false;
$cpCfg['m.common.country.hasTopLevelDomain'] = false;

//------------ GEO REGION -------------//
$cpCfg['m.common.geoRegion.hasDescription'] = 0;

//------------ LOGIN HISTORY -------------//
$cpCfg['m.lawNews.loginHistory.specialSearchArr'] = array(
     'Active'
    ,'Not-Active'
);

//------------ SITE -------------//
$cpCfg['m.common.site.hasAdOpsSiteName'] = false;
$cpCfg['m.common.site.showMetaData'] = false;
$cpCfg['m.common.site.hasAdditionalMetaTags'] = false;
$cpCfg['m.common.site.hasAdditonalAnalyticsScript'] = false;
$cpCfg['m.common.site.hasSecondLogo'] = false;
$cpCfg['m.common.site.hasSortOrder'] = false;
$cpCfg['m.common.site.hasCaption'] = false;
$cpCfg['m.common.site.hasSiteKey'] = false;
$cpCfg['m.common.site.hasTagline'] = false;
$cpCfg['m.common.site.hasSiteAlias'] = false;
$cpCfg['m.common.site.hasLanguage'] = false;

//------------ INTEREST -------------//
$cpCfg['m.common.interest.showDesc'] = 0;
$cpCfg['m.common.interest.showMetaData'] = 0;

return $cpCfg;