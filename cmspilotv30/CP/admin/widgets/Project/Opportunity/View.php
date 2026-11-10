<?
class CP_Admin_Widgets_Project_Opportunity_View extends CP_Common_Lib_WidgetViewAbstract
{

    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');

        $oneMonthBefore = date('Y-m-d', mktime (0,0,0,date('m'),date('d')-31, date('Y')));

        $SQL = "
        SELECT o.opportunity_id
              ,o.title
              ,FORMAT(o.estimated_value, 0) AS estimated_value
              ,o.status
              ,date_format(o.enquiry_date, '%d %b %Y') AS enquiry_date
              ,c.company_name
        FROM opportunity o
        LEFT JOIN (company c)    ON (o.company_id = c.company_id)
        WHERE LOWER(o.status) != 'cancelled' 
              AND LOWER(o.status) != 'win' 
              AND LOWER(o.status) != 'lost'
              AND o.status != ''
              AND enquiry_date > '{$oneMonthBefore} 23:59:59'
        ORDER BY o.enquiry_date DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $url = "index.php?_topRm=project&module=opportunity&record_id={$row['opportunity_id']}&_action=detail";
            
            $rows .= "
            <tr>
                <td><a href='{$url}'>{$row['title']}</a></td>
                <td>{$row['company_name']}</td>
                <td>{$row['enquiry_date']}</td>
                <td class='txtRight'>{$row['estimated_value']}</td>
                <td>{$row['status']}</td>
            </tr>
            ";
        }

        $url = "index.php?_topRm=project&module=opportunity";
        $text = "
        <h2><a href='{$url}'>Latest Enquiries</a></h2>
        <div class='tableOuter'>
            <table>
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Company</th>
                    <th class='w75'>Enquiry Date</th>
                    <th class='txtRight'>Est. Value</th>
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