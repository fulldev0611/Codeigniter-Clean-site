<div class="col-xl-3 col-md-4">
<?php
	if($this->session->userdata('you_are_appling_as') == C_YOUARE_STAFF){
        $isEmployee = true;
        $memberTypeName = 'Staff';
        $navigationName = 'staff';
    }
 ?>
				<div class="panel-style">
		   		 <?php 
					$user=$this->db->where('id',$this->session->userdata('id'))->get('users')->row();
					if(!empty($user->profile_img)){
						$profile_img=$user->profile_img;
					}else{
						$profile_img="assets/img/user.jpg";
					}
		   		?>
				<div class="mb-4">
					<div class="d-sm-flex flex-row flex-wrap text-center text-sm-left align-items-center">
						<img alt="profile image"  src="<?php echo base_url().$profile_img; ?>"   class="avatar-lg rounded-circle">
						<div class="ml-sm-3 ml-md-0 ml-lg-3 mt-2 mt-sm-0 mt-md-2 mt-lg-0 info-blk-style">
							<h6 class="mb-0"><?=$user->name.(!is_null($user->l_name)?" ".$user->l_name:"")?></h6>
							<p class="text-muted mb-0">Member Since <?php echo date('M Y',strtotime($user->created_at));?></p>
						</div>
					</div>
				</div>
				<div class="widget settings-menu">
					<ul role="tablist" class="nav nav-tabs">	
						<li class="nav-item current">
							<a href="<?php echo base_url()?><?php echo $navigationName?>-dashboard" class="nav-link <?= ($this->uri->segment(1)==$navigationName."-dashboard"||$this->uri->segment(1)==$navigationName."-settings")?'active':'';?>">
								<i class="fa fa-line-chart"></i>
								<span><?php echo $memberTypeName?> Dashboard</span>
							</a>
						</li>
						<li class="nav-item">
		                    <a href="<?php echo base_url()?>staff-security" class="nav-link <?= ($this->uri->segment(1)=="staff-security")?'active':'';?>">
		                        <i class="fa fa-lock"></i>
		                        <span>Security</span>
		                    </a>
		                </li>
						<li class="nav-item current">
							<a href="<?php echo base_url()?><?php echo $navigationName?>-orders/view" class="nav-link <?= ($this->uri->segment(1)==$navigationName."-orders" && $this->uri->segment(2)=="view")?'active':'';?>">
								<i class="fa fa-calendar-o"></i>
								<span>View Orders</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="<?php echo base_url()?><?php echo $navigationName?>-orders/complete" class="nav-link <?= ($this->uri->segment(1)==$navigationName."-orders" && $this->uri->segment(2)=="complete")?'active':'';?>">
								<i class="fa fa-calendar-check-o"></i>
								<span>View Completed Orders</span>
							</a>
						</li>			
						<li class="nav-item">
							<a href="<?php echo base_url()?><?php echo $navigationName?>-contact-admin" class="nav-link <?= ($this->uri->segment(1)==$navigationName."-contact-admin")?'active':'';?>">
								<i class="fa fa-subway"></i>
								<span>Contact with SuperAdmin</span>
							</a>
						</li>					
						<li class="nav-item">
							<a href="<?php echo base_url()?><?php echo $navigationName?>-contact-user" class="nav-link <?= ($this->uri->segment(1)==$navigationName."-contact-user")?'active':'';?>">
								<i class="fa fa-group"></i>
								<span>Contact with customer</span>
							</a>
						</li>				
						<li class="nav-item">
							<a href="<?php echo base_url('', 'https')?><?php echo $navigationName?>-chat/avcall" 
									target='_blank' <?php echo $this->uri->segment(1) ?>
									class="nav-link <?= ($this->uri->segment(1)==$navigationName."-chat")?'active':'';?>">
								<i class="fa fa-tv"></i>
								<span>Video/Voice Chat</span>
							</a>
						</li>					
						<li class="nav-item">
							<a href="<?php echo base_url()?><?php echo $navigationName?>-wallet" class="nav-link <?= ($this->uri->segment(1)==$navigationName."-wallet")?'active':'';?>">
								<i class="fa fa-money"></i>
								<span>Wallet</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="<?php echo base_url()?>logout" class="nav-link <?= ($this->uri->segment(1)=="logout")?'active':'';?>">
								<i class="fa fa-sign-out"></i>
								<span>Logout</span>
							</a>
						</li> 
						
					</ul>
				</div>
			</div>
			</div>