<?
$tv = Zend_Registry::get('tv');
array_push($tv['protSiteSpActionExceptions'], 'smartCardForm');

CP_Common_Lib_Registry::arrayMerge('tv', $tv);
