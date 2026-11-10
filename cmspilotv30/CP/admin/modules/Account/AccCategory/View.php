<?
class CP_Admin_Modules_Account_AccCategory_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $jssKeys = array('jqTreeview-1.4.1');

    /**
     *
     */
    function getList() {
        $rows = '';

        $addMenuUrl = "index.php?module=account_accCategory&_spAction=newMenuItem&showHTML=0";

        $text  = "
        <div id='menu'>
            <a class='button' id='addNewMenu' link='{$addMenuUrl}'>Add New Menu Item</a>
            <div id='menuList'>
                {$this->getListItems()}
            </div>
        </div>
		";

       return $text;
    }

    /**
     *
     */
    function getListItems() {
        $db = Zend_Registry::get('db');

        $rows = "";

        $SQL = "
        SELECT acc_category_id
              ,title
        FROM acc_category
        WHERE parent_id = 0
        ORDER BY code
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $editMenuUrl = "index.php?module=account_accCategory&_spAction=editMenuItem&id={$row['acc_category_id']}&showHTML=0";
            $rows .= "
            <li>
                <span class='folder'>
                    <a href='{$editMenuUrl}' id='{$row['acc_category_id']}'>{$row['title']}</a>
                </span>
                {$this->getChildItems($row['acc_category_id'])}
            </li>
            ";
        }

        $text  = "
        <ul class='tree filetree noDefault'>
            {$rows}
        </ul>
       ";

       return $text;
    }

    /**
     *
     */
    function getChildItems($parent_id) {
        $db = Zend_Registry::get('db');

        $rows = "";

        $SQL = "
        SELECT acc_category_id
              ,title
              ,category_type
        FROM acc_category
        WHERE parent_id = {$parent_id}
        ORDER BY code
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $editMenuUrl = "index.php?module=account_accCategory&_spAction=editMenuItem&id={$row['acc_category_id']}&showHTML=0";
            $title = "{$row['title']} - <i>{$row['category_type']}</i>";
            $category_type = $row['category_type'] ? "- <i>{$row['category_type']}</i>" : '';
            $rows .= "
            <li>
                <a href='{$editMenuUrl}' id='{$row['acc_category_id']}'>{$row['title']}</a>
                {$category_type}
                {$this->getChildItems($row['acc_category_id'])}
            </li>
            ";
        }

        $text = "";

        if ($numRows > 0){
            $text  = "
            <ul class='noDefault'>
                {$rows}
            </ul>
           ";
        }

       return $text;
    }

    /**
     *
     */
    function getNewMenuItem(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $categoryTypeArr = $cpCfg['m.account.accCategory.categoryType'];

        $formAction = "index.php?module=account_accCategory&_spAction=addMenuItem&showHTML=0";
        $fnsModGrp = includeCPClass('ModGroup', 'Account', 'Functions');

        $text = "
        <form id='menuForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                <div class='type-select'>
                    <label for='fld_parent_id'>Parent</label>
                    <select id='fld_parent_id' name='parent_id'>
                        <option selected='selected' value=''>Please Select</option>
                        {$fnsModGrp->getAccCatDropdown()}
                    </select>
                </div>
                {$formObj->getTBRow('Title', 'title')}
                {$formObj->getTBRow('Code', 'code')}
                {$formObj->getDDRowByArr('Category Type', 'category_type', $categoryTypeArr)}
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditMenuItem(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $categoryTypeArr = $cpCfg['m.account.accCategory.categoryType'];

        $formAction = "index.php?module=account_accCategory&_spAction=saveMenuItem&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $id = $fn->getReqParam('id', '', true);
        $row = $fn->getRecordRowByID('acc_category', 'acc_category_id', $id);
        $fnsModGrp = includeCPClass('ModGroup', 'Account', 'Functions');

        $text = "
        <form id='menuForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                <div class='type-select'>
                    <label for='fld_parent_id'>Parent</label>
                    <select id='fld_parent_id' name='parent_id'>
                        <option selected='selected' value=''>Please Select</option>
                        {$fnsModGrp->getAccCatDropdown($row['parent_id'])}
                    </select>
                </div>
                {$formObj->getTBRow('Title', 'title', $row['title'])}
                {$formObj->getTBRow('Code', 'code', $row['code'])}
                {$formObj->getDDRowByArr('Category Type', 'category_type', $categoryTypeArr, $row['category_type'])}
                <input type='hidden' name='acc_category_id' value='{$id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }
}