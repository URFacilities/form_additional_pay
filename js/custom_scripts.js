	alert("in the included file");
		/* FORM: auto populate today's date */
		$(document).ready(function() {
			var now = new Date();
			var day = ("0" + now.getDate()).slice(-2);
			var month = ("0" + (now.getMonth() + 1)).slice(-2);		
			/*var today = now.getFullYear()+"-"+(month)+"-"+(day) ;*/
			var today = (month) + "-" + (day) + "-" + now.getFullYear();
			$('#date').val(today);
			
			/* Show or Hide Admin Section */
			alert($('#who').val());
			if($('#who').val() == 'admin') {
				$('#admin_detail').css("display", "inline-block"); 
				alert("who works");
			}
		});
		/* ================================================================================== 
	        FORM VALIDATOR HANDLES FORM SUBMIT 			
		/* ================================================================================ */
		/* FORM: validation */
		var validator = $("#addl_pay").validate({
			errorElement: "em",								/* So we can style it */
			errorPlacement: function(error, element) {		/* places error in error span */
				/* alert(element.parents("div").eq(0).find("span.error_msg").attr("id")); */
				element.parents("div").eq(0).find("span.error_msg").show();
				error.appendTo(element.parents("div").eq(0).find("span.error_msg"));
			},
			focusInvalid: true,
			/* debug: true, */		/* halts form submit to display errors */
			/* form rules */
			rules: {											
				other_reason: {
					required: '#reason_other:checked'
				}
			},
			 
			/* handler for invalid form */
			invalidHandler: function(event, validator) {
			    // 'this' refers to the form			
			    var errors = validator.numberOfInvalids();			
			    if (errors) {			
			      var message = errors == 1			
			        ? 'You missed 1 field. It has been highlighted'			
			        : 'You missed ' + errors + ' fields. They have been highlighted';			
			      $("#err").html(message);					     	
			      $("#err").show();			
			      $('html, body').animate({ scrollTop: 0 }, 'fast');
			    } else {			
			      $("#err").hide();			
			    }			
			},
			/* handler for valid form */
			submitHandler: function(form) {
				$("#err").hide();	
				form.submit();
			}
		});

		
		/* ================================================================================
	        DATEPICKER 			
		/* ================================================================================ */
		$(function() {			
		    $('input[name="date_range"]').daterangepicker({		
		        applyClass: 'btn-primary',		//makes button blue
        		cancelClass: 'btn-default',		
		        showDropdowns: true,
		        format: 'MM/DD/YYYY',			//strtotime php function expects slashes for american dates
                separator: ' to '
	
		    }/*, 
		
			/* Datepicker callback function
		    function(start, end, label) {		
		        var years = moment().diff(start, 'years');
		        alert("you are " + years + " old");
    		} */
    		); 
    		
    	/* ================================================================================== 
	        MAKE CHECKBOXES, RADIOS & SELECTS READONLY WHEN BIZ MGR IS APPROVING/DENYING REQUEST
		/* ================================================================================ */
		$('input[type=radio]').attr('disabled', false);
		if($('#who').val() == 'biz_mgr') {
			alert("in jquery who value");
			$(':checkbox[readonly]').click(function(){
	            return false;
	        });
			$(':checkbox[readonly]:not(:checked)').prop('disabled', true);
	        /* $(':radio[readonly]:not(:checked)').prop('disabled', true); */
	        $(':radio').click(function() { return false; } );
	       
	        $('option:not(:selected)').prop('disabled', true);
	    	
	  		});
	  	}
  		
  		
  		