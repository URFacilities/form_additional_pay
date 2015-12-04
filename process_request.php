<?php
// ======================================================================================
// AUTOLOAD PHP PACKAGES via COMPOSER and IMPORT CLASSES
// ======================================================================================== 
require_once __DIR__ . '/vendor/autoload.php';
use Respect\Validation\Validator as v;						//form validator
use Facilities\DataLayer\DBConnection as DBConnection;		//UFS MVC layers		
use Facilities\PresentationLayer\PageView as PageView;
use Facilities\BusinessLayer\Utilities as Utilities;
use Facilities\BusinessLayer\FormFns as FormFns;
use Facilities\BusinessLayer\SwiftMail as SwiftMail;

// ========================================================================================
// VERIFY POST METHOD
// ======================================================================================== 
$REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
if(!($REQUEST_METHOD=='POST')){
	Utilities::msg_alert("no post", "please go back and try again"); 
	Utilities::go_to("index.php"); 
}

// VARS
$return_path = 'bpitoni@ur.rochester.edu';
$who = filter_input(INPUT_GET, 'who', FILTER_SANITIZE_STRING); 


try {
	// ========================================================================================
	// SPAM CHECK
	// ========================================================================================
    if($_POST['permanganese'] == '' || !isset($_POST['permanganese'])) {

	// ========================================================================================
	// VALIDATE AND SANITIZE FORM DATA
	// ========================================================================================    	
		$err_msg = '';
		$form_data = array();
		$fields_to_skip = array('permanganese','other_reason','work_class','union_code');	//not required

		// ================================================================
		// VALIDATE POST VALUES
		// ================================================================  		
		//validate and sanitize all POST values
		foreach($_POST as $key => $value) {
			$form_data[$key] = FormFns::sanitizeInput($value);			//load array with sanitized form data				
		}		
							
		// ========================================================================================
		// ADJUST VALUES FOR DATABASE INSERT
		// ======================================================================================== 
		if($form_data['reason'] == 'Other') {
			$form_data['reason'] = $form_data['other_reason'];
		}
		//format dates ('YYYY-MM-DD') and times ('00:00:00') for mysql 
		
		//split date range into two values (start, end)
		$dates_array = explode(' to ', $form_data['date_range']);
		$start_date = $dates_array[0];	
		$mysql_start_date = date("Y-m-d", strtotime($start_date)); 
		$end_date = $dates_array[1];
		$mysql_end_date = date("Y-m-d", strtotime($end_date)); 
		
		//instantiate vars for queries
		$department = $_POST['dept_name'];		//sanitized dept names convert ampersand to html special char &amp;
		$req_fname = $form_data['requester_fname'];
		$req_lname = $form_data['requester_lname'];
		$job_title = $form_data['job_title'];
		$emp_fname = $form_data['emp_fname'];
		$emp_lname = $form_data['emp_lname'];
		
		// ========================================================================================
		// FILTER DATA: VALIDATE
		// ======================================================================================== 
		// Alphanumeric with some typical symbols
		if(v::not(v::alnum("-.,()'"))->validate($form_data['requester_fname'])) { $err_msg .= "Your first name must be alphanumeric.\\n"; }
		if(v::not(v::alnum("-.,()'"))->validate($form_data['requester_lname'])) { $err_msg .= "Your last name must be alphanumeric.\\n"; }
		if(v::not(v::alnum("-.,()'"))->validate($form_data['emp_fname'])) { $err_msg .= "The first name of the employee must be alphanumeric.\\n"; }
		if(v::not(v::alnum("-.,()'"))->validate($form_data['emp_lname'])) { $err_msg .= "The last name of the employee must be alphanumeric.\\n"; }
		//if(v::not(v::alnum("-.,()'"))->validate($form_data['account_number'])) { $err_msg .= "The account number must be alphanumeric.\\n"; }

		// Phone
		if(v::not(v::phone())->validate($form_data['requester_phone'])) { $err_msg .= "Your phone number is not valid: " . $form_data['requester_phone'] . "\\n"; }
		if(v::not(v::phone())->validate($form_data['dept_phone'])) { $err_msg .= "The department phone number is not valid: " . $form_data['dept_phone'] . "\\n"; }
		
		// Date or Time - should not be in future
		if(v::not(v::date('Y-m-d')->max('today'))->validate($mysql_start_date)) { $err_msg .= "The start date is invalid: " . $start_date . "\\n"; }
		if(v::not(v::date('Y-m-d')->max('today'))->validate($mysql_end_date)) { $err_msg .= "The end date is invalid: " . $end_date . "\\n"; }

			 		
	// ========================================================================================
	// DATA PASSED VALIDATION - INSERT INTO DB AND SEND EMAILS
	// ========================================================================================
	if($err_msg == '') {

		// ========================================================================================
		// GET BUSINESS MANAGER
		// ======================================================================================== 
		$facil_conn = new DBConnection('facil');	
		$conn1 = $facil_conn->openConnection();			
		$stmt1 = $conn1->prepare("SELECT hr_business_mgr.mgr_fname, hr_business_mgr.mgr_lname, hr_business_mgr.mgr_email FROM hr_business_mgr, a_workplaces WHERE a_workplaces.workplace = :dept AND a_workplaces.business_mgr = hr_business_mgr.mgrID");
		$stmt1->bindParam(":dept", $department, PDO::PARAM_STR);
		$stmt1->execute();
		
		if($stmt1->error) {
			throw new Exception($stmt1->errno . ' ' . $stmt1->error . '<br/>There was an error submitting this request.  Please click the back button and try again.');
			exit;
		}	
		
		$biz_mgr = $stmt1->fetch(PDO::FETCH_ASSOC);
		$mgrName = $biz_mgr['mgr_fname'] . " " . $biz_mgr['mgr_lname'];
		$mgrEmail = $biz_mgr['mgr_email']; 		
		$stmt1 = null; 
		$conn1 = null;
		
		// ========================================================================================
		// ADD REQUEST TO DATABASE
		// using named placeholders
		// ======================================================================================== 		
		$facil2_conn = new DBConnection('facil2');
		$conn2 = $facil2_conn->openConnection();
			
		$sql2 = "INSERT INTO addl_pay_request(`date_of_request` ,
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
		  `business_mgr`)
		VALUES (CURDATE(),
		  :req_fname ,
		  :req_lname ,
		  :req_phone ,
		  :emp_fname ,
		  :emp_lname ,
		  :dept_name ,
		  :dept_phone ,
		  :reason_for_payment ,
		  :work_start_date ,
		  :work_end_date ,
		  :job_title ,
		  :description_of_work ,
		  :work_classification ,
		  :biz_mgr
		  )";	
		/*$sql2 = "INSERT INTO addl_pay_request(`date_of_request` ,
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
		  `business_mgr`)
		VALUES (CURDATE(),
		  :req_fname ,
		  :req_lname ,
		  :req_phone ,
		  :emp_fname ,
		  :emp_lname ,
		  :dept_name ,
		  :dept_phone ,
		  :reason_for_payment ,
		  :work_start_date ,
		  :work_end_date ,
		  :job_title ,
		  :description_of_work ,
		  :work_classification ,
		  :emp_ID ,
		  :emp_record_num ,
		  :union_code ,
		  :primary_job_code ,
		  :current_pay_rate ,
		  :addl_work_job_code ,
		  :addl_work_pay_rate ,
		  :total_dollars ,
		  :fao_account_num ,
		  :biz_mgr
		  )";*/
		
		if ($stmt2 = $conn2->prepare($sql2)) {
            $stmt2->execute( array(
            ':req_fname'=>$form_data['requester_fname'], 
            ':req_lname'=>$form_data['requester_lname'], 
            ':req_phone'=>$form_data['requester_phone'],
            ':emp_fname'=>$form_data['emp_fname'], 
            ':emp_lname'=>$form_data['emp_lname'],
            ':dept_name'=>$department,  
            ':dept_phone'=>$form_data['dept_phone'],
            ':reason_for_payment'=>$form_data['reason'], 
            ':work_start_date'=>$mysql_start_date,
			':work_end_date'=>$mysql_end_date,            
			':job_title'=>$form_data['job_title'],
			':description_of_work'=>$form_data['work_description'],
			':work_classification'=>$form_data['work_class'],			
			':biz_mgr'=>$mgrEmail	)	 	
        	); 
			
		/*if ($stmt2 = $conn2->prepare($sql2)) {
            $stmt2->execute( array(
            ':req_fname'=>$form_data['requester_fname'], 
            ':req_lname'=>$form_data['requester_lname'], 
            ':req_phone'=>$form_data['requester_phone'],
            ':emp_fname'=>$form_data['emp_fname'], 
            ':emp_lname'=>$form_data['emp_lname'],
            ':dept_name'=>$department,  
            ':dept_phone'=>$form_data['dept_phone'],
            ':reason_for_payment'=>$form_data['reason'], 
            ':work_start_date'=>$mysql_start_date,
			':work_end_date'=>$mysql_end_date,            
			':job_title'=>$form_data['job_title'],
			':desc_of_work'=>$form_data['work_description'],
			':work_classification'=>$form_data['work_class'],
			':emp_ID'=>$form_data['emp_ID'],
			':emp_record_num'=>$form_data['emp_record_num'],
			':union_code'=>$form_data['union_code'],
			':primary_job_code'=>$form_data['primary_job_code'],
			':current_pay_rate'=>$form_data['current_pay_rate'],
			':addl_work_job_code'=>$form_data['addl_work_job_code'],
			':addl_work_pay_rate'=>$form_data['addl_work_pay_rate'],
			':total_dollars'=>$form_data['total_dollars'],
			':fao_account_num'=>$form_data['fao'],
			':biz_mgr'=>$mgrEmail	)	 	
        	); */
			
			if($stmt2->error) {
				throw new Exception($stmt2->errno . ' ' . $stmt2->error . '<br/>There was an error submitting this request.  Please click the back button and try again.');
				exit;
			}		
		} //end of prepared statement
	

		// ========================================================================================
		// CREATE AND SEND EMAIL
		// ======================================================================================== 	
		//CREATE EMAIL CONTENT
		include_once('./includes/email.php');
		$email_msg = $email_start . $email_body . $biz_mgr_approval_link . $email_end;
	
		//SEND EMAIL TO BUSINESS MANAGER USING SWIFT MAILER
	    //Create the Mailer using SMTP Transport
        $mailer = Swift_Mailer::newInstance(
        	Swift_SmtpTransport::newInstance('smtp-gw.rochester.edu')
		);    		
        //Create Error Logger
        $logger = new Swift_Plugins_Loggers_ArrayLogger();
        $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));		   
        
        // ====================================================================================
        // EMAIL TO BUSINESS MANAGER  
        // ====================================================================================   
        // CREATE MESSAGE               
        $swift_msg = Swift_Message::newInstance("Requires Approval: Additional Pay");			
		$swift_msg->setFrom('noreply@rochester.edu');
