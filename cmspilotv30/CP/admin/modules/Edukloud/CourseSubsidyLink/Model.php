<?
class CP_Admin_Modules_Edukloud_CourseSubsidyLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getSubsidyValueForDropDown() {
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
        $category_type   = $fn->getReqParam('category_type');
        //we are setting the course in session here to use in all our further functions, this is unset in orderlink view->getCourseTraineeSearch
        $_SESSION['selectedCourse']  = $srcValue;        

        $json = array();

        if ($srcValue == ''){
            $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
            return json_encode($json);
        }

        $today = date("Y-m-d");
        $SQL  = "
        SELECT s.course_subsidy_history_id
              ,sd.title 
        FROM course_subsidy_history s
        LEFT JOIN (subsidy_discount sd) ON (s.subsidy_discount_id = sd.subsidy_discount_id)
        WHERE {$srcFld} = {$srcValue}
        AND sd.category_type = 'Subsidy'
        AND sd.valid_from_date <= '{$today}'
        AND sd.valid_to_date >= '{$today}'
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
    function getDiscountValueForDropDown() {
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
        SELECT s.course_subsidy_history_id
              ,sd.title 
        FROM course_subsidy_history s
        LEFT JOIN (subsidy_discount sd) ON (s.subsidy_discount_id = sd.subsidy_discount_id)
        WHERE {$srcFld} = {$srcValue}
        AND sd.category_type = 'Discount'
        AND sd.valid_from_date <= '{$today}'
        AND sd.valid_to_date >= '{$today}'
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
    function getSQL($linkRecType) {
        $modObj = getCPModuleObj('edukloud_subsidyDiscount');
        return $modObj->model->getSQL($linkRecType);
    }


    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $modObj = getCPModuleObj('edukloud_subsidyDiscount');
        $modObj->model->setSearchVar($linkRecType);
    }
    

    /**
     *
     */
    function getSQLForPager() {
    }      
}
