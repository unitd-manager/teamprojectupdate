<?
class CP_Admin_Modules_ManPower_CandidateCountry_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{
    function setModuleArray($modules){
    	
        $modObj = $modules->getModuleObj('manPower_candidateCountry');
        $modObj['tableName'] = 'candidate_country';
        $modObj['keyField']  = 'candidate_country_id';
        $modules->registerModule($modObj, array(
            'hasMultiLang'  => 1
           ,'title'         => 'Candidate Country'
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
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
    
}