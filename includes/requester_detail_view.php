<article id="requester_detail">			
				<h3>Requester</h3>
				<div class="form-group"><!-- creates row -->		
				    <label class="col-md-2 control-label" for="requester_fname">Requested By (FN):</label>			    
					<div class="pad_for_small col-md-4" >
						<input type="text" class="form-control" id="requester_fname" name="requester_fname" value="<?php echo $row['requester_fname']; ?>"  <?php echo $readonly; ?> placeholder="Your First Name" minlength="2" letterswithbasicpunc="true" required>
						<span class='error_msg'></span>
					</div>	
				    <label class="col-md-2 control-label" for="requester_lname">Requested By (LN):</label>
				    <div class="col-md-4" >
				    	<input type="text" class="form-control" id="requester_lname" name="requester_lname" value="<?php echo $row['requester_lname']; ?>"   <?php echo $readonly; ?> placeholder="Your Last Name" minlength="2" letterswithbasicpunc="true" required>
				    	<span class='error_msg'></span>
				    </div>			     				    
				</div><!-- end row -->
				
				<div class="form-group">	
					<label class="col-md-2 control-label" for="requester_phone">Phone Number:</label>
				    <div class="pad_for_small col-md-4">
				    	<input type="tel" class="form-control" id="requester_phone" name="requester_phone" value="<?php echo $row['requester_phone']; ?>" <?php echo $readonly; ?> placeholder="585-" phoneUS="true" data-mask="585-999-9999" required title="Please enter your 10 digit phone number with area code">	
				    	<span class='error_msg'></span>		    	
				    </div>		    
				    
				</div>
				<hr/>
</article>	
