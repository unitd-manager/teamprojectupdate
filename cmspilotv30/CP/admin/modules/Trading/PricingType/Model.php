<?
class CP_Admin_Modules_Trading_PricingType_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT pt.*
              ,CASE
               WHEN pt.show_in_catalog = 1
               THEN 'Yes'
               ELSE 'No'
               END AS show_in_catalog_text
        FROM pricing_type pt
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "pt.pricing_type_id = {$tv['record_id']}";

        } else {
            if ($tv['keyword'] != '') {
                $searchVar->sqlSearchVar[] = "(
                       pt.pricing_type LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $searchVar->sortOrder = "pt.sort_order";

    }

    /**
     *
     */
    function getNewValidate() {
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $pricing_type = $fn->getReqParam('pricing_type');

        $SQL = "
        SELECT COUNT(*) AS count
        FROM pricing_type pt
        WHERE pt.pricing_type   = '{$pricing_type}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $validate->errorArray['pricing_type']['name'] = 'pricing_type';
            $validate->errorArray['pricing_type']['msg']   = 'Duplicate pricing type';
        }

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
        $fn->returnAfterNewSave($id, 'detail');
    }

    /**
     *
     */
    function getEditValidate() {
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $pricing_type_id = $fn->getReqParam('pricing_type_id');
        $rowRegion = $fn->getRecordRowByID('pricing_type', 'pricing_type_id', $pricing_type_id);

        $pricing_type = $fn->getReqParam('pricing_type');

        if ($rowRegion['pricing_type_id'] != $pricing_type_id) {
            $SQL = "
            SELECT COUNT(*) AS count
            FROM pricing_type pt
            WHERE pt.pricing_type   = '{$pricing_type}'
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
            if ($row['count'] > 0) {
                $validate->errorArray['pricing_type']['name'] = 'pricing_type';
                $validate->errorArray['pricing_type']['msg']  = 'Duplicate pricing type';
            }
        }

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
        $fn->returnAfterNewSave($id, 'detail');
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'pricing_type');
        $fa = $fn->addToFieldsArray($fa, 'record_type');
        $fa = $fn->addToFieldsArray($fa, 'discount_percent');
        $fa = $fn->addToFieldsArray($fa, 'show_in_catalog');
        $fa = $fn->addToFieldsArray($fa, 'hide_in_company');

        return $fa;
    }

    function getRecordByType($record_type = 'no_tax') {
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $pricing_type = $fn->getReqParam('pricing_type');

        $SQL = "
        SELECT *
        FROM pricing_type pt
        WHERE pt.record_type = '{$record_type}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        return $row;
    }
}
