<?php
//****************************************************************************************//
//
// THIS PAGE PULLS UP MATCHING RECORD AND DISPLAYS IT AS A FORM
//
//****************************************************************************************//
// ========================================================================================
// AUTOLOAD PHP PACKAGES via COMPOSER and IMPORT CLASSES
// ======================================================================================== 
require_once __DIR__ . '/vendor/autoload.php';    

use FacilitiesDataLayerDBConnection as DBConnection;
use FacilitiesDataLayerFormQueries as FormQueries;
use Facilities\PresentationLayer\PageView as PageView;
use FacilitiesBusinessLayerUtilities as Utilities;
use FacilitiesBusinessLayerSwiftMail as SwiftMail; 

// ================================================================================== 
// MYSQL DATABASE CONNECTION 
// ================================================================================== 
$connector = new DBConnection('facil2');
$conn = $connector->openConnection();

		
// ======================================================================================= //
// EXIT IF NO ID
// ======================================================================================= //
if(!isset($_GET['id'])) {
	Utilities::msg_alert('Appropriate parameters not provided for access.', 'Please go back and try again.');
	echo "Error: Missing Parameters.<br/>";	
	exit;
}

$reqID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT); 
$disabled = ''; 	//so submits can be disabled if necessary
		
try {	
	// ======================================================================================= //
	// QUERY DB FOR MATCH
	// ======================================================================================= //   		
	$sql = "SELECT `date_of_request` ,
		  `requester_fname` ,
		  `requester_lname` ,
		  `dept_name` ,
		  `crew` ,
		  `building` ,
		  `room` ,
		  `phone_number` ,
		  `fax_number` ,
		  `job_title` ,
		  `has_patient_contact` ,
		  `reports_to_fname` ,
		  `reports_to_lname` ,
		  `reports_to_phone` ,
		  `reports_to_fax` ,
		  `who_replaced_fname` ,
		  `who_replaced_lname` ,
		  `reason_explanation` ,
		  `start_date` ,
		  `end_date` ,
		  `start_time` ,
		  `end_time` ,
		  `weekly_hours` ,
		  `pay_rate` ,
		  `charge_rate` ,
		  `account_number` ,
		  `work_days` ,
		  `is_iuoe` ,
		  `candidate_identified` ,
		  `candidate_fname` ,
		  `candidate_lname` ,
		  `job_description` ,
		  `specific_skills` ,
		  `num_temps` ,
		  `impact_if_refused` ,
		  `other_options` ,
		  `business_mgr` ,
		  `date_of_response`,
		  `response`
		FROM request 
		WHERE request_id = :id LIMIT 1";
		
		
	// ======================================================================================= //
	// USE PREPARED STATEMENT, BIND AND FETCH RESULTS
	// ======================================================================================= //
	//prepare
	$stmt = $conn->prepare($sql);
	if(!$stmt) {
	    throw new Exception("Error getting statement handle - PDO::errorInfo(): " . print_r($conn->errorInfo(),true));
	}
	//bind
	$stmt->bindParam(':id', $reqID, PDO::PARAM_INT); // <-- Automatically sanitized for SQL by PDO
	//execute
	$err_array = array();
	$stmt->execute();
	if(count($err_array) != 0) {
		throw new Exception("Error executing query - PDO::errorInfo(): " . print_r($stmt->errorInfo(),true));
	}
	//fetch
	$row = $stmt->fetch(PDO::FETCH_ASSOC);	
	if(!$row) {
	    throw new Exception("Error fetching data - PDO::errorInfo(): " . print_r($conn->errorInfo(),true));
	}
	
	//check if already responded
	if(!IS_NULL($row['date_of_response'])) {
    	Utilities::msg_alert("This request was already processed and the notices were sent out.", "");
		$disabled = 'disabled';
    }	
				
	
	// ======================================================================================= //
	// GET REASONS
	// ======================================================================================= //
	//to get number of rows
	$sql_count = "SELECT COUNT(*) FROM request WHERE request_id = $reqID";
	$result = $conn->query($sql_count);	
	if($result->fetchColumn() == 0) {
		Utilities::msg_alert("No matching record in database", "Access Denied");
		Utilities::go_to("http://www.facilities.rochester.edu/apps/request_for_temp/index.php");
	} else {	                              
		//GET REASONS
		$reasons = array();
		$sql2="SELECT reason_id FROM request_reason WHERE request_id = $reqID";   
		$stmt2 = $conn->prepare($sql2);
		$stmt2->execute(array($reqID));
			while($row2  = $stmt2->fetch(PDO::FETCH_ASSOC)) {
			$reasons[] = $row2['reason_id'];
		}  			    
                
// ================================================================================== 
// BOOTSTRAP PAGE TOP WITH NAVIGATION
// ==================================================================================  
	$page_view = new PageView;
	$page_view->createHTMLHead('Additional Pay Form::Facilities and Services:: University of Rochester');

    echo "<body class='home blue'>";		// class determines style of header: home, other... 
    
    	$page_view->createMainHeader();
				?>

<!-- // ================================================================================== 
<!-- // CUSTOM STYLES
<!-- // ================================================================================== -->				
<style>
	div.form_wrap {
		background-color:#D9EDF7; 
		border-radius:25px;
	}
	
	div.form_wrap h3 {
		margin:2%;
	}
	
	hr {
		width:90%; 
		padding-left:9%; 
		border:1px solid #FFFFFF;
	}
</style>
           
	<div class="top_pad"> <!-- Padding matches height of Header -->
	
		<!-- // ================================================================================== 
                PAGE HEADING			
        <!-- // ==================================================================================	-->
	    <div class="container ">	    		    
		    <section class='page-header'>	
		    	<h1><small>Facilities Forms</small><br/>	
		    	Approval for Temporary Employee</h1>
		        <p>Your approval is required to authorize the use of temporary help. Please approve or deny the request below.</p>
		    </section> 
		</div>
		<br/>
	
		<!-- // ================================================================================== 
                BOOTSTRAP FORM  			
        <!-- // ==================================================================================	-->	
		<div class='container form_wrap'>	
		<?php

			$readonly = 'readonly'; 	//so form values are not editable
			echo "<form id='temp_hire' class='form-horizontal' method='post' action='process_response.php' role='form' >";
			// ================================================================================== 
	        //  FORM: REQUESTER INFO			
	        // ==================================================================================       	    
			include_once("includes/form_requester.php");							
			// ================================================================================== 
			//  FORM: TEMP POSITION INFO			
			// ==================================================================================
			include_once("includes/form_temp_position.php");					
			// ================================================================================== 
			//  FORM: ADMINISTRATIVE INFO	
			//  Note: values from 'reason' table in facilities db	: should be created dynamically
			// ==================================================================================	
			include_once("includes/form_administrative.php");	 	
		?> 				
			<input type="hidden" name="permanganese" value="" />
			<input type="hidden" name="id" value="<?php echo $reqID; ?>" />
						
		 	<div class="form-group">
		    	<div class="col-md-offset-2 col-md-10">
			      <button name="response" type="submit" value='approved' class="btn btn-primary" <?php echo $disabled; ?>>Approve</button> &nbsp;&nbsp;&nbsp;
			      <button name="response" type="submit" value='denied' class="btn btn-primary" <?php echo $disabled; ?>>Deny</button>
			    </div>
			</div>		 	
			</form>
			<br/><br/>		
		</div><!-- end wrap -->
	
    </div><!-- end top pad -->
    <br/><br/>
 
<?php
	}	// end else
	
// ================================================================================== 
// BOOTSTRAP PAGE BOTTOM WITH JAVASCRIPTS
// ================================================================================== 
	// ==================================================================================
		include_once("/www_vol/data/facil-data/includes/ufs_footer.php");				// UFS Footer -->
	// ================================================================================== 
	
	//<!-- Scroll to Top -->
    echo "<div id='topcontrol' title='Scroll to Top' style='display:none;'>
		  <a class='top'><img src='http://www.facilities.rochester.edu/images/up.png'/></a>
		  </div>";
       
    // ==================================================================================  
   		include_once("/www_vol/data/facil-data/includes/javascripts.html"); 				// Jquery and Bootstrap CDN
    // ==================================================================================
   
    // ==================================================================================  
   		include_once("/www_vol/data/facil-data/includes/javascripts_forms.html"); 		// Form UI and Validation
    // ==================================================================================
?>  

	<!-- // ================================================================================== 
	        CUSTOM JAVASCRIPTS 			
	<!-- // ==================================================================================	-->	  
    <!-- Standard JS -->
    <script src="http://www.facilities.rochester.edu/scripts/scripts.js"></script>
    <!-- Custom JS -->  
    <script>
		/* FORM: auto populate today's date */
		var now = new Date();
		var day = ("0" + now.getDate()).slice(-2);
		var month = ("0" + (now.getMonth() + 1)).slice(-2);		
		/*var today = now.getFullYear()+"-"+(month)+"-"+(day) ;*/
		var today = (month) + "-" + (day) + "-" + now.getFullYear();
		$('#date').val(today);
		
		/* ================================================================================== 
	        FORM VALIDATOR HANDLES FORM SUBMIT 			
		/* ================================================================================ */		 
		/* FORM: validation */
		$("#temp_hire").validate({
			submitHandler: function(form) {
				form.submit();
			}
		});
				
		/* ================================================================================== 
	        MAKE CHECKBOXES, RADIOS & SELECTS READONLY 			
		/* ================================================================================ */
		$(':checkbox[readonly]').click(function(){
            return false;
        });
		$(':checkbox[readonly]:not(:checked)').prop('disabled', true);
        $(':radio[readonly]:not(:checked)').prop('disabled', true);
       
        $('option:not(:selected)').prop('disabled', true);
    </script> 
    
	</body>
</html>
		 
<?php

} catch (Exception $e) {
    Utilities::msg_alert($e->getMessage(), "");
    Utilities::close();		//closes window opened by alert
    exit;
}


 	
?>		