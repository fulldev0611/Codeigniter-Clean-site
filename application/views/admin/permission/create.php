<?php
   $module_details = $this->db->where('status',1)->get('admin_modules')->result_array();
?>
<div class="page-wrapper">
	<div class="content container-fluid">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<h4 class="page-title m-b-20 m-t-0">Create New Permission</h4> </div>
			</div>
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card-box">
						<form class="form-horizontal" id="add_data_form" action="<?php echo base_url('admin/'.$model.'/create'); ?>" method="POST" enctype="multipart/form-data">
							<div class="form-group">
								<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
							</div>
							
							<div class="form-group">
								<label class="col-3 control-label">Name</label>
								<div class="col-10">
									<input type="text" class="form-control" name="title" id="title" value="" required> 
								</div>
							</div>
							<div class="form-group">
								<label class="col-3 control-label">Applying As</label>
								<select required="true" class="form-control col-10" name="user_type" id="are_appling_as">
									<option value="">Select User Role</option>
									<?php 
									$applyingAs = C_APPLINGAS;
									asort($applyingAs, SORT_STRING);
									foreach($applyingAs as $key=>$value) {
										$select = "";
										if (is_array($registerInput) && isset($registerInput['you_are_appling_as'])) {
											if ($key==$registerInput['you_are_appling_as']) {$select='selected';}
										}
										?>
										<option <?=$select?> value="
											<?=$key?>">
												<?=$value?>
										</option>
										<?php
										}
									?>
								</select>
							</div>
							<div class="form-group">
								<label class="col-3 control-label">Description</label>
								<div class="col-10">
									<input type="text" class="form-control" name="description" id="description" value="" required> 
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-3 control-label">Status</label>
								<div class="col-10">
									<div class="radio radio-primary radio-inline">
										<input type="radio" id="academy_status1" value="1" name="status" checked="">
										<label for="academy_status1">Active</label>
									</div>
									<div class="radio radio-danger radio-inline">
										<input type="radio" id="academy_status2" value="0" name="status">
										<label for="academy_status2">Inactive</label>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label>Set Access</label>
								<div class="example1">
									<div><input type="checkbox" name="selectall1" id="selectall1" class="all" value="1"> <label for="selectall1"><strong>Select all</strong></label></div>
									<?php foreach ($module_details as $module) {
										$checkcondition  = "";
										if(!empty($user['user_id'])){
											$access_result = $this->db->where('admin_id',$user['user_id'])->where('module_id',$module['id'])->where('access',1)->select('id')->get('admin_access')->result_array();
											if(!empty($access_result)){
												$checkcondition  = "checked='checked'";
											}
										}
									?>
									<div><input type="checkbox" <?php echo $checkcondition; ?> name="accesscheck[]" id="check<?php echo $module['id'];?>" value="<?php echo $module['id'];?>"> <label for="check1"><?php echo $module['module_name'];?></label></div>
									<?php } ?>									
								</div>
							</div>

							<div class="m-t-30 text-center">
								<button name="form_submit" type="submit" class="btn btn-primary center-block" value="true">Save Changes</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" href="<?php echo base_url();?>assets/css/admin/<?=$model?>/create.css">
<script type="text/javascript">
    var categories = <?=json_encode($categories)?>;
</script>
<script src="<?php echo base_url(); ?>assets/js/admin/<?=$model?>/create.js"></script>