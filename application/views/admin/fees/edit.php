<div class="page-wrapper">
	<div class="content container-fluid">
		<div class="container">
			<div class="row">
				<div class="col-xl-8 offset-xl-2">
					<h4 class="page-title m-b-20 m-t-0">Edit Fee</h4>
				</div>
			</div>
			<div class="row">
				<div class="col-xl-8 offset-xl-2">
					<div class="card">
						<div class="card-body">
							<form class="form-horizontal" id="edit_fee" method="POST" enctype="multipart/form-data">
								<div class="form-group">
									<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
									<input type="hidden" name="fee_id" value="<?=$feeAry['id']?>" />

									<label class=" control-label">Fee Name</label>
									<input type="text" class="form-control" name="fee_name" value="<?= isset($feeAry['name']) ? $feeAry['name'] : '' ?>">
								</div>
								<div class="form-group">
									<label class=" control-label">Type</label>
									<input type="number" class="form-control" name="fee_type" value="<?= isset($feeAry['type']) ? $feeAry['type'] : '' ?>">
								</div>
								<div class="form-group">
									<label class=" control-label">Value (%)</label>
									<input type="text" class="form-control" name="fee_value" placeholder="0.00" value="<?= isset($feeAry['fee']) ? $feeAry['fee'] : '' ?>">
								</div>
								
								<div class="mt-4">
									<?php if ($user_role == 1) { ?>
										<button class="btn btn-primary " name="form_submit" value="submit" type="submit">Submit</button>
									<?php } ?>
									<a href="<?php echo $base_url; ?>admin/fees" class="btn btn-link">Cancel</a>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {	
	var csrf_token=$('#admin_csrf').val();
	
    $('#edit_fee').bootstrapValidator({
        fields: {
          	fee_name: {
            	validators: {
            		remote: {
			            url: base_url + 'admin/fees/check_fee_name',
			            data: function(validator) {
			              return {
			                fee_name: validator.getFieldElements('fee_name').val(),
			                fee_id: validator.getFieldElements('fee_id').val(),
			                csrf_token_name:csrf_token
			              };
			            },
			            message: 'This fee name is already exist',
			            type: 'POST'
			        },
              		notEmpty: {
                		message: 'Please insert Fee Name'
              		}
            	}
          	},
          	fee_type: {
            	validators: {
            		remote: {
			            url: base_url + 'admin/fees/check_fee_type',
			            data: function(validator) {
			              return {
			                fee_type: validator.getFieldElements('fee_type').val(),
			                fee_id: validator.getFieldElements('fee_id').val(),
			                csrf_token_name: csrf_token
			              };
			            },
			            message: 'This fee type is already exist',
			            type: 'POST'
			        },
            		between: {
                        min: 1,
                        max: 100,
                        message: 'Fee Type must be between 1 and 100'
                    },
              		notEmpty: {
                		message: 'Please insert Fee Type'
              		}
            	}
          	},
          	fee_value: {
            	validators: {
            		between: {
                        min: 0,
                        max: 90,
                        message: 'Fee Value must be between 0.00 and 100.00'
                    },
              		notEmpty: {
                		message: 'Please insert Fee Value'
              		}
            	}
          	},
        }
    }).on('success.form.bv', function(e) {
        return true;
    });
});
</script>