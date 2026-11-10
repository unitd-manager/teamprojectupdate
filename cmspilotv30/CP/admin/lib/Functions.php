<?
class CP_Admin_Lib_Functions extends CP_Common_Lib_Functions
{
    //==================================================================//
    function getFlagImage($value) {
        $cpCfg = Zend_Registry::get('cpCfg');
        if ($value == 1){
            return "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/star.gif'>";
        } else {
            return '';
        }
    }

    //==================================================================//
    function getRowClass($value, $id = '', $listRowClass='') {
        $currentId = $this->getReqParam('currentId');

        if ($currentId != '' &&  $currentId == $id) {
            return "current";

        } else if ($listRowClass != '') {
            return $listRowClass;

        } else if ($value == 0) {
            return "odd";

        } else {
            return "even";
        }
    }

    //==================================================================//
    function getListTitle($value) {

        if ($value == '')
            return "---";
        else
            return $value;
    }

    //==================================================================//
    function getYesNo($value) {

        if ($value == 1)
            return "Yes";
        else
            return "No";
    }


    /**
     *
     */
    function getQueryStringForJasper() {
        $queryStringNoRoomInfo = $_SERVER['QUERY_STRING'];
        $queryStringNoRoomInfo = $_SERVER['QUERY_STRING'];
        $queryStringNoRoomInfo = preg_replace('/_topRm=[a-zA-Z0-9\. _,]+&?/', '&', $queryStringNoRoomInfo);
        $queryStringNoRoomInfo = preg_replace('/&module=[a-zA-Z0-9\. _,]+&?/', '&', $queryStringNoRoomInfo);
        $queryStringNoRoomInfo = preg_replace('/&_action=[a-zA-Z0-9\. _,]+&?/', '&', $queryStringNoRoomInfo);
        $queryStringNoRoomInfo = preg_replace('/&_spAction=[a-zA-Z0-9\. _,]+&?/', '&', $queryStringNoRoomInfo);

        return $queryStringNoRoomInfo;
    }

    /**
     *
     * @return string
     */
    function getTopRoomName($module){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $topRoomsArray = $cpCfg['cp.topRooms'];

        $topRmName = '';
        foreach ($topRoomsArray as $topRmName => $topRmArr) {
            foreach ($topRoomsArray[$topRmName]['modules'] as $moduleName) {
                if ($moduleName == $module) {
                    return $topRmName;
                }
            }
        }

        return '';
    }


    /**
     *
     * @return string
     */
    function setSpecialSearch(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        //------------------------------------------------------------------------//
        if ($tv['special_search'] == 'Flagged') {
            $searchVar->sqlSearchVar[] = 'c.flag = 1';
        }

        if ($tv['special_search'] == 'Not-Flagged') {
            $searchVar->sqlSearchVar[] = '(c.flag != 1 OR c.flag IS null)';
        }
    }

    /**
     *
     */
    function getStaffCountryID($staff_id = '') {
        $cpCfg = Zend_Registry::get('cpCfg');

        $staff_id = ($staff_id != '') ? $staff_id : $this->getSessionParam('staff_id');

        if ($staff_id == ''){
            return;
        }

        $rec = $this->getRecordRowByID($cpCfg['cp.modAccessStaffTable'], $cpCfg['cp.modAccessStaffIdLabel'], $staff_id);
        $countryId = isset($rec['country_id']) ? $rec['country_id'] : '';

        return $countryId;
    }

    /**
     *
     */
    function isSuperAdmin($staff_id = '') {
        $cpCfg = Zend_Registry::get('cpCfg');

        $staff_id = ($staff_id != '') ? $staff_id : $this->getSessionParam('staff_id');

        if ($staff_id == ''){
            return;
        }

        $rec = $this->getRecordRowByID($cpCfg['cp.modAccessStaffTable'], $cpCfg['cp.modAccessStaffIdLabel'], $staff_id);
        $user_group_id = $rec['user_group_id'];

        if ($user_group_id == $cpCfg['cp.superAdminUGId']){
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function isDeveloper($staff_id = '') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        $staff_id = ($staff_id != '') ? $staff_id : $this->getSessionParam('staff_id');

        if ($staff_id == ''){
            return;
        }

        $SQL = "
        SELECT *
        FROM {$cpCfg['cp.modAccessStaffTable']}
        WHERE {$cpCfg['cp.modAccessStaffIdLabel']} = '{$staff_id}'
        ";
        $result = $db->sql_query($SQL);
        $rec    = $db->sql_fetchrow($result);

        $developer = $rec['developer'];

        if ($developer == 1){
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getISODate($dateObj = null) {
        $dateStr = '';
        if ($dateObj) {
            $dateStr = date('Y-m-d', $dateObj);
        } else {
            $dateStr = date('Y-m-d');
        }

        return $dateStr;
    }

    /**
     *
     */
    function getSiteDropDown($mode, $row = '', $alwaysShow = 0) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');

        $module = $tv['module'];

        if ($cpCfg['cp.hasMultiUniqueSites'] == 1 && !$this->isDeveloper()){
            return;
        }

        if (in_array($module, $cpCfg['w.common_multiUniqueSite.ignoreModules'])) {
            return;
        }

        if ($cpCfg['cp.hasMultiUniqueSites'] == 1 || $alwaysShow == 1){
            $sqlSite = $this->getDDSql('common_site');

            if ($mode == 'new'){
                $site = $formObj->getDDRowBySQL('Site', 'site_id', $sqlSite);
            } else if ($mode == 'search'){
                $site_id = $this->getReqParam('site_id');
                $site = "
                <td>
                    <select name='site_id'>
                        <option value=''>Site</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlSite, $site_id)}
                    </select>
                </td>
                ";
            } else {
                $expSite = array('detailValue' => $row['site_title']);
                $site = $formObj->getDDRowBySQL('Site', 'site_id', $sqlSite, $row['site_id'], $expSite);
            }

            return $site;
        }
    }

    /**
     *
     */
    function getValidateSiteFld($validateObj) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        if (   $cpCfg['cp.hasMultiUniqueSites']
            && !in_array($tv['module'], $cpCfg['w.common_multiUniqueSite.ignoreModules'])
            && $this->isDeveloper()){
            $validateObj->validateData('site_id' , 'Please choose the site');
        }
    }

    /**
     *
     */
    function getSiteFldSqlJoin($mainTblPrefix, $siteTblPrefix = 'si') {
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['cp.hasMultiUniqueSites'] && $this->isDeveloper()){
            return "LEFT JOIN (site si) ON ({$mainTblPrefix}.site_id = si.site_id)";
        }
    }

    /**
     *
     */
    function getSiteTitleFld($tblPrefix = 'si') {
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['cp.hasMultiUniqueSites'] && $this->isDeveloper()){
            return ",si.title AS site_title";
        }
    }

    function getSiteLabelForList($lbl = 'Site') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $listObj = Zend_Registry::get('listObj');

        if ($cpCfg['cp.hasMultiUniqueSites'] && $this->isDeveloper()){
            return "{$listObj->getListHeaderCell($lbl, 'site_title')}";
        }
    }

    function getSiteFldForList($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $listObj = Zend_Registry::get('listObj');

        if ($cpCfg['cp.hasMultiUniqueSites'] && $this->isDeveloper()){
            return "{$listObj->getListDataCell($row['site_title'])}";
        }
    }

    function getRowClass2($counter) {
        $class = '';
        if ($counter%2 == 0) {
            $class = 'even';
        } else {
            $class = 'odd';
        }
        return $class;
    }

}
