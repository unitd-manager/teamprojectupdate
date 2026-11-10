<?
class CP_Admin_Widgets_Common_MultiUniqueSite_View extends CP_Common_Lib_WidgetViewAbstract
{

    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $this->model->setDefaultSite();
        $arr = $this->model->getSitesArray();

        $cp_site_id = $fn->getSessionParam('cp_site_id');

        $text = "
        {$cpCfg['cp.chooseSiteLbl']}
        <select name='cp_site_id'>
            {$cpUtil->getDropDownFromArr($arr, $cp_site_id)}
        </select>
        ";

        return $text;
    }

    function getSiteRow($row = null) {
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $text = '';
        if ($cpCfg['cp.hasMultiUniqueSites']){
            $siteRec = $fn->getRecordRowByID('site', 'site_id', $row['site_id'], array('globalForAllSites' => true));
            $siteTitle = is_array($siteRec) ? $siteRec['title'] : '';
            $text = "
            {$formObj->getTBRow('Site', 'site_id', $siteTitle, array('isEditable' => false))}
            ";
        }

        return $text;
    }


}