<?
class CP_Www_Modules_Edukite_DailyActivity_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT da.*
              ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
        FROM daily_activity da
        LEFT JOIN teacher t ON (t.teacher_id = da.teacher_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'da';

        $teacher_id = $fn->getReqParam ('teacher_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "da.daily_activity_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'da.daily_activity_id');

            if ($teacher_id != '') {
                $searchVar->sqlSearchVar[] = "da.teacher_id = '{$teacher_id}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   da.title LIKE '%{$tv['keyword']}%'
                )";
            }
            $searchVar->sortOrder = "da.title ASC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the Title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the Title');

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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'jellyfish_group_time');
        $fa = $fn->addToFieldsArray($fa, 'sea_turtles_group_time');
        $fa = $fn->addToFieldsArray($fa, 'whales_group_time');
        $fa = $fn->addToFieldsArray($fa, 'music');
        $fa = $fn->addToFieldsArray($fa, 'school_readiness_program');
        $fa = $fn->addToFieldsArray($fa, 'todays_meals');
        $fa = $fn->addToFieldsArray($fa, 'morning_tea');
        $fa = $fn->addToFieldsArray($fa, 'fruit_break');
        $fa = $fn->addToFieldsArray($fa, 'lunch');
        $fa = $fn->addToFieldsArray($fa, 'dessert');

        return $fa;
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
        $fa['teacher_id'] = $_SESSION['cpContactId'];
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }
}
