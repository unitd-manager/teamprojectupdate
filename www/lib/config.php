<?
$cpCfg = array();
//$cpCfg['cp.theme'] = 'Default';
$cpCfg['cp.useMinFiles'] = true;
$cpCfg['cp.hasAdminOnly'] = true;

$modArr = array(
     'webBasic_home'
    ,'webBasic_section'
    ,'webBasic_content'
    ,'webBasic_contactUs'
);
$hiddenModules = array();

$cpCfg['cp.availableModules'] = array_merge($modArr, $hiddenModules);

$cpCfg['cp.availableModGroups'] = array(
    'webBasic'
);

$cpCfg['cp.availableWidgets'] = array(
     'core_mainNav'
    ,'core_subNav'
    ,'core_subCat'
    ,'media_anythingSlider'
    ,'content_record'
);

$cpCfg['cp.availablePlugins'] = array(
     'common_comment'
    ,'common_media'
    ,'common_siteSearch'
);


return $cpCfg;