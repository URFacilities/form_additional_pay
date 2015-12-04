		<article id="work_detail">
			<h3>Work Performed</h3>
			<?php
				$addl_work_checked = '';
				$union_checked = '';
				$out_title_checked = '';
				$trng_pay_checked = '';
				$other_checked = '';
				if(!empty($row)) {					
					$reason = $row['reason_for_payment'];	
					if($reason != 'Additional Work' && $reason != 'Union Contract' && $reason != 'Out of Title' && $reason != 'Training Pay') {
						$other_checked = 'checked';
						$other_reason = $reason;
					}
					if($reason == 'Additional Work') {
						$addl_work_checked = 'checked';
					}
					if($reason == 'Union Contract') {
						$union_checked = 'checked';
					}
					if($reason == 'Out of Title') {
						$out_title_checked = 'checked';
					}
					if($reason == 'Training Pay') {
						$trng_pay_checked = 'checked';
					}
				
				}
			?>
			<div class="form-group required">
				<label class="col-md-2 control-label" for="reason">Reason for Payment:</label>
				<div id="reason_div" class="pad_for_small col-md-10">
					<label class="radio-inline" id="radio_label">
					  <!-- when input is an array, you only need to add 'required' to the first input -->
					  <input type="radio" name="reason" id="reason_addl_work" value="Additional Work" <?php echo $addl_work_checked . " " . $readonly; ?> required title="You must enter a reason for the addional pay."> Additional work performed Outside Home Department or Inside Home Department in a Different Classification
					</label><br/>
					<label class="radio-inline">
					  <input type="radio" name="reason" id="reason_union" value="Union Contract" <?php echo $union_checked . " " . $readonly; ?>> Union Contract Language
					</label>
					<label class="radio-inline">
					  <input type="radio" name="reason" id="reason_out_title" value="Out of Title" <?php echo $out_title_checked . " " . $readonly; ?>> Out of Title
					</label>
					<label class="radio-inline">
					  <input type="radio" name="reason" id="reason_trng_pay" value="Training Pay" <?php echo $trng_pay_checked . " " . $readonly; ?>> Training Pay
					</label>
					<label class="radio-inline">
					  <input type="radio" name="reason" id="reason_other" value="Other" <?php echo $other_checked . " " . $readonly; ?>> Other*
					</label>
					
					<span class='error_msg' id="reason_span"><br/></span>
					<p class="help-block">*If the reason is 'other', please enter the reason in the space below.</p>
				</div>					
		 	</div>
		 	
			<div class="form-group">
			    <label class="col-md-2 control-label" for="other_reason">Other Reason:</label>
			    <div class="pad_for_small col-md-4">
			    	<input type="text" class="form-control" id="other_reason" name="other_reason" value="<?php echo $other_reason; ?>" <?php echo $readonly; ?> placeholder="If checked above, enter other reason" minlength="2" title="You must enter a reason if you checked the 'other' box above.">	
			    	<span class='error_msg'></span>		    	
			    </div>	
			    <!-- <label class="col-md-2 control-label" for=""></label>
			    <div class="col-md-4" id="">
			    	<input type="text" class="form-control" id="" name="" placeholder="" >
			    </div>	-->				    
			</div>

			<?php
			 	if(!empty($row)) {
			 		$start_date = Facilities\BusinessLayer\Utilities::convertDate($row['work_start_date']);
					$end_date = Facilities\BusinessLayer\Utilities::convertDate($row['work_end_date']);
					$date_range = $start_date . " to " . $end_date;
				}
			 ?>
			<div class="form-group">
			    <label class="col-md-2 control-label" for="date_range">Dates of Add'l Work:</label>
			    <div class="pad_for_small col-md-4">
			    	<input type="text" class="form-control" id="date_range" name="date_range" value="<?php echo $date_range; ?>" <?php echo $readonly; ?> placeholder="Enter start and end date." required>	
			    	<span class='error_msg'></span>		    	
			    </div>	
			    <label class="col-md-2 control-label" for="job_title">Job Title:</label>
			    <div class="col-md-4">
			    	<input type="text" class="form-control" id="job_title" name="job_title" value="<?php echo $row['job_title']; ?>" placeholder="Job title for additional work." required>
			    	<span class='error_msg'></span>
			    </div>		    	
		 	</div>
		 	
		 	<div class="form-group">
			    <label class="col-md-2 control-label" for="work_description">Description of Work:</label>
			    <div class="col-md-10">
			    	<textarea class="form-control" rows="3" id="work_description" name="work_description" <?php echo $readonly; ?> placeholder="Describe extra/additional work performed." required><?php echo $row['description_of_work']; ?></textarea>			    	
			    	<span class='error_msg'></span>
			    </div>			    
			</div>
			
			<div class="form-group">
			    <label class="col-md-2 control-label" for="work_class">Work Classification:</label>
			    <div class="col-md-10">
			    	<input type="text" class="form-control" id="work_class" name="work_class" value="<?php echo $row['work_classification']; ?>" <?php echo $readonly; ?> placeholder="Enter classification if different from regular/primary classification." >			    	
			    	<span class='error_msg'></span>
			    </div>			    
			</div>
		 	
		 	<hr/>
			</article>