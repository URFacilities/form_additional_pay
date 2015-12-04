<?php
// ========================================================================================
// AUTOLOAD PHP PACKAGES via COMPOSER and SHORTEN CLASS NAMES
// ======================================================================================== 
require_once __DIR__ . '/vendor/autoload.php';
use Respect\Validation\Validator as v;						//form validator
use Facilities\DataLayer\DBConnection as DBConnection;		
use Facilities\DataLayer\FormQueries as FormQueries;		
use Facilities\PresentationLayer\FormView as FormView;	
use Facilities\PresentationLayer\PageView as PageView;
use Facilities\BusinessLayer\Utilities as Utilities;
//use FacilitiesBusinessLayerSwiftMail as SwiftMail; 

try {
	// ================================================================================++
	// GET VARS
	// ================================================================================== 
	$disabled = ''; 	//so submits can be disabled when appropriate
	$row = array();		//so form values are initially blank
	$readonly = '';		//so form values can be locked when appropriate
			
	//to determine which sections of form are shown and where form is sent
	$who = filter_input(INPUT_GET, 'who', FILTER_SANITIZE_STRING); 
Utilities::msg_alert("value of who is ", $who);
	if(!$who || $who == '' || $who == 'super') { $process_page = 'process_request.php'; }	//supervisor fills out request and submits
	if($who == 'biz_mgr') { $process_page = 'process_response.php'; }	//biz mgr approves or denies request
	if($who == 'admin') { $process_page = 'process_admin.php'; }		//admin fills in missing details on form

	//must have request ID
	if($who == 'biz_mgr' || $who == 'admin') {
		$readonly = 'readonly';	//locks previously submitted values, cannot change
		
		if(!isset($_GET['id'])) {
			Utilities::msg_alert('Appropriate parameters not provided for access.', 'Please go back and try again.');
			echo "Error: Missing Parameters.<br/>";	
			exit;
		}

		$reqID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT); 
		
		// ==============================================================================
		// MYSQL DATABASE CONNECTION - facilities (stores request details)
		// ==============================================================================
		$connector2 = new DBConnection('facil2');
		$conn2 = $connector2->openConnection();
		
		// Find matching request
		$sql2 = "SELECT
		  `date_of_request` ,
		  `requester_fname` ,
		  `requester_lname` ,
		  `requester_phone` ,
		  `employee_fname` ,
		  `employee_lname` ,
		  `dept_name` ,
		  `dept_phone` ,
		  `reason_for_payment` ,
		  `work_start_date` ,
		  `work_end_date` ,
		  `job_title` ,
		  `description_of_work` ,
		  `work_classification` ,
		  `emp_ID` ,
		  `emp_record_num` ,
		  `union_code` ,
		  `primary_job_code` ,
		  `current_pay_rate` ,
		  `addl_work_job_code` ,
		  `addl_work_pay_rate` ,
		  `total_dollars` ,
		  `fao_account_num` ,
		  `business_mgr`
		FROM addl_pay_request
		WHERE request_id = :id LIMIT 1";
		
		// ======================================================================================= //
		// USE PREPARED STATEMENT, BIND AND FETCH RESULTS
		// ======================================================================================= //
		//prepare
		$stmt2 = $conn2->prepare($sql2);
		if(!$stmt2) {
		    throw new Exception("Error getting statement handle - PDO::errorInfo(): " . print_r($conn2->errorInfo(),true));
		}
		//bind
		$stmt2->bindParam(':id', $reqID, PDO::PARAM_INT); // <-- Automatically sanitized for SQL by PDO
 			    		
		//execute
		$err_array = array();
		$stmt2->execute();
		if(count($err_array) != 0) {
			throw new Exception("Error executing query - PDO::errorInfo(): " . print_r($stmt2->errorInfo(),true));
		}
		//fetch
		$row = $stmt2->fetch(PDO::FETCH_ASSOC);	
		if(!$row) {
		    throw new Exception("Error fetching data - PDO::errorInfo(): " . print_r($conn2->errorInfo(),true));
		}
		
		//check if already responded
		if($who == 'biz_mgr' && !IS_NULL($row['date_of_response'])) {
	    	Utilities::msg_alert("You have already responded to this request.", "");
			$disabled = 'disabled';
	    }	
		if($who == 'admin' && !IS_NULL($row['date_admin_submit'])) {
	    	Utilities::msg_alert("This request was already processed and the notices were sent out.", "");
			$disabled = 'disabled';
	    }
	} //end who = biz_mgr/admin

