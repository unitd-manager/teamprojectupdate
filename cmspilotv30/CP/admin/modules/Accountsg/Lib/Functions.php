<?
class CP_Admin_Modules_Accountsg_Lib_Functions
{
    /**
     *
     */
    function getAccCatDropdown($selectedId = '') {
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
            $selected = ($selectedId == $row['acc_category_id']) ? " selected='selected'" : '';
            $rows .= "
            <option class='level1' value='{$row['acc_category_id']}'{$row['acc_category_id']}{$selected}>{$row['title']}</option>
            {$this->getAccCatChildDropdown($row['acc_category_id'], $selectedId)}
            ";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getAccCatChildDropdown($parent_id, $selectedId, $level=2) {
        $db = Zend_Registry::get('db');

        $rows = "";

        $SQL = "
        SELECT acc_category_id
              ,title
        FROM acc_category
        WHERE parent_id = {$parent_id}
        ORDER BY code
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $selected = ($selectedId == $row['acc_category_id']) ? " selected='selected'" : '';
            $rows .= "
            <option class='level{$level}' value='{$row['acc_category_id']}'{$selected}>{$row['title']}</option>
            {$this->getAccCatChildDropdown($row['acc_category_id'], $selectedId, $level+1)}
            ";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}
