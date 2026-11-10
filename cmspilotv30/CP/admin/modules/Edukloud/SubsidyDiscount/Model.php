<?
class CP_Admin_Modules_Edukloud_SubsidyDiscount_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT sd.*
        FROM subsidy_discount sd
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'sd';

        $category_type = $fn->getReqParam('category_type');
        
        $subsidy_discount_id = $fn->getReqParam('subsidy_discount_id');

        if ($subsidy_discount_id != "") {
            $searchVar->sqlSearchVar[] = "sd.subsidy_discount_id = '{$subsidy_discount_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "sd.subsidy_discount_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'sd.subsidy_discount_id');

            if ($category_type != "") {
                $searchVar->sqlSearchVar[] = "sd.category_type = '{$category_type}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
																sd.title            LIKE '%{$tv['keyword']}%' OR
																sd.value            LIKE '%{$tv['keyword']}%' 
                )";
            }
        }        

        if ($tv['lnkRoom'] == 'edukloud_courseSubsidyLink'){
            $course_id = $fn->getReqParam('linkMasterTableID');
            $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
            
            if (is_array($courseRec)){
                $program_group_id = $courseRec['program_group_id'];
                $searchVar->sqlSearchVar[] = "sd.subsidy_discount_id IN (
                    SELECT subsidy_discount_id
                    FROM program_group_subsidy_discount
                    WHERE program_group_id = '{$program_group_id}'
                )";
            }
        }

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('category_type', 'Please enter the Category Type');
        $validate->validateData('mode_of_calculation', 'Please enter the Mode of Calculation');
        $validate->validateData('value', 'Please enter the Value');
        $validate->validateData('valid_from_date', 'Please Valid From Date');
        $validate->validateData('valid_to_date', 'Valid To Date');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'category_type');
        $fa = $fn->addToFieldsArray($fa, 'value');
        $fa = $fn->addToFieldsArray($fa, 'mode_of_calculation');
        $fa = $fn->addToFieldsArray($fa, 'valid_from_date');
        $fa = $fn->addToFieldsArray($fa, 'valid_to_date');
        
        return $fa;
    }

    /**
     *
     */
    function getSubsidyDiscountSQL() {
        return $SQL = "
        SELECT subsidy_discount_id
              ,title
        FROM subsidy_discount
        ";
    }
}