// ================================================================================== 
// MYSQL DATABASE CONNECTION - facil_csc (stores dept,clerk,biz_mgr lists)
// ================================================================================== 
$connector = new DBConnection('facil');
$conn = $connector->openConnection();

// ================================================================================== 
// BOOTSTRAP PAGE TOP WITH NAVIGATION
// ==================================================================================  
$page_view = new PageView;
$page_view->createHTMLHead('Additional Pay Form::Facilities and Services:: University of Rochester');

echo "<body class='home blue'>";		// class determines style of header: home, other... 
    
$page_view->createMainHeader();
       
?>  

<main>           
	<div class="top_pad"> <!-- Padding matches height of Header -->
	
		<!-- // ================================================================================== 
                PAGE HEADING			
        <!-- // ==================================================================================	-->
	    <div class="container ">	    		    
		    <section class='page-header'>	
		    	<h1><small>Facilities Forms</small><br/>	
		    	Extra/Additional Pay to Hourly Paid Faculty/Staff/Student</h1>
		        <p>This form must be submitted for approval to authorize additional pay. The finalized forms will be submitted to University HR 
		        prior to sending to payroll.</p>
		    </section> 
		</div>
		<div id="err" class="container" style="display:none;"></div>
		<br/>

		<!-- // ================================================================================== 
                BOOTSTRAP FORM - utilizes jquery validate plugin 			
        <!-- // ==================================================================================	-->		
		<div class='container form_wrap'>
		<?php
			echo "<form id='addl_pay' class='form-horizontal' method='post' action='$process_page' role='form' >";
			echo "<input type='hidden' class='form-control' id='date' name='date' >";	
			echo "<input type='hidden' name='permanganese' value='' />";			//spam honeypot
			echo "<input type='hidden' name='who' id='who' value='$who' />";		//controls whether to show admin section or not
			echo "<input type='hidden' name='id' value='$reqID' />"; //stores reqID if form previously submitted
		
			// ================================================================================== 
		    //  FORM: REQUESTER INFO			
		    // ================================================================================== -->	    			
			include_once("./includes/requester_detail_view.php");		 
 					
			// ================================================================================== 
		    //  FORM: EMPLOYEE INFO			
		    // ==================================================================================	 -->
		    include_once("./includes/employee_detail_view.php");
			
			// ================================================================================== 
		    //  FORM: ADDITIONAL WORK INFO			
		    // ==================================================================================	 -->
		    include_once("./includes/work_detail_view.php");
		    
			// ================================================================================== 
		    //  FORM: ADMINISTRATIVE INFO			
		    // ==================================================================================	 -->
		    include_once("./includes/admin_detail_view.php");
			
			// ================================================================================== 
		    //  FORM: SUBMIT			
		    // ==================================================================================	 -->
			if($who == 'biz_mgr') {
				echo "<div class='form-group'>
			    	<div class='col-md-offset-2 col-md-10'>
				      <button name='response' type='submit' value='approved' class='btn btn-primary' $disabled >Approve</button> &nbsp;&nbsp;&nbsp;
				      <button name='response' type='submit' value='denied' class='btn btn-primary' $disabled >Deny</button>
				    </div>
				</div>";	
			} else {
				echo "<div class='form-group'>
				    <div class='col-md-offset-2 col-md-10'>				  
					  <button id='request' type='submit' class='btn btn-primary' $disabled>Submit</button>
					</div>
				</div>";
			}
			
?> 
			
			 
			</form>
			<br/><br/>
    	</div><!-- end wrap -->
    	
    </div><!-- end top pad -->
</main>
<br/><br/>

<?php
// ================================================================================== 
// BOOTSTRAP PAGE BOTTOM WITH JAVASCRIPTS
// ================================================================================== 
	// UFS Footer -->
	$page_view->createFooter('php');
	
	// Scroll to Top -->
    $page_view->displayScrollToTop();       
 
   	// Jquery and Bootstrap CDN
   $page_view->includeJavascripts();
 
   // Form UI and Validation
   $page_view->includeFormScripts();
?>

	<!-- // ================================================================================== 
	        CUSTOM JAVASCRIPTS 			
	<!-- // ==================================================================================	-->	
	<!-- Standard JS -->
    <script src="http://www.facilities.rochester.edu/scripts/scripts.js"></script>
    <!-- Custom JS -->  
    <script src="./js/custom_scripts.js"></script>
    
</body>
</html>

<?php
//close all connections
$stmt2 = null;
$conn2 = null;
$conn = null; 

} catch (Exception $e) {
    Utilities::msg_alert($e->getMessage(), "");
    Utilities::close();		//closes window opened by alert
    exit;
}
?>	