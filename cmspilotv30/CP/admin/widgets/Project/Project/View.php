<?
class CP_Admin_Widgets_Project_Project extends CP_Common_Lib_WidgetViewAbstract
{

    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT p.project_id
              ,p.title
              ,p.status
              ,c.company_name
        FROM 
        project p
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
        WHERE LOWER(p.status) = 'wip' 
        ORDER BY p.project_id DESC
        limit 0, 20
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $url = "index.php?_topRm=project&module=project&record_id={$row['project_id']}&_action=detail";
            
            $rows .= "
            <tr>
                <td><a href='{$url}'>{$row['title']}</a></td>
                <td>{$row['company_name']}</td>
                <td>{$row['status']}</td>
            </tr>
            ";
        }

        $url = "index.php?_topRm=project&module=project";
        $text = "
        <h2><a href='{$url}'>WIP Projects</a></h2>
        <div class='tableOuter'>
            <table>
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Company</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
        </div>
        ";

        return $text;
    }
}