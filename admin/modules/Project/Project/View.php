<?
class CPL_Admin_Modules_EnggCrm_Project_View extends CP_Admin_Modules_EnggCrm_Project_View
{
    /**
     *
     */
    function getListFooter() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $db = Zend_Registry::get('db');

        $searchVar->sqlSearchVar = array();

        $fld_suffix = '';
        if ($cpCfg['m.enggCrm.hasMultiCurrency'] == 1){
            $fld_suffix = '_base';
        }

        $mode = ($tv['spAction'] == 'link') ? 'link' : '';
        $modProject = getCPModuleObj('enggCrm_project');
        $SQLSum  = $modProject->model->getProjectValueSumSQL('project_value');

        $SQLSum .= $searchVar->getSearchVar($tv['module'], 0);
        $SQLSum .= "
        AND LOWER(p.status) != 'lost'
        AND LOWER(p.status) != 'cancelled'
        ";
        $resSum = $db->sql_query($SQLSum);
        $row = $db->sql_fetchrow($resSum);
        $total1 = $row[0];

        $searchVar->sqlSearchVar = array();
        $modProject = getCPModuleObj('enggCrm_project');
        $SQLSum  = $modProject->model->getProjectValueSumSQL('still_to_bill');

        $SQLSum .= $searchVar->getSearchVar($tv['module'], 0);
        $SQLSum .= "
        AND LOWER(p.status) != 'lost'
        AND LOWER(p.status) != 'cancelled'
        ";

        $resSum = $db->sql_query($SQLSum);
        $row = $db->sql_fetchrow($resSum);
        $total2 = $row[0];

        $text = "
            </tbody>
            <tfoot>
                <tr class='header' >
                    <td colspan='12'></td>
                    <td class='txtRight'>{$total1}</td>
                    <td class='txtRight'>{$total2}</td>
                    <td colspan='8'></td>
                </tr>
                <input type='hidden' name='boxChecked' value='0' />
                <input type='hidden' name='task' value='' />
            </form>
            </tfoot>
        </table>
        ";

        return $text;
    }
}
