<?
$cpCfg = array();

$cpCfg['m.web2.tags.hasGroup'] = 1;
$cpCfg['m.web2.tags.hasChildren'] = 1;
$cpCfg['m.web2.tags.hasCSSStyle'] = 1;
$cpCfg['m.web2.tags.hasLanguage'] = 1;

//------------ FEED --------------//
$cpCfg['m.web2.feed.showMetaData']     = 1;
$cpCfg['m.web2.feed.recordTypeArr']    = array (
     'RSS'
    ,'Atom'
);

$cpCfg['m.web2.feed.feedSource'] = array();
//$cpCfg['m.web2.feed.feedSource'][] = array(
//     'type' => 'RSS'
//    ,'url'  => 'http://mrinetwork.com/feeds/media-releases/'
//);

return $cpCfg;