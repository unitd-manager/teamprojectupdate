<?
class CP_Admin_Modules_Common_Country_Functions extends CP_Common_Modules_Common_Country_Functions
{
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('common_country');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'actBtnsList' => array('new', 'export')
        ));
    }

    /**
     *
     */
    function getCountryDropDown($mode, $row = '', $alwaysShow = 0) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        
        if ($cpCfg['cp.showOnlyDataOfStaffsCountry'] == 1 && !$fn->isSuperAdmin()){
            return;
        }

        if ($cpCfg['cp.showOnlyDataOfStaffsCountry'] == 1 || $alwaysShow == 1){
            $sqlCountry = $this->model->getCountrySQL();

            if ($mode == 'new'){
                $country = $formObj->getDDRowBySQL('Country', 'country_id', $sqlCountry);
            } else if ($mode == 'search'){
                $country_id = $fn->getReqParam('country_id');
                $country = "
                <td>
                    <select name='country_id'>
                        <option value=''>Country</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlCountry, $country_id)}
                    </select>
                </td>
                ";
            } else {
                $expCountry = array('detailValue' => $row['title']);
                $country = $formObj->getDDRowBySQL('Country', 'country_id', $sqlCountry, $row['country_id'], $expCountry);
            }
            
            return $country;
        }
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $linkObj = $inst->getLinksArrayObj('common_country', 'common_languageLink', array(
             'historyTableName'       => 'language'
            ,'showAnchorInLinkPortal' => 0
            ,'hasPortalEdit'          => 1
            ,'hasPortalDelete'        => 1
            ,'linkingType'            => 'portal'
            ,'fieldlabel'             => array(
                 'Language'
                ,'Lang Prefix'
                ,'Published'
            )
        ));
        $inst->registerLinksArray($linkObj);
    }

    /**
     *
     */
    function setCountrySearch($searchVar, $prefix) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        
        if ($cpCfg['cp.showOnlyDataOfStaffsCountry'] == 1 && !$fn->isSuperAdmin()){
            $searchVar->sqlSearchVar[] = "{$prefix}.country_id = {$fn->getStaffCountryID()}";
        }
        return $searchVar;
    }
    

    /**
     *
     */
    function getCountryValueCellInList($row = '', $alwaysShow = 0) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        
        if ($cpCfg['cp.showOnlyDataOfStaffsCountry'] == 1 && !$fn->isSuperAdmin()){
            return;
        }
        
        if ($cpCfg['cp.showOnlyDataOfStaffsCountry'] == 1 || $alwaysShow == 1){
            $text = $listObj->getListDataCell($row['title']);
            return $text;
        }
    }

    /**
     *
     */
    function getCountryLabelCellInList($alwaysShow = 0) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        
        if ($cpCfg['cp.showOnlyDataOfStaffsCountry'] == 1 && !$fn->isSuperAdmin()){
            return;
        }
        
        if ($cpCfg['cp.showOnlyDataOfStaffsCountry'] == 1 || $alwaysShow == 1){
            $text = $listObj->getListHeaderCell('Country', 'co.title');
            return $text;
        }
    }     
}