<?
class CP_Admin_Modules_EnterpriseIms_BatchLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getBatchValueForDropDown() {
        $db         = Zend_Registry::get('db');
        $tv         = Zend_Registry::get('tv');
        $fn         = Zend_Registry::get('fn');
        $ln         = Zend_Registry::get('ln');
        $cpCfg      = Zend_Registry::get('cpCfg');
        $cpUtil     = Zend_Registry::get('cpUtil');
        $modulesArr = Zend_Registry::get('modulesArr');
        
        $module   = $fn->getReqParam('room');
        $srcFld   = $fn->getReqParam('srcFld', '', true);
        $srcValue = $fn->getReqParam('srcValue', '', true);
        $category_type   = $fn->getReqParam('category_type');
        //we are setting the course in session here to use in all our further functions, this is unset in orderlink view getCourseTraineeSearch
        $_SESSION['selectedCourse']  = $srcValue;        

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
        WHERE b.course_id = {$_SESSION['selectedCourse']}
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
    function getBatchValueForDropDownPvt() {
        $db         = Zend_Registry::get('db');
        $tv         = Zend_Registry::get('tv');
        $fn         = Zend_Registry::get('fn');
        $ln         = Zend_Registry::get('ln');
        $cpCfg      = Zend_Registry::get('cpCfg');
        $cpUtil     = Zend_Registry::get('cpUtil');
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
}