$swift_msg->setTo($return_path);
	    if($bcc != '') { $swift->setBcc($bcc); }
	    $swift_msg->setBody($email_msg, 'text/html');
	    $swift_msg->setReturnPath($return_path); 
		
		// ECHO HEADERS FOR TROUBLESHOOTING
		/*
		$headers = $swift_msg->getHeaders();
		foreach ($headers->getAll() as $header) {
		  printf("%s<br />\n", $header->getFieldName());
		}
		echo "<br/>" . $headers->toString() . "<br/>"; */
        
		// SEND MESSAGE      
        $num_sent = $mailer->send($swift_msg, $failedRecipients);  
		// CATCH ERRORS
		if(count($failedRecips) != 0) {
            $err_msg = "These addresses failed: " . print_r($failedRecips, true) . " Original message:<br/>" . $email_msg;
        } elseif(!$num_sent || $num_sent == 0) {
            $err_msg = "Log: " . $logger->dump() . " Original message:<br/>" . $email_msg;
        } else {
            $err_msg = 0;
        }

        if($errs != 0) {
            $swift_errs = $swiftObj->createPlainMsg('additional pay form email error', 'bpitoni@ur.rochester.edu', 'bpitoni@ur.rochester.edu', '', $errs);
		 	$num_sent2 = $mailer->send($swift_errs, $failedRecipients);
        }    

		//MSG FOR SUBMITTER
		Utilities::msg_alert("Form submitted successfully.", "");
		Utilities::go_to("index.php");
		
	// ========================================================================================
	// DATA FAILED VALIDATION - ERROR OUT
	// ========================================================================================	
	} else {
		Utilities::err_alert("One or more form fields failed validation.", $err_msg);
	}

	
	}//end spam check
} catch (Exception $e) {
	echo $e->getMessage();
    echo "<br/>";
    exit;
}
?>  
