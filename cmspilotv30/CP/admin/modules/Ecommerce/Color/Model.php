<?
class CP_Admin_Modules_Ecommerce_Color_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL   = "
        SELECT c.*
        FROM color c
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
        $searchVar->mainTableAlias = 'c';

        $color_id = $fn->getReqParam('color_id');

        if ($color_id != '') {
            $searchVar->sqlSearchVar[] = "c.color_id = '{$color_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.color_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.color_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "
                (   c.title LIKE '%{$tv['keyword']}%'
                 OR c.code LIKE '%{$tv['keyword']}%'
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

        $validate->validateData('title', 'Please enter the color');

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
        $validate->validateData('title', 'Please enter the color');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'title');

        return $fa;
    }
}
