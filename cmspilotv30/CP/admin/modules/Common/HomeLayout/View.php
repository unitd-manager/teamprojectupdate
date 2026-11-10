<?
class CP_Admin_Modules_Common_HomeLayout_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList(){
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $modulesArr = Zend_Registry::get('modulesArr');
        $tv = Zend_Registry::get('tv');

        $text = "
        <div class='floatbox'>
			<div class='projectHeading'>
				<h1> PROJECT </h1>
			</div>
            <div class='actionProjectBtns'>
				<ul>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=common_dashboard'>Dashboard</a></li>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=project_company'>Company</a></li>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=project_contact'>Contact</a></li>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=project_project'>Project</a></li>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=project_invoice'>Invoice</a></li>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=project_task'>Task</a></li>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=project_timesheet'>Timesheet</a></li>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=project_attendance'>Attendance</a></li>
				</ul>
            </div>        
			<div class='marketingHeading'>
				<h1> MARKETING </h1>
			</div>
            <div class='actionMarketingBtns'>
				<ul>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=common_interest'>Interest</a></li>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=common_broadcast'>Broadcast</a></li>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=common_template'>Template</a></li>
				</ul>
            </div>        
			<div class='adminHeading'>
				<h1> ADMIN </h1>
			</div>
            <div class='actionAdminBtns'>
				<ul>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=core_userGroup'>User Group</a></li>                
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=core_staff'>Staff</a></li>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=core_valuelist'>Valuelist</a></li>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=core_setting'>Setting</a></li>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=project_quoteTemplate'>Quote Template</a></li>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=project_creditCardPayments'>Credit Card Payments</a></li>
				</ul>
            </div>        
			<div class='reportsHeading'>
				<h1> REPORTS </h1>
			</div>
            <div class='actionReportsBtns'>
				<ul>
                   	<li><a class='button' href='/admin/index.php?_topRm=project&module=project_reports'>Reports</a></li>                
				</ul>
            </div>        
        </div>
        ";
        
        return $text;
    }
}