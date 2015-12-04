			<article id="employee_detail">
				<h3>Employee</h3>
				<div class="form-group"><!-- creates row -->		
				    <label class="col-md-2 control-label" for="emp_fname">First Name:</label>			    
					<div class="pad_for_small col-md-4" >
						<input type="text" class="form-control" id="emp_fname" name="emp_fname" value="<?php echo $row['employee_fname']; ?>"  <?php echo $readonly; ?> placeholder="First Name" minlength="2" letterswithbasicpunc="true" required>
						<span class='error_msg'></span>
					</div>	
					
				    <label class="col-md-2 control-label" for="emp_lname">Last Name:</label>
				    <div class="col-md-4" >
				    	<input type="text" class="form-control" id="emp_lname" name="emp_lname" value="<?php echo $row['employee_lname']; ?>"   <?php echo $readonly; ?> placeholder="Last Name" minlength="2" letterswithbasicpunc="true" required>
				    	<span class='error_msg'></span>
				    </div>			     				    
				</div><!-- end row -->
				
				<div class="form-group">
					<label class="col-md-2 control-label" for="dept_name">Dept Name:</label>
				    <div class="pad_for_small col-md-4">
				    	<select class="form-control" id="dept_name" name="dept_name" required <?php echo $readonly; ?> onchange="$('#dept_name2').val(this.value);">
				    	  <option value="">-- Select One --</option>		
						  <?php 
						  try {
						    //queries mysql db for departments and creates dept dropdown
						    //requires fully qualified class name because this is an include file
						  	$depts_query = new Facilities\DataLayer\FormQueries;
							$depts_result = $depts_query->getDepts($conn);
							$dept_view = new Facilities\PresentationLayer\FormView;
						  	$dept_view->createDeptOptions( $depts_result, $row['dept_name'] ); 
						  } catch (PDOException $e) {
						  	echo $e->getMessage();
						  }
						  ?>
						</select>	
						<span class='error_msg'></span>		    	
				    </div>	
				    
					<label class="col-md-2 control-label" for="dept_phone">Dept Phone:</label>
				    <div class="col-md-4">
				    	<input type="tel" class="form-control" id="dept_phone" name="dept_phone" value="<?php echo $row['dept_phone']; ?>" <?php echo $readonly; ?> placeholder="585-" phoneUS="true" data-mask="585-999-9999" required title="Please enter the 10 digit phone number with area code">	
				    	<span class='error_msg'></span>		    	
				    </div>			    
			 	</div>
	
				<hr/>
			</article>