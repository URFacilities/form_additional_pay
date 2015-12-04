		<article id="admin_detail" style="display:none;">
			<h3>Administrative</h3>	
			
			<div class="form-group">
				<label class="col-md-2 control-label" for="emp_ID">Employee ID:</label>
			    <div class="pad_for_small col-md-4">
			    	<input type="text" class="form-control" id="emp_ID" name="emp_ID" value="<?php echo $row['emp_ID']; ?>" required>	
			    	<span class='error_msg'></span>    	
			    </div>	
			    <label class="col-md-2 control-label" for="emp_record_num">Employee Record #:</label>
			    <div class="col-md-4">
			    	<input type="text" class="form-control" id="emp_record_num" name="emp_record_num" value="<?php echo $row['emp_record_num']; ?>" required>	
			    	<span class='error_msg'></span>    	
			    </div>
			</div>		
			
			<div class="form-group">
				<label class="col-md-2 control-label" for="dept_name2">Dept Name:</label>
			    <div class="pad_for_small col-md-4">
			    	<input type="text" class="form-control" id="dept_name2" name="dept_name2" value="<?php echo $row['dept_name2']; ?>" readonly >	    	
			    </div>	
			    <label class="col-md-2 control-label" for="union_code">Union Code:</label>
			    <div class="col-md-4">
			    	<input type="text" class="form-control" id="union_code" name="union_code" value="<?php echo $row['union_code']; ?>" >	    	
			    </div>	
			</div>
			
			<div class="form-group">
				<label class="col-md-2 control-label" for="primary_job_code">Primary Job Code:</label>
			    <div class="pad_for_small col-md-4">
			    	<input type="text" class="form-control" id="primary_job_code" name="primary_job_code" value="<?php echo $row['primary_job_code']; ?>" required >
			    	<span class='error_msg'></span> 
			    </div>
			    <label class="col-md-2 control-label" for="current_pay_rate">Current Pay Rate:</label>
			    <div class="col-md-4">
			    	<div class="input-group">
      					<div class="input-group-addon">$</div>
			    		<input type="text" class="form-control" id="current_pay_rate" name="current_pay_rate" value="<?php echo $row['current_pay_rate']; ?>" placeholder="Dollars per Hour" number="true" required >				    		
			    		<span class='error_msg'></span>
			    		<div class="input-group-addon">per hour</div>				    									    		
			    	</div>    	
			    </div>			    
			</div>
			
			<div class="form-group">
				<label class="col-md-2 control-label" for="addl_work_job_code">Add'l Work Job Code:</label>
			    <div class="pad_for_small col-md-4">
			    	<input type="text" class="form-control" id="addl_work_job_code" name="addl_work_job_code" value="<?php echo $row['addl_work_job_code']; ?>" required >
			    	<span class='error_msg'></span> 
			    </div>
				<label class="col-md-2 control-label" for="addl_work_pay_rate">Add'l Work Rate:</label>
			    <div class="col-md-4" onfocusin="$('#tip').show();" onfocusout="$('#tip').hide();">
			    	<div class="input-group">
      					<div class="input-group-addon">$</div>
			    		<input type="text" class="form-control" id="addl_work_pay_rate" name="addl_work_pay_rate" value="<?php echo $row['addl_work_pay_rate']; ?>" placeholder="Dollars per Hour" number="true" required >				    		
			    		<span class='error_msg'></span>
			    		<div class="input-group-addon">per hour</div>				    									    		
			    	</div>
			    	
			    	<div id="tip" class="alert alert-info alert-dismissible" role="alert" style="display:none;">
					  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  Rate of pay for Additional Work.
					</div>   	
			    </div>								    	    
			</div>
			
			<div class="form-group">
				<label class="col-md-2 control-label" for="total_dollars">Total Dollars to Pay</label>
			    <div class="col-md-4">
			    	<div class="input-group">
      					<div class="input-group-addon">$</div>
			    		<input type="text" class="form-control" id="total_dollars" name="total_dollars" value="<?php echo $row['total_dollars']; ?>" number="true" required >				    		
			    		<span class='error_msg'></span>			    						    									    		
			    	</div>    	
			    </div>	
			    <label class="col-md-2 control-label" for="fao">FAO:</label>
			    <div class="col-md-4" id="fao_div">
			    	<input type="text" class="form-control" id="fao" name="fao" value="<?php echo $row['fao']; ?>" placeholder="Financial Activity Object" required >
			    	<span class='error_msg' id="rep_lname_span"></span>
			    </div>		
			</div>
		</article>