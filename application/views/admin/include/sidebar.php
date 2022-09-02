<?php
    $page = $this->uri->segment(1);
    $active =$this->uri->segment(2);
	$access_result_data_array = $this->session->userdata('access_module');	
	$admin_id=$this->session->userdata('admin_id');
	//echo "<pre>";print_r($this->session->userdata('admin_id'));exit;
	$website_logo_front ='assets/img/'.TEMPLATE_THEME.'/logo.png';
 ?>
 <div class="sidebar" id="sidebar">
	<div class="sidebar-logo">
		<a href="<?php echo $base_url; ?>dashboard">
			<img src="<?php echo $base_url.$website_logo_front; ?>" class="img-fluid" alt="">
		</a>
	</div>
	<div class="sidebar-inner slimscroll">
		<div id="sidebar-menu" class="sidebar-menu">
			<ul>
				<li class="<?php echo ($page == 'dashboard')?'active':'';?>">
					<a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-columns"></i> <span>Dashboard</span></a>
				</li>
				<?php if(in_array(1,$access_result_data_array)) { ?>
				<li class="<?php echo ($page == 'adminusers')?'active':''; echo ($page == 'edit_adminuser')?'active':''; echo ($page == 'adminuser_details')?'active':'';?>" >
					<a href="<?php echo $base_url; ?>adminusers"><i class="fa fa-user"></i> <span>Staffs</span></a>
				</li>
                <?php }if(in_array(2,$access_result_data_array)) { ?>                
				<li class="<?php echo ($page == 'categories')?'active':''; echo ($page == 'add-category')?'active':''; echo ($page == 'edit-category')?'active':'';?>">
					<a href="<?php echo $base_url; ?>categories"><i class="fa fa-layer-group"></i> <span>Categories</span></a>
				</li>
				<?php }if(in_array(3,$access_result_data_array)) { ?>
				<li class="<?php echo ($page == 'subcategories')?'active':''; echo ($page == 'add-subcategory')?'active':''; echo ($page == 'edit-subcategory')?'active':'';?>">
					<a href="<?php echo $base_url; ?>subcategories"><i class="fab fa-buffer"></i> <span>Sub Categories</span></a>
				</li>
				<?php }if(in_array(4,$access_result_data_array)) { ?>
				<li class="<?php echo ($page == 'service-list')?'active':''; echo ($page == 'service-details')?'active':''; ?>">
					<a href="<?php echo $base_url; ?>service-list"><i class="fa fa-bullhorn"></i> <span> Services</span></a>
				</li>
				<li class="<?php echo ($page == 'job-view')?'active':''; echo ($page == 'job-detail')?'active':''; ?>">
					<a href="<?php echo $base_url; ?>job-view"><i class="fa fa-user"></i> <span> Job Match</span></a>
				</li>
				<!--    -->		
				<li class="<?php echo ($page == 'job-post-admin')?'active':'';  ?>">
					<a href="<?php echo $base_url; ?>job-post-admin"><i class="fa fa-user"></i> <span> Job Post</span></a>
				</li>


				<?php }if(in_array(5,$access_result_data_array)) { ?>
				<li class="<?php echo ($active =='total-report' || $active =='pending-report' || $active == 'inprogress-report' || $active == 'complete-report' || $active == 'reject-report' || $active == 'cancel-report' ||$active == 'reject-payment/(:num)')? 'active':''; ?>">
					<a href="<?php echo $base_url; ?>admin/total-report"><i class="fa fa-calendar-check"></i> <span> Manage Booking</span></a>
				</li>
				<?php if($admin_id==1){ ?>						
				<li class="<?php echo ($page == 'admin' && $active == 'settings')?'active':'';?>">
				<a href="<?php echo $base_url; ?>admin/settings"><i class="fa fa-layer-group"></i> <span>Genaral Settings</span></a>
				</li> 
				<?php } ?>
				<?php }if(in_array(6,$access_result_data_array)) { ?>
				<li class="<?php echo ($page == 'payment_list')?'active':''; echo ($page == 'admin-payment')?'active':'';?>">
					<a href="<?php echo $base_url; ?>payment_list"><i class="fa fa-hashtag"></i> <span>Payments</span></a>
				</li>
				<?php }if($admin_id==1 || in_array(16,$access_result_data_array)){ ?>
				<li class="<?php echo ($page == 'admin' && $active == 'contact')?'active':''; ?>">
					<a href="<?php echo $base_url; ?>admin/contact"><i class="fa fa-layer-group"></i> <span>Enquiries</span></a>
				</li>
				<?php } ?>
				<?php if(in_array(7,$access_result_data_array)) { ?>
				<li class="<?php echo ($page == 'ratingstype')?'active':''; echo ($page == 'add-ratingstype')?'active':''; echo ($page == 'edit-ratingstype')?'active':'';?>">
					 <a href="<?php echo $base_url; ?>ratingstype"><i class="fa fa-star-half-alt"></i> <span>Rating Type</span></a>
				</li> 
				<?php }if(in_array(8,$access_result_data_array)) { ?>
				<li class="<?php echo ($page == 'review-reports')?'active':''; echo ($page == 'add-review-reports')?'active':''; echo ($page == 'edit-review-reports')?'active':'';?>">
					 <a href="<?php echo $base_url; ?>review-reports"><i class="fa fa-star"></i> <span>User Ratings</span></a>
				</li>
				<?php }if(in_array(9,$access_result_data_array)) { ?>				
				<li class="<?php echo ($page == 'subscriptions')?'active':''; echo ($page == 'add-subscription')?'active':''; echo ($page == 'edit-subscription')?'active':'';?>">
					<a href="<?php echo $base_url; ?>subscriptions"><i class="fa fa-calendar"></i> <span>Manage Subscriptions</span></a>
				</li>
				<?php }if(in_array(10,$access_result_data_array)) { ?>
				<li class="<?php echo ($active =='wallet' || $active =='wallet-history')? 'active':''; ?>">
					 <a href="<?php echo $base_url; ?>admin/wallet"><i class="fa fa-wallet"></i> <span> Wallet</span></a>
				</li>
				<?php }if(in_array(12,$access_result_data_array)) { ?>
				<li class="<?php echo ($page == 'service-providers')?'active':'';?>" >
					<a href="<?php echo $base_url; ?>service-providers"><i class="fa fa-user-tie"></i> <span> Service Providers</span></a>
				</li>
				<?php }if(in_array(11,$access_result_data_array)) { ?>
				<li class="<?php echo ($page == 'Revenue')?'active':'';?>" >
					<a href="<?php echo $base_url; ?>Revenue"><i class="fa fa-percent"></i> <span>Profit</span></a>
				</li>
                                
                                <!--Add Multiple Languages-->
                                
                                <!-- <li class="<?php echo ($page == 'language')?'active':'';?>" >
					<a href="<?php echo $base_url; ?>language"><i class="fa fa-flag"></i> <span>Language</span></a>
				</li> -->
				<!-- <li class="<?php echo ($page == 'country_code_config')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/country_code_config"><i class="fa fa-columns"></i> <span>Country Code</span></a>
				</li> -->
				<?php }if(in_array(13,$access_result_data_array)) { ?>
				<li class="<?php echo ($page == 'users')?'active':'';?>" >
					<a href="<?php echo $base_url; ?>users"><i class="fa fa-user"></i> <span>Users</span></a>
				</li>

				<?php }if(in_array(13,$access_result_data_array)) { ?>
				<li class="<?php echo ($active == 'userpermission')?'active':'';?>" >
					<a href="<?php echo $base_url; ?>admin/userpermission"><i class="fa fa-user"></i><span>User Permission</span></a>
				</li>

				
				<?php }if(in_array(13,$access_result_data_array)) { ?>
				<li class="<?php echo ($active == 'permission')?'active':'';?>" >
					<a href="<?php echo $base_url; ?>admin/permission"><i class="fa fa-user"></i><span> Permission Management</span></a>
				</li>

				<?php }if(in_array(13,$access_result_data_array)) { ?>
				<li class="<?php echo ($active == 'career')?'active':'';?>" >
					<a href="<?php echo $base_url; ?>admin/career"><i class="fa fa-user"></i><span> Career Management</span></a>
				</li>

				
				<?php }if(in_array(14,$access_result_data_array)) { ?>
				<!-- <li class="<?php echo ($active == 'settings' || $active =='emailsettings' || $active =='stripe_payment_gateway')? 'active':''; ?>">
					<a href="<?php echo $base_url; ?>admin/settings"><i class="fa fa-cog"></i> <span> Settings</span></a>
				</li>  -->
<?php }if(in_array(15,$access_result_data_array)) { ?>
				<li class="<?php echo ($active == 'emailtemplate' || $active =='edit-emailtemplate')? 'active':''; ?>">
					<a href="<?php echo $base_url; ?>admin/emailtemplate"><i class="fa fa-envelope"></i> <span> Email Templates</span></a>
				</li>
				<?php }?>	

		<?php if($admin_id==1){ ?>		
	
				<li class="<?php echo ($page == 'admin' && $active == 'custom_review')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/custom_review"><i class="fa fa-columns"></i> <span>Custom Review</span></a>
				</li>
 				<!-- <li class="<?php echo ($page == 'admin' && $active == 'footer_menu')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/footer_menu"><i class="fa fa-columns"></i> <span>Footer Menu</span></a>
				</li>
                                
				<li class="<?php echo ($page == 'admin' && $active == 'footer_submenu')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/footer_submenu"><i class="fa fa-layer-group"></i> <span>Footer Sub menu</span></a>
				</li>  -->

				<li class="<?php echo ($page == 'admin' && $active == 'faq')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/faq"><i class="fa fa-layer-group"></i> <span>faq</span></a>
				</li> 
				
				<li class="<?php echo ($page == 'employee_form')?'active':'';?>">
					<a href="<?php echo $base_url; ?>employee_form"><i class="fa fa-layer-group"></i> <span>Employee List</span></a>
				</li> 

				</li> 
				<li class="<?php echo ($page == 'user-subscription')?'active':'';?>">
					<a href="<?php echo $base_url; ?>user-subscription"><i class="fa fa-layer-group"></i> <span>Email Subscriptions</span></a>
				</li> 

				<li class="<?php echo ($page == 'user-login-history')?'active':'';?>">
					<a href="<?php echo $base_url; ?>user-login-history"><i class="fa fa-history"></i> <span>User Login History</span></a>
				</li>
				<li class="<?php echo ($page == 'user-chat-history')?'active':'';?>">
					<a href="<?php echo $base_url; ?>user-chat-history"><i class="fa fa-history"></i> <span>User Chat History</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'get_price')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/get_price"><i class="fa fa-layer-group"></i> <span>Guest User Data Store</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'blog')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/blog"><i class="fa fa-layer-group"></i> <span>Blog</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'customers_compliment')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/customers_compliment"><i class="fa fa-layer-group"></i> <span>Customers Compliment</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'how_to_work')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/how_to_work"><i class="fa fa-layer-group"></i> <span>How It Works</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'reason_to_love')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/reason_to_love"><i class="fa fa-layer-group"></i> <span>Reason To Love</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'faqs')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/faqs"><i class="fa fa-layer-group"></i> <span>Faqs</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'coupons')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/coupons"><i class="fa fa-layer-group"></i> <span>Coupons</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'why_choose')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/why_choose"><i class="fa fa-layer-group"></i> <span>Why Choose</span></a>
				</li>
				<!-- added by MaksimU -->
				<li class="<?php echo ($page == 'admin' && $active == 'fees')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/fees"><i class="fa fa-leaf"></i> <span>Fees</span></a>
				</li>
				<!-- added by Vadim -->
				<li class="<?php echo ($page == 'admin' && $active == 'commission')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/commission"><i class="fa fa-leaf"></i> <span>Commission</span></a>
				</li>

				<li class="<?php echo ($page == 'admin' && $active == 'requestQuote')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/requestQuote"><i class="fa fa-leaf"></i> <span>Request Quota</span></a>
				</li>


				<li class="<?php echo ($page == 'admin' && $active == 'transaction')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/transaction"><i class="fa fa-leaf"></i> <span>Transaction</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'affiliation')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/affiliation"><i class="fa fa-leaf"></i> <span>Affiliation Referral</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'promo_code')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/promo_code"><i class="fa fa-comment-dollar"></i> <span>Promo Codes</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'priority')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/priority"><i class="fa fa-leaf"></i> <span>Priority Services Fee</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'insertion')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/insertion"><i class="fa fa-leaf"></i> <span>Insertion Services Fee</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'gift')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/gift"><i class="fa fa-gift"></i> <span>Gifts & Points</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'loyalty')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/loyalty"><i class="fa fa-leaf"></i> <span>Loyalty Points</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'leads')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/leads"><i class="fa fa-leaf"></i> <span>Leads Plan</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'ads')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/ads"><i class="fa fa-laptop"></i> <span>Advertisement</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'skills')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/skills"><i class="fa fa-life-ring"></i> <span>Skills</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'schedule_shift')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/schedule_shift"><i class="fa fa-user-circle"></i> <span>Employee Schedule</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'partner_department')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/partner_department"><i class="fa fa-layer-group"></i> <span>Partner Department</span></a>
				</li>
				<li class="<?php echo ($page == 'admin' && $active == 'partner_categories')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/partner_categories"><i class="fa fa-layer-group"></i> <span>Partner Categories</span></a>
				</li>		
				<li class="<?php echo ($page == 'admin' && $active == 'whitelabel')?'active':'';?>">
					<a href="<?php echo $base_url; ?>admin/whitelabel"><i class="fa fa-layer-group"></i> <span>Whitelabel</span></a>
				</li>	
				<!-- end by MaksimU -->			
		<?php } ?>
			</ul>
		</div>
	</div>
</div>

<script type="text/javascript">

</script>
