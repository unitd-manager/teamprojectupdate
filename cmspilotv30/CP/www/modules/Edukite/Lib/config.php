<?
$cpCfg = array();

$cpCfg['m.edukite.current_academic_year']  = date('Y');
$cpCfg['cp.primarySchool'] = 1;
$cpCfg['cp.uploadTechnology'] = 'html';
//$cpCfg['cp.maxUploadLimit']          = '5MB';
$cpCfg['m.edukite.notice.showGlobalKite'] = 0;
$cpCfg['showAcheivement'] = 0;
$cpCfg['cp.showHomework'] = 0;
$cpCfg['cp.noticeReadSummary'] = 0;
$cpCfg['cp.schoolEnrolledCurrentYear'] = 0;
$cpCfg['cp.showSubjectFilterInKite'] = 1;
$cpCfg['cp.dailyActivity'] = 0;
/************** CONTACT *************/
$cpCfg['m.edukite.statusArr'] = array (
     'Active'
    ,'Archive'
);

return $cpCfg;