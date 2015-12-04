<?php
// ========================================================================================
// VERIFY POST
// ======================================================================================== 
$REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
if(!($REQUEST_METHOD=='POST')){ echo "no post<br/>"; exit(); }

// ========================================================================================
// INCLUDE REQUIRED LIBRARIES
// ========================================================================================    
use FacilitiesBusinessLayerUtilities as Utilities;
use FacilitiesDataLayerDBConnection as DBConnection;
use FacilitiesBusinessLayerSwiftMail as SwiftMail; 

$connector = new DBConnection;
	//$conn = $connector->set_connection('facil');
	//include_once('/www_vol/data/facil-data/dependencies/php_classes/FormQueries.php');

try {   
	// ======================================================================================= //
	// CHECK REQUIRED FIELD
	// ======================================================================================= //
	if(!isset($_POST['id'])) {
		throw new Exception('Error: Missing ID - Please go back and try again.');
	}

	// ======================================================================================= //
	// FILTER FORM DATA
	// ======================================================================================= //
	$reqID = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT); 
	$response = filter_input(INPUT_POST, 'response', FILTER_SANITIZE_STRING); 

	// ========================================================================================
	// SPAM CHECK
	// ========================================================================================
    if($_POST['permanganese'] == '' || !isset($_POST['permanganese'])) {
    	
		$pdo = $connector->set_connection('facil2_pdo');
		
		// ========================================================================================
		// CHECK: HAS ALREADY RESPONDED?
		// ========================================================================================  
		$sql1 = "SELECT date_of_response 
		FROM request 
		WHERE request_id = $reqID";
		$stmt1 = $pdo->query($sql1);		 
		$row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
		$date_of_response = $row1['date_of_response'];
		if(!IS_NULL($row['date_of_response'])) {
	    	throw new Exception("This request was already processed and the notices were sent out.");
	    }	

		// ========================================================================================
		// UPDATE RECORD IN DATABASE
		// ======================================================================================== 
		$sql2 = "UPDATE request SET `date_of_response` = CURDATE(), `response` = '$response' WHERE request_id = $reqID";
		$stmt2 = $pdo->query($sql2);
		if(!$stmt2) {
		    throw new Exception("Error getting statement handle - PDO::errorInfo(): " . print_r($pdo->errorInfo(),true));
		}
		$stmt2->execute();	
		if($stmt2->error) {
			throw new Exception($stmt2->errno . ' ' . $stmt2->error . '<br/>There was an error processing your response.  Please click the back button and try again.');
			exit;
		}	 
		$count = $stmt2->rowCount();
		
		// ========================================================================================
		// LOAD FORM VALUES FOR EMAIL
		// ======================================================================================== 
		$form_data = array();
		foreach($_POST as $key => $value) {
			$form_data[$key] = $value;					
		}
		
		// ========================================================================================
		// ADJUST VALUES FOR DATABASE INSERT
		// ======================================================================================== 
		//reasons
		$reasons = array();		//clear array
		$reasons = $_POST['reason'];
		
		//days
		$days = array();	//clear array
		$days_array = $_POST['work_days'];
		$work_days = '';			
		for($i=0; $i<count($days_array); $i++) {
			if($i == 0) { $work_days .= $days_array[$i]; } else { $work_days .= "-" . $days_array[$i]; }
		}		

		//split date range into two values (start, end)
		$dates_array = explode(' to ', $form_data['date_range']);
		$start_date = $dates_array[0];			 
		$end_date = $dates_array[1];		 	
		

	// ========================================================================================
	// CREATE AND SEND EMAIL
	// ======================================================================================== 	
	//CREATE EMAIL CONTENT
	include_once('./includes/email.php');
	$email_msg = $email_start . $email_body . $email_end;

	//SEND EMAIL TO ADMIN ASSISTANT USING SWIFT MAILER
    //Create the Transport
    $transport = Swift_SmtpTransport::newInstance('smtp-gw.rochester.edu');    
    //Create the Mailer
    $mailer = Swift_Mailer::newInstance($transport);    
    //Create Error Logger
    $logger = new Swift_Plugins_Loggers_ArrayLogger();
    $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));		
	$swiftObj = new SwiftMail();   
        
    // ====================================================================================
    // EMAIL TO ADMIN ASSISTANT 
    // ====================================================================================                  
    $subject = "Request for Temp " . ucfirst($response);
    $swift_super = $swiftObj->createHTMLMsg($subject , 'noreply@rochester.edu', 'colleen.williams@rochester.edu', '', $email_msg); 
	//$swift_super = $swiftObj->createHTMLMsg($subject , 'noreply@rochester.edu', 'bpitoni@ur.rochester.edu', '', $email_msg); 
	//so special characters (&nbsp;) do not come out all garbled (weird characters)
	$swift_super->setCharset('UTF-8');                
    $num_sent = $mailer->send($swift_super, $failedRecipients);     
    $errs = $swiftObj->getErrors($failedRecipients, $num_sent, $logger, $email_msg);
    if($errs != 0) {
    	$swift_errs = $swiftObj->createPlainMsg('temp hire response email error', 'noreply@rochester.edu', 'bpitoni@ur.rochester.edu', '', $errs);
		$num_sent2 = $mailer->send($swift_errs, $failedRecipients);
    }    

	//MSG FOR SUBMITTER
	Utilities::msg_alert("Form submitted successfully.", "");
	Utilities::go_to("index.php");
	
	}//end spam check
} catch (Exception $e) {
	echo $e->getMessage();
    echo "<br/>";
    exit;
}
?>  
