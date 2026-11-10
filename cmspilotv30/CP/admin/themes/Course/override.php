<?
$tv = Zend_Registry::get('tv');
$cpCfg = Zend_Registry::get('cpCfg');

$themePath = CP_MASTER_PATH_ALIAS . 'themes/' . $cpCfg['cp.theme'] . '/';

$cssFilesArr = array();
$cssFilesArr[] = 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,200,200italic,300,300italic,400italic,600,600italic,700italic,700,900,900italic';
$cssFilesArr[] = 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css';
$cssFilesArr[] = 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css';
$cssFilesArr[] = $themePath.'css/material-dashboard.min.css';
$cssFilesArr[] = $themePath.'css/circle.css';

$jsFilesArr = array();
$jssKeys = array('fontAwesome-4.3.0', 'dropzone', 'ckEditor');

array_push($tv['protSiteSpActionExceptions'], 'takeVirtualTour');
array_push($tv['protSiteSpActionExceptions'], 'sendReminderMail');
array_push($tv['protSiteSpActionExceptions'], 'sendFollowUpSMS');
array_push($tv['protSiteSpActionExceptions'], 'sendTaskUpdatesToPM');
array_push($tv['protSiteSpActionExceptions'], 'sendCallbackMailAndSMS');
CP_Common_Lib_Registry::arrayMerge('tv', $tv);
CP_Common_Lib_Registry::arrayMerge('cssFilesArr', $cssFilesArr);
CP_Common_Lib_Registry::arrayMerge('jsFilesArr', $jsFilesArr);
CP_Common_Lib_Registry::arrayMerge('jssKeys', $jssKeys);


