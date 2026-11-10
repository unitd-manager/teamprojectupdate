<?
class CP_Admin_Widgets_Common_MultiYear_Model extends CP_Common_Lib_WidgetModelAbstract
{
    function getYearArray() {
        $year = date('Y');
        $cpCfg = Zend_Registry::get('cpCfg');

        $arr = array();
        if (count($cpCfg['w.common_multiYear.yearArray']) > 0) {
            $arr = $cpCfg['w.common_multiYear.yearArray'];
        } else {
            $start = $year - 10;
            $end   = $year + 10;

            $arr = array();
            for($i = $start; $i <= $end; $i++) {
                $arr[] = $i;
            }
        }
        return $arr;

    }

    function setDefaultYear() {
        $fn = Zend_Registry::get('fn');
        $cp_year = $fn->getSessionParam('cp_year');
        if ($cp_year == '') {
            $year = date('Y');
            $fn->setSessionParam('cp_year', $year);
        }
    }

    function getChangeYear() {
        $fn = Zend_Registry::get('fn');
        $cp_year = $fn->getReqParam('cp_year');
        $fn->setSessionParam('cp_year', $cp_year);
    }

    function getSqlCondn($mainTblPrefix = '') {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($mainTblPrefix != '') {
            $mainTblPrefix = $mainTblPrefix . '.';
        }

        $whereCond = '';
        $cp_year = $fn->getSessionParam('cp_year');
        if ($cpCfg['cp.hasMultiYears']){
            $whereCond = "{$mainTblPrefix}cp_year = {$cp_year}";
        }
        return $whereCond;
    }
}
