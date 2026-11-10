<?
class CPL_Admin_Modules_Core_UserGroup_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows  = "";
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .="
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['user_group_id'], 'center' )}
            {$listObj->getListRowEnd($row['user_group_id'])}
            ";

            $rowCounter++ ;
        }

        $text ="
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.core.userGroup.lbl.title', 'Title'), 'a.title')}
        {$listObj->getListHeaderCell($ln->gd('m.core.userGroup.lbl.id', 'ID'), 'a.user_group_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $userGroupTypeArr = $cpCfg['m.core.userGroup.userGroupTypeArr'];
        $exp = array('hideFirstOption' => 1);

        $fieldset1 = "
        {$formObj->getTBRow($ln->gd('m.core.userGroup.lbl.title', 'Title'), 'title', $row['title'])}
        {$formObj->getDDRowByArr($ln->gd('m.core.userGroup.lbl.userGroupType', 'User Group Type'), 'user_group_type', $userGroupTypeArr, $row['user_group_type'], $exp)}
        ";

        $actions = '';
        $otherActions = '';
        if ($cpCfg['cp.hasAccessModule']){
            $actions = "
            {$formObj->getFieldSetWrapped('Actions', "
                <div class='modAccSecurity'>
                    {$this->_getAccessTableHTML($row)}
                </div>
                "
            )}
            ";

           if ($cpCfg['cp.hasAccessModuleOtherAction']){
                $otherActions = "
                {$formObj->getFieldSetWrapped('Special Actions', "
                    <div class='modAccSecurity'>
                        {$this->_getOtherActionTableHTML($row)}
                    </div>
                    "
                )}
                ";
            }
        }

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset1)}
        {$actions}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $formObj = Zend_Registry::get('formObj');

        $fielset = "
        {$formObj->getTBRow($ln->gd('m.core.userGroup.lbl.title', 'Title'), 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;

    }

    //==================================================================//
    protected function _getAccessTableHTML($row = array()) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        if (isset($row['user_group_id'])) {
            $SQL = "
            SELECT a.title
                  ,b.list
                  ,b.detail
                  ,b.new
                  ,b.edit
                  ,b.delete
                  ,b.publish
                  ,b.unpublish
                  ,b.print AS print
                  ,b.import AS import
                  ,b.export AS export
                  ,a.list AS list_rm
                  ,a.detail AS detail_rm
                  ,a.new AS new_rm
                  ,a.edit AS edit_rm
                  ,a.delete AS delete_rm
                  ,a.publish AS publish_rm
                  ,a.unpublish AS unpublish_rm
                  ,a.print AS print_rm
                  ,a.import AS import_rm
                  ,a.export AS export_rm
                  ,a.room_id
            FROM mod_acc_room a
            LEFT JOIN mod_acc_room_user_group b
            ON (a.room_id = b.room_id)
            WHERE b.user_group_id = {$row['user_group_id']}
            AND a.room_type = 'module'
            ORDER BY a.title
            ";
        } else { //*** new record
            $SQL = "
            SELECT a.title
                  ,0 AS list
                  ,0 AS detail
                  ,0 AS new
                  ,0 AS edit
                  ,0 AS `delete`
                  ,0 AS publish
                  ,0 AS unpublish
                  ,0 AS print
                  ,0 AS import
                  ,0 AS export
                  ,a.list AS list_rm
                  ,a.detail AS detail_rm
                  ,a.new AS new_rm
                  ,a.edit AS edit_rm
                  ,a.delete AS delete_rm
                  ,a.publish AS publish_rm
                  ,a.unpublish AS unpublish_rm
                  ,a.print AS print_rm
                  ,a.import AS import_rm
                  ,a.export AS export_rm
                  ,a.room_id
            FROM mod_acc_room a
            WHERE a.room_type = 'module'
            ORDER BY a.title
            ";
        }

        $result = $db->sql_query($SQL);

        $rows = '';

        $rows .= "
        <tr>
            <th colspan='12' class='HeadeingTrTh'>Modules</th>
        </tr>
        ";

        while ($row2 = $db->sql_fetchrow($result)) {
            if ($tv['action'] == "detail") {
                $rows .= "
                <tr>
                    <td class='roomName'>{$row2['title']}</td>
                    {$this->_getAccessTDDetail($row2, 'list')}
                    {$this->_getAccessTDDetail($row2, 'detail')}
                    {$this->_getAccessTDDetail($row2, 'new')}
                    {$this->_getAccessTDDetail($row2, 'edit')}
                    {$this->_getAccessTDDetail($row2, 'delete')}
                    {$this->_getAccessTDDetail($row2, 'publish')}
                    {$this->_getAccessTDDetail($row2, 'unpublish')}
                    {$this->_getAccessTDDetail($row2, 'print')}
                    {$this->_getAccessTDDetail($row2, 'import')}
                    {$this->_getAccessTDDetail($row2, 'export')}
                </tr>
                ";
            } else {
                $rows .= "
                <tr>
                    <td class='roomName'>{$row2['title']} <input name='room_{$row2['room_id']}' type='hidden' /></td>
                    {$this->_getAccessTD($row2, 'list')}
                    {$this->_getAccessTD($row2, 'detail')}
                    {$this->_getAccessTD($row2, 'new')}
                    {$this->_getAccessTD($row2, 'edit')}
                    {$this->_getAccessTD($row2, 'delete')}
                    {$this->_getAccessTD($row2, 'publish')}
                    {$this->_getAccessTD($row2, 'unpublish')}
                    {$this->_getAccessTD($row2, 'print')}
                    {$this->_getAccessTD($row2, 'import')}
                    {$this->_getAccessTD($row2, 'export')}

                    <th class='click-all-side'>
                        <a href='#' class='check-all'><img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'></a>
                        <a href='#' class='uncheck-all'><img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'></a>
                    </th>
                </tr>
                ";
            }
        }

        if (isset($row['user_group_id'])) {
            $SQL = "
            SELECT a.title
                  ,b.list
                  ,b.detail
                  ,b.new
                  ,b.edit
                  ,b.delete
                  ,b.publish
                  ,b.unpublish
                  ,b.print AS print
                  ,b.import AS import
                  ,b.export AS export
                  ,a.list AS list_rm
                  ,a.detail AS detail_rm
                  ,a.new AS new_rm
                  ,a.edit AS edit_rm
                  ,a.delete AS delete_rm
                  ,a.publish AS publish_rm
                  ,a.unpublish AS unpublish_rm
                  ,a.print AS print_rm
                  ,a.import AS import_rm
                  ,a.export AS export_rm
                  ,a.room_id
            FROM mod_acc_room a
            LEFT JOIN mod_acc_room_user_group b
            ON (a.room_id = b.room_id)
            WHERE b.user_group_id = {$row['user_group_id']}
            AND a.room_type = 'widget'
            ORDER BY a.title
            ";
        } else { //*** new record
            $SQL = "
            SELECT a.title
                  ,0 AS list
                  ,0 AS detail
                  ,0 AS new
                  ,0 AS edit
                  ,0 AS `delete`
                  ,0 AS publish
                  ,0 AS unpublish
                  ,0 AS print
                  ,0 AS import
                  ,0 AS export
                  ,a.list AS list_rm
                  ,a.detail AS detail_rm
                  ,a.new AS new_rm
                  ,a.edit AS edit_rm
                  ,a.delete AS delete_rm
                  ,a.publish AS publish_rm
                  ,a.unpublish AS unpublish_rm
                  ,a.print AS print_rm
                  ,a.import AS import_rm
                  ,a.export AS export_rm
                  ,a.room_id
            FROM mod_acc_room a
            WHERE a.room_type = 'widget'
            ORDER BY a.title
            ";
        }

        $result = $db->sql_query($SQL);

        $rows .= "
        <tr>
            <th colspan='12' class='HeadeingTrTh'>Reports / Widgets</th>
        </tr>
        ";

        while ($row2 = $db->sql_fetchrow($result)) {
            if ($tv['action'] == "detail") {
                $rows .= "
                <tr>
                    <td class='roomName'>{$row2['title']}</td>
                    {$this->_getAccessTDDetail($row2, 'list')}
                    {$this->_getAccessTDDetail($row2, 'detail')}
                    {$this->_getAccessTDDetail($row2, 'new')}
                    {$this->_getAccessTDDetail($row2, 'edit')}
                    {$this->_getAccessTDDetail($row2, 'delete')}
                    {$this->_getAccessTDDetail($row2, 'publish')}
                    {$this->_getAccessTDDetail($row2, 'unpublish')}
                    {$this->_getAccessTDDetail($row2, 'print')}
                    {$this->_getAccessTDDetail($row2, 'import')}
                    {$this->_getAccessTDDetail($row2, 'export')}
                </tr>
                ";
            } else {
                $rows .= "
                <tr>
                    <td class='roomName'>{$row2['title']} <input name='room_{$row2['room_id']}' type='hidden' /></td>
                    {$this->_getAccessTD($row2, 'list')}
                    {$this->_getAccessTD($row2, 'detail')}
                    {$this->_getAccessTD($row2, 'new')}
                    {$this->_getAccessTD($row2, 'edit')}
                    {$this->_getAccessTD($row2, 'delete')}
                    {$this->_getAccessTD($row2, 'publish')}
                    {$this->_getAccessTD($row2, 'unpublish')}
                    {$this->_getAccessTD($row2, 'print')}
                    {$this->_getAccessTD($row2, 'import')}
                    {$this->_getAccessTD($row2, 'export')}

                    <th class='click-all-side'>
                        <a href='#' class='check-all'><img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'></a>
                        <a href='#' class='uncheck-all'><img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'></a>
                    </th>
                </tr>
                ";
            }
        }

        $checkAllRow = '';
        if ($tv['action'] != "detail") {
            $checkAllRow = "
            <tr class=''>
                <th></th>
                {$this->_getAccessTH('accList')}
                {$this->_getAccessTH('accDetail')}
                {$this->_getAccessTH('accNew')}
                {$this->_getAccessTH('accEdit')}
                {$this->_getAccessTH('accDelete')}
                {$this->_getAccessTH('accPublish')}
                {$this->_getAccessTH('accUnpublish')}
                {$this->_getAccessTH('accPrint')}
                {$this->_getAccessTH('accImport')}
                {$this->_getAccessTH('accExport')}
                <th></th>
            </tr>
            ";
        }

        $text = "
        <table class='thinlist room-actions-table'>
            <tr>
                <th>room name</th>
                <th>list</th>
                <th>detail</th>
                <th>new</th>
                <th>edit</th>
                <th>delete</th>
                <th>publish</th>
                <th>un-publish</th>
                <th>print</th>
                <th>import</th>
                <th>export</th>
                <th></th>
            </tr>
            {$checkAllRow}
            {$rows}
        </table>
        ";

        return $text;
    }



    /**
     *
     * @param <type> $className
     * @return <type>
     */
    private function _getAccessTH($className) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "
        <th class='click-all-top'>
            <a href='#' class='check-all'><img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'></a>
            <a href='#' class='uncheck-all'><img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'></a>
        </th>
        ";
        return $text;
    }

    /**
     *
     * @param <type> $row
     * @param <type> $action
     * @return <type>
     */
    private function _getAccessTD($row, $action) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $checkedText   = "<input name='chk_[pref]_{$row['room_id']}' value='1' type='checkbox' checked='checked' />";
        $uncheckedText = "<input name='chk_[pref]_{$row['room_id']}' value='1' type='checkbox' />";
        $unavailableText = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/cross.gif' />";

        $className = 'acc' . ucfirst($action);
        if (!$row[$action . "_rm"]) {
            $text = $unavailableText;
        } else if ($row[$action]) {
            $text = str_replace('[pref]', $action, $checkedText);
        } else {
            $text = str_replace('[pref]', $action, $uncheckedText);
        }

        $text = "
        <td class='{$className} room_{$row['room_id']}'>
            {$text}
        </td>
        ";
        return $text;
    }

    /**
     *
     * @param <type> $row
     * @param <type> $action
     * @return <type>
     */
    private function _getAccessTDDetail($row, $action) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $checkedText   = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/checkbox_checked.gif' />";
        $uncheckedText = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/checkbox_unchecked.gif' />";
        $unavailableText = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/cross.gif' />";

        if (!$row[$action . "_rm"]) {
            $text = $unavailableText;
        } else if ($row[$action]) {
            $text = $checkedText;
        } else  {
            $text = $uncheckedText;
        }

        $text = "<td>{$text}</td>";
        return $text;
    }

    /**
     *
     * @param <type> $row
     * @return <type>
     */
    private function _getOtherActionTableHTML($row = array()) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        if (isset($row['user_group_id'])) {
            $SQL = "SELECT a.title, a.name, a.room_id, a.other_action_id,
                           b.has_access, c.room_name, c.title AS room_title
                    FROM mod_acc_other_action a
                    LEFT JOIN mod_acc_user_group_other_action b
                    ON (a.other_action_id = b.other_action_id)
                    LEFT JOIN mod_acc_room c
                    ON (a.room_id = c.room_id)
                    WHERE b.user_group_id = {$row['user_group_id']}
                    ORDER BY c.room_name, a.title
                   ";
        } else { //*** new record
            $SQL = "SELECT a.title, a.name, a.room_id, 0 AS has_access, c.room_name,
                            a.other_action_id, c.title AS room_title
                    FROM mod_acc_other_action a
                    JOIN mod_acc_room c
                    ON (a.room_id = c.room_id)
                    ORDER BY c.room_name, a.title
                   ";
        }

        $result = $db->sql_query($SQL);

        $rows = '';
        while ($row2 = $db->sql_fetchrow($result)) {
            if ($tv['action'] == "detail") {
                $rows .= "
                <tr>
                    <td class='roomName'>{$row2['title']}</td>
                    {$this->_getAccessTDOtherAction($row2)}
                    <td class='roomName'>{$row2['room_title']}</td>
                </tr>
                ";
            } else {
                $rows .= "
                <tr>
                    <td class='roomName'>{$row2['title']} <input name='room_{$row2['room_id']}' type='hidden' /></td>
                    {$this->_getAccessTDOtherAction($row2)}
                    <td class='roomName'>{$row2['room_title']}</td>
                </tr>
                ";
            }
        }

        $text = "
        <table>
            <tr>
                <th>action name</th>
                <th>access</th>
                <th>room</th>
            </tr>
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     *
     * @param <type> $row
     * @return <type>
     */
    private function _getAccessTDOtherAction($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        if ($tv['action'] == 'detail') {
            $checkedText   = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/checkbox_checked.gif' />";
            $uncheckedText = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/checkbox_unchecked.gif' />";
        } else {
            $checkedText   = "<input name='other_act_access_{$row['other_action_id']}' value='1'
                                     type='checkbox' checked='checked' />";
            $uncheckedText = "<input name='other_act_access_{$row['other_action_id']}' value='1' type='checkbox' />";
        }

        if ($row['has_access'] == 1) {
            $text = $checkedText;
        } else {
            $text = $uncheckedText;
        }

        $text = "<td>{$text}</td>";
        return $text;
    }
}