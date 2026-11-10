<?
class CP_Admin_Modules_Pms_BatchLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getBatchValueForDropDown() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn     = Zend_Registry::get('fn');
        $ln     = Zend_Registry::get('ln');
        $modulesArr = Zend_Registry::get('modulesArr');
        
        $site_id  = $fn->getSessionParam('cp_site_id');
        $module   = $fn->getReqParam('room');
        $srcFld   = $fn->getReqParam('srcFld', '', true);
        $srcValue = $fn->getReqParam('srcValue', '', true);
        $category_type   = $fn->getReqParam('category_type');
        //we are setting the course in session here to use in all our further functions, this is unset in orderlink view getCourseTraineeSearch
        $_SESSION['selectedCourse']  = $srcValue;        

        $json = array();
        
        $sqlAppend = '';
        if ($cpCfg['m.pms.batch.multiSiteBranch'] == 1) {
            $sqlAppend = " AND b.site_id = {$site_id}";
        }

        if ($srcValue == ''){
            $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
            return json_encode($json);
        }

        $today = date("Y-m-d");
        $SQL  = "
        SELECT b.batch_id
              ,b.title
        FROM batch b
        WHERE b.course_id = {$_SESSION['selectedCourse']}
          AND b.status='Open'
          {$sqlAppend}
        ";
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row[0], "caption" => $row[1]);
        }

        return json_encode($json);
    }
    /**
     *
     */
    function getBatchValueForDropDownPvt() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn     = Zend_Registry::get('fn');
        $ln     = Zend_Registry::get('ln');
        $modulesArr = Zend_Registry::get('modulesArr');
        
        $module   = $fn->getReqParam('room');
        $srcFld   = $fn->getReqParam('srcFld', '', true);
        $srcValue = $fn->getReqParam('srcValue', '', true);

        $json = array();

        if ($srcValue == ''){
            $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
            return json_encode($json);
        }

        $today = date("Y-m-d");
        $SQL  = "
        SELECT b.batch_id
              ,b.title
        FROM batch b
        WHERE b.course_id = {$srcValue}
          AND b.status='Open'
        ";
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row[0], "caption" => $row[1]);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getBatchValueForDropDownReport() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        
        $siteFld     = $fn->getReqParam('siteFld', '', true);
        $siteValue   = $fn->getReqParam('siteValue', '', true);
        $courseFld   = $fn->getReqParam('courseFld', '', true);
        $courseValue = $fn->getReqParam('courseValue', '', true);
        
        if ($siteValue == '') {
            $siteValue = $fn->getSessionParam('cp_site_id');
        }
        
        $json = array();
        
        if ($courseValue == '' || $siteValue == ''){
            $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
            return json_encode($json);
        }

        $SQL  = "
        SELECT b.batch_id
              ,b.title
        FROM batch b
        WHERE b.course_id = {$courseValue}
          AND b.site_id = {$siteValue}
          AND b.status = 'Open'
        ";
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row[0], "caption" => $row[1]);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getBatchValueForDropDownFromBatchTransfer() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        
        $selected_branch   = $fn->getReqParam('selected_branch');
        $course_contact_id = $fn->getReqParam('course_contact_id');
        
        $courseContactRec = $fn->getRecordRowById('course_contact', 'course_contact_id', $course_contact_id);
        
        $json = array();
        
        if ($selected_branch == '') {
            $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
            return json_encode($json);
        }

        $SQL  = "
        SELECT b.batch_id
              ,b.title
        FROM batch b
        WHERE b.course_id = {$courseContactRec['course_id']}
          AND b.status = 'Open'
          AND b.site_id = {$selected_branch}
        ";
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row[0], "caption" => $row[1]);
        }

        return json_encode($json);
    }
}