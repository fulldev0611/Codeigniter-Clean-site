<?php
    $type = $this->session->userdata('usertype');
    $userId = $this->session->userdata('id');
?>

<?php
    if ($this->session->userdata('user_select_language') == '') {
        if ($default_language_select['tag'] == 'ltr' || $default_language_select['tag'] == '') {

        } elseif ($default_language_select['tag'] == 'rtl') {
            echo'<link href="' . base_url() . 'assets/css/bootstrap-rtl.min.css" media="screen" rel="stylesheet" type="text/css" />';
            echo'<link href="' . base_url() . 'assets/css/app-rtl.css" rel="stylesheet" />';
        }
    } else {
        if ($this->session->userdata('tag') == 'ltr' || $this->session->userdata('tag') == '') {
            
        } elseif ($this->session->userdata('tag') == 'rtl') {

            echo'<link href="' . base_url() . 'assets/css/bootstrap-rtl.min.css" media="screen" rel="stylesheet" type="text/css" />';
            echo'<link href="' . base_url() . 'assets/css/app-rtl.css" rel="stylesheet" />';
        }
    }

    $headerClass = "";
    if ($module == TEMPLATE_THEME && $page == "home") {
        $headerClass = "home";    
    } 
    else {
        $headerClass = "page";
    }

    if ($this->session->userdata('id')) { 
        if ($this->session->userdata('usertype') == 'user') {
            $user_details = $this->db->where('id', $this->session->userdata('id'))->get('users')->row_array();
        } elseif ($this->session->userdata('usertype') == 'provider') {
            $user_details = $this->db->where('id', $this->session->userdata('id'))->get('providers')->row_array();
        }
    }
    
?>

<div class="main-wrapper">
    <header class="header" style="background: transparent;">
        <nav class="navbar navbar-light" id="tazzer-top-navbar">
            <!-- <a class="navbar-brand" href="#"></a> -->
            <!-- Toggler/collapsibe Button -->
            <!-- <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapseTopNavbar">
                <span class="navbar-toggler-icon"></span>
            </button> -->
            <!-- <div class="collapse navbar-collapse" id="collapseTopNavbar"> -->
                <ul class="nav" id="top-info-navbar">
                    <li class="nav-item nav-info-item">
                        <a href="javascript:void(0);" class="nav-link text-item" style="text-decoration: none;">
                            WELCOME TO  
                            <!-- <b>TAZZER CLEAN & HANDYMAN SERVICES</b> -->
                            <b>TAZZER GROUP</b>
                        </a>
                    </li>
                    <li class="nav-item nav-info-item" style="margin-left: 10px;">
                      <a href="https://web.facebook.com/TazzerGroup" class="nav-link round-item" target="_blank"><i class="fa fa-facebook-official"></i></a>
                      <a href="https://www.linkedin.com/company/66900323/" target="_blank" class="nav-link round-item"><i class="fa fa-linkedin"></i></a>
                      <a href="https://twitter.com/CleanTazzer" target="_blank" class="nav-link round-item"><i class="fa fa-twitter"></i></a>
                      <a href="https://www.instagram.com/tazzer_group/" target="_blank" class="nav-link round-item"><i class="fa fa-instagram"></i></a>
                    </li>
                </ul>
            <!-- </div> -->
        </nav>
        <hr class="header-top-hr">
    </header>
    <!-- <hr class="header-top-hr"> -->
    <header class="header tazzer-second-header <?php echo $headerClass; ?>">
        <nav class="navbar header-nav">
            <div class="navbar-header">
                <a href="<?php echo base_url(); ?>" class="navbar-brand logo">
                    <img src="<?php echo base_url() . $this->website_logo_front; ?>" class="img-fluid" alt="Logo">
                </a>
            </div>
            <div class="main-menu-wrapper">
                <div class="menu-header">
                    <a href="<?php echo base_url(); ?>" class="menu-logo">
                        <img src="<?php echo base_url() . $this->website_logo_front; ?>" class="img-fluid" alt="Logo">
                    </a>
                    <a id="menu_close" class="menu-close" href="javascript:void(0);">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
                <ul class="main-nav">
                    
                </ul>        
            </div>       
            <ul class="nav header-navbar-rht">
                <li class="nav-item nav-info-item">
                    <a href="mailto:info@<?=str_replace("www.","",base_uri())?>" id='nav-phone-item' class="nav-link text-item" style="text-decoration: none;">
                        <div class="nav-image">
                            <img src="<?php echo base_url();?>assets/img/ic_mail_outline_24px.png">
                        </div>
                        <span class="text">
                            <text class="text-title">MAIL TO US:</text>
                            <br> 
                            <text class="text-content">info@<?=str_replace("www.","",base_uri())?></text>
                        </span>
                    </a>
                </li>
                <li class="nav-item nav-info-item">
                    <a href="tel:+44-079-6124-2587" class="nav-link text-item" style="text-decoration: none;">
                        <div class="nav-image">
                            <img src="<?php echo base_url();?>assets/img/ic_phone_in_talk_24px.png">
                        </div>
                        <span class="text">
                            <text class="text-title">CALL FOR HELP:</text>
                            <br> 
                            <text class="text-content">079 6124 2587</text>
                        </span>
                    </a>
                </li>
                <?php
                    if ($userId != '') {
                        $get_currency = get_currency();
                        $user_currency = get_user_currency();
                        $user_currency_code = $user_currency['user_currency_code'];
                    } 
                ?>
                <?php
                    $wallet = 0;
                    $token = '';
                    if ($this->session->userdata('id') != '') {
                        if (!empty($token = $this->session->userdata('chat_token'))) {
                            $wallet_sql = $this->db->select('*')->from('wallet_table')->where('token', $this->session->userdata('chat_token'))->get()->row();
                            if (!empty($wallet_sql)) {
                                $wallet = $wallet_sql->wallet_amt;
                                $user_currency_code = '';
                                If (!empty($userId)) {
                               
                                    $wallet = $wallet_sql->wallet_amt;
                                    $user_currency = get_user_currency();
                                    $user_currency_code = $user_currency['user_currency_code'];

                                    $wallet = get_gigs_currency($wallet_sql->wallet_amt, $wallet_sql->currency_code, $user_currency_code);
                                } else {
                                    $user_currency_code = settings('currency');
                                    $wallet = $wallet_sql->wallet_amt;
                                }
                            }
                        }

                        if ($this->session->userdata('usertype') == 'provider') {
                            ?>
                            <li class="nav-item desc-list wallet-menu">
                                <a href="<?= base_url() . 'provider-wallet' ?>" class="nav-link header-login">
                                    <img src="<?php echo $base_url ?>assets/img/wallet.png" alt="" class="mr-2 wallet-img"><span><?php echo (!empty($user_language[$user_selected]['lg_wallet'])) ? $user_language[$user_selected]['lg_wallet'] : $default_language['en']['lg_wallet']; ?>:</span> <?php echo currency_conversion($user_currency_code) . $wallet; ?>
                                </a>
                            </li>
                            <?php   
                        } 
                        else {
                            ?>
                            <li class="nav-item desc-list wallet-menu">
                                <a href="<?= base_url() . 'user-wallet' ?>" class="nav-link header-login">
                                    <img src="<?php echo $base_url ?>assets/img/wallet.png" alt="" class="mr-2 wallet-img"><span><?php echo (!empty($user_language[$user_selected]['lg_wallet'])) ? $user_language[$user_selected]['lg_wallet'] : $default_language['en']['lg_wallet']; ?>:</span> <?php echo currency_conversion($user_currency_code) . $wallet; ?>
                                </a>
                            </li>
                            <?php
                        }
                    }
                ?>
                
                <?php
                    if (($this->session->userdata('id') != '') && ($this->session->userdata('usertype') == 'provider')) {   ?>
                            <li class="nav-item">
                                <a href="javascript:;" class="nav-link header-login post-service" id="post-service"><i class="fa fa-plus-circle mr-1"></i> <span><?php echo (!empty($user_language[$user_selected]['lg_post_service'])) ? $user_language[$user_selected]['lg_post_service'] : $default_language['en']['lg_post_service']; ?></span></a>
                            </li>
                        <?php
                    }  
                ?>

                <?php 
                    if ($this->session->userdata('id') == '') { 

                        $loginUrl = "login";
                        $signupUrl = "join-us";
                        ?>

                    <!-- <li class="nav-item">
                        <a class="nav-link v-btn old-tazzer-button" href="javascript:void(0);"  data-toggle="modal" data-target="#myModal">
                            <?php // echo (!empty($user_language[$user_selected]['lg_become_user'])) ? $user_language[$user_selected]['lg_become_user'] : $default_language['en']['lg_become_user']; ?>
                        </a>
                    </li> -->
                    <!-- <li><a class="btn tazzer-button" href="javascript:void(0);"  data-toggle="modal" data-target="#modal-wizard">BECOME A SELLER</a></li> -->
                    <li class="nav-item">
                        <a class="nav-link v-btn header-login" href="<?=base_url().$loginUrl?>">
                            <img src="<?php echo base_url();?>assets/img/ic_person_white.png" class="mr-2"> Login
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link v-btn header-join" href="<?=base_url().$signupUrl?>">
                            <img src="<?php echo base_url();?>assets/img/ic_group_add.png" class="mr-2"> Join Us
                        </a>
                    </li>
                    <?php
                    } else {
                        if ($this->session->userdata('usertype') == 'provider') {
                    ?>
                    <!-- User Menu -->
                    <li class="nav-item dropdown has-arrow logged-item">
                        <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                            <span class="user-img">
                                <?php if ($user_details['profile_img'] != '') { ?>
                                    <img class="rounded-circle" src="<?php echo $base_url . $user_details['profile_img'] ?>" width="31" alt="">
                                <?php } else { ?>
                                    <img class="rounded-circle" src="<?php echo $base_url ?>assets/img/user.jpg" alt="">
                                <?php } ?>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="user-header">
                                <div class="avatar avatar-sm">
                                    <?php if ($user_details['profile_img'] != '') { ?>
                                        <img class="avatar-img rounded-circle" src="<?php echo $base_url . $user_details['profile_img'] ?>" alt="">
                                    <?php } else { ?>
                                        <img class="avatar-img rounded-circle" src="<?php echo $base_url ?>assets/img/user.jpg" alt="">
                                    <?php } ?>
                                </div>
                                <div class="user-text">
                                    <h6><?php echo $user_details['name']; ?></h6>
                                    <p class="text-muted mb-0">Provider</p>
                                </div>
                            </div>
                            <a class="dropdown-item" href="<?php echo base_url(); ?>provider-dashboard">Dashboard</a>
                            <a class="dropdown-item" href="<?php echo base_url(); ?>my-services">My Services</a>
                            <a class="dropdown-item" href="<?php echo base_url(); ?>provider-bookings">Booking List</a>
                            <a class="dropdown-item" href="<?php echo base_url(); ?>provider-settings">Profile Settings</a>
                            <a class="dropdown-item" href="<?php echo base_url(); ?>provider-wallet">Wallet</a>
                            <a class="dropdown-item" href="<?php echo base_url() ?>provider-subscription">Subscription</a>
                            <a class="dropdown-item" href="<?php echo base_url() ?>verify-payment-method">Payment Verify</a>
                            <a class="dropdown-item" href="<?php echo base_url() ?>provider-availability">Availability</a>
                            <?php 
                            $query = $this->db->query("select * from system_settings WHERE status = 1");
                            $result = $query->result_array();
                            
                            $login_type='';
                            foreach ($result as $res) {
                                
                                if($res['key'] == 'login_type'){
                                    $login_type = $res['value'];
                                }
                                
                                if($res['key'] == 'login_type'){
                                    $login_type = $res['value'];
                                }

                            }
                                if($login_type=='email'){
                                ?>
                            <a class="dropdown-item" href="<?php echo base_url() ?>provider-change-password">Change Password</a>
                            
                                <?php } ?>
                            <a class="dropdown-item" href="<?php echo base_url() ?>user-chat">Chat</a>
                            <a class="dropdown-item" href="<?php echo base_url() ?>logout">Logout</a>
                        </div>
                    </li>
                    <!-- /User Menu -->
                        <?php 
                    } elseif ($this->session->userdata('usertype') == 'user') { ?>
                    <li class="nav-item dropdown has-arrow logged-item">
                        <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                            <span class="user-img">
                                <?php if ($user_details['profile_img'] != '') { ?>
                                    <img class="rounded-circle" src="<?php echo $base_url . $user_details['profile_img'] ?>" alt="">
                                <?php } else { ?>
                                    <img class="rounded-circle" src="<?php echo $base_url ?>assets/img/user.jpg" alt="">
                                <?php } ?>
                            </span>
                        </a>
                        <?php  
                        # added by maksimU ================================================================================
                        $memberTypeName = "User";
                        $navigationName = "user";
                        $isEmployee = false;
                        if($this->session->userdata('employee_status')=='yes')
                        {
                            $isEmployee = true;
                            $memberTypeName = 'Employee';
                            $navigationName = 'employee';
                        }
                        elseif($this->session->userdata('soletrader_status')=='yes')
                        {
                            $isEmployee = true;
                            $memberTypeName = 'Sole Trader';
                            $navigationName = 'soletrader';
                        } elseif($this->session->userdata('self_employed_status')=='yes')
                        {
                            $isEmployee = true;
                            $memberTypeName = 'Self-Employed';
                            $navigationName = 'self-employed';
                        } else if($this->session->userdata('you_are_appling_as') == C_YOUARE_ORGANIZATION){
                            $isEmployee = true;
                            $memberTypeName = 'Organization';
                            $navigationName = 'organization';
                        } else if($this->session->userdata('you_are_appling_as') == C_YOUARE_STAFF){
                            $isEmployee = true;
                            $memberTypeName = 'Staff';
                            $navigationName = 'staff';
                        } else if($this->session->userdata('you_are_appling_as') == C_YOUARE_PARTNER){
                            $isEmployee = true;
                            $memberTypeName = 'Partner';
                            $navigationName = 'partner';
                        }
                        else if($this->session->userdata('you_are_appling_as') == C_YOUARE_BUSINESS){
                            $isEmployee = false;
                            $memberTypeName = 'Business';
                            $navigationName = 'business';
                        }
                        else if ($this->session->userdata('you_are_appling_as') == C_DELIVERY_SUPER_MARKET) {
                            $memberTypeName = 'Supermarket';
                        }
                        else if ($this->session->userdata('you_are_appling_as') == C_DELIVERY_RESTAURANT) {
                            $memberTypeName = 'Restaurant';
                        }
                        else if ($this->session->userdata('you_are_appling_as') == C_DELIVERY_PARCEL) {
                            $memberTypeName = 'Parcel Delivery';
                        }
                        else if ($this->session->userdata('you_are_appling_as') == C_DELIVERY_TRANSPORT) {
                            $memberTypeName = 'Transport Delivery';
                        }
                        else if ($this->session->userdata('you_are_appling_as') == C_DELIVERY_PERSONAL) {
                            $memberTypeName = 'Personal Delivery';
                        }
                        if($isEmployee)
                        {
                        ?>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="user-header">
                                <div class="avatar avatar-sm">
                                    <?php if ($user_details['profile_img'] != '') { ?>
                                        <img class="avatar-img rounded-circle" src="<?php echo $base_url . $user_details['profile_img'] ?>" alt="">
                                    <?php } else { ?>
                                        <img class="avatar-img rounded-circle" src="<?php echo $base_url ?>assets/img/user.jpg" alt="">
                                    <?php } ?>
                                </div>
                                <div class="user-text">
                                    <h6><?php echo $user_details['name'].(!is_null($user_details['l_name'])?" ".$user_details['l_name']:""); ?></h6>
                                    <p class="text-muted mb-0"><?php echo $memberTypeName ?></p>
                                </div>
                            </div>
                            <a class="dropdown-item" href="<?php echo base_url(); ?><?php echo $navigationName ?>-dashboard"><?php echo $memberTypeName ?> Dashboard</a>
                            <?php 
                                if($this->session->userdata('you_are_appling_as') != C_YOUARE_STAFF){
                            ?>
                                <a class="dropdown-item" href="<?php echo base_url(); ?><?php echo $navigationName ?>-my-services">My Services</a>
                            <?php } ?>
                            <a class="dropdown-item" href="<?php echo base_url(); ?><?php echo $navigationName ?>-settings">Profile Settings</a>
                            <a class="dropdown-item" href="<?php echo base_url(); ?><?php echo $navigationName ?>-orders/view">View Orders</a>
                            <a class="dropdown-item" href="<?php echo base_url(); ?><?php echo $navigationName ?>-orders/complete">View Completed Orders</a>
                            
                            <?php 
                            
                            $login_type='';
                            if (isset($settings['login_type'])) {
                                $login_type = $settings['login_type'];
                            }
                            if($login_type=='email'){
                                ?>
                            <a class="dropdown-item" href="<?php echo base_url() ?>change-password">Change Password</a>
                            
                                <?php } ?>
                            <a class="dropdown-item" href="<?php echo base_url() ?><?php echo $navigationName ?>-wallet">Payment<!-- History--></a>
                            <a class="dropdown-item" href="<?php echo base_url() ?><?php echo $navigationName ?>-chat">Chat</a>
                            <a class="dropdown-item" href="<?php echo base_url() ?>logout">Logout</a>
                        </div>
                        <?php
                        }
                        else
                        {
                        # end ======================================================================================================
                        ?>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="user-header">
                                <div class="avatar avatar-sm">
                                    <?php if ($user_details['profile_img'] != '') { ?>
                                        <img class="avatar-img rounded-circle" src="<?php echo $base_url . $user_details['profile_img'] ?>" alt="">
                                    <?php } else { ?>
                                        <img class="avatar-img rounded-circle" src="<?php echo $base_url ?>assets/img/user.jpg" alt="">
                                    <?php } ?>
                                </div>
                                <div class="user-text">
                                    <h6><?php echo $user_details['name'].(!is_null($user_details['l_name'])?" ".$user_details['l_name']:""); ?></h6>
                                    <p class="text-muted mb-0"><?=$memberTypeName?></p>
                                </div>
                            </div>
                            <a class="dropdown-item" href="<?php echo base_url(); ?>user-dashboard">Dashboard</a>
                            <a class="dropdown-item" href="<?php echo base_url(); ?>user-bookings">My Bookings</a>
                            <a class="dropdown-item" href="<?php echo base_url(); ?>user-settings">Profile Settings</a>
                            <a class="dropdown-item" href="<?php echo base_url() ?>all-services">Book Services</a>
                            
                            <?php 
                            $query = $this->db->query("select * from system_settings WHERE status = 1");
                            $result = $query->result_array();
                            
                            $login_type='';
                            foreach ($result as $res) {
                                
                                if($res['key'] == 'login_type'){
                                    $login_type = $res['value'];
                                }
                                
                                if($res['key'] == 'login_type'){
                                    $login_type = $res['value'];
                                }

                            }
                                if($login_type=='email'){
                                ?>
                            <a class="dropdown-item" href="<?php echo base_url() ?>change-password">Change Password</a>
                            
                                <?php } ?>
                            <a class="dropdown-item" href="<?php echo base_url() ?>verify-payment-method">Payment Verify</a>
                            <a class="dropdown-item" href="<?php echo base_url() ?>user-chat">Chat</a>
                            <a class="dropdown-item" href="<?php echo base_url() ?>logout">Logout</a>
                        </div>
                    <?php
                        } 
                        ?>
                    </li>
                    <?php
                    }   
                }    
                ?>
            </ul>
        </nav>
    </header>
    <header class="header sticktop tazzer-third-header <?php echo $headerClass; ?>">
        <nav class="navbar navbar-expand-md header-nav">
            <div class="navbar-header">
                <a id="mobile_btn" href="javascript:void(0);">
                    <span class="bar-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </a>
            </div>
            <div class="main-menu-wrapper">
                <div class="menu-header">
                    <a href="<?php echo base_url(); ?>" class="menu-logo">
                        <img src="<?php echo base_url() . $this->website_logo_front; ?>" class="img-fluid" alt="Logo">
                    </a>
                    <a id="menu_close" class="menu-close" href="javascript:void(0);">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
                <ul class="main-nav">
                    <?php 
                        if (ENVIRONMENT != "production") {
                            ?>
                    <li><a href="<?php echo base_url(); ?>">HOME</a></li>
                            <?php
                        }
                    ?>
                    <!-- <li><a href="<?php echo base_url()."service"; ?>">SERVICE</a></li> -->
                  
                    <li class="has-submenu">
                        <?php  
                        // Leo: bug fix -- please don't use home ----
                        // $result = $this->home->get_category();
                        $result = getCategoryList();  
                        // --------------------------------------------
                        ?>
                        <a href="<?php echo base_url(); ?>services">SERVICE <i class="fa fa-chevron-down"></i></a>
                        <ul class="submenu">
                            <?php 
                                foreach ($result as $res) {
                                    $categoryName = str_replace([" and "," And "]," & ",trim($res['category_name']));
                                    ?>
                                <li><a href="<?php echo base_url(); ?>service-details/<?=$res['id']?>/<?=replace_specials(strtolower(trim($res['category_name'])))?>"><?php echo ucfirst($categoryName); ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>


                    <?php 
                        if (ENVIRONMENT == "production") {

                            
                        } else if (ENVIRONMENT == "testing") {
                            ?>
                    <li><a href="https://deliveries.azurewebsites.net/">DELIVERY</a></li>
                    <li><a href="https://tazzershop.azurewebsites.net/">SHOP</a></li>
                    <li><a href="https://easyworksdirect.azurewebsites.net/">FREELANCER</a></li>
                            <?php
                        } 
                        else {
                            ?>
                    <li><a href="https://local.deliveries/">DELIVERY</a></li>
                    <li><a href="https://tazzershop.azurewebsites.net/">SHOP</a></li>
                    <li><a href="https://local.easyworksdirect/">FREELANCER</a></li>
                            <?php
                        }
                    ?>
                    
                    <!--
                    <li><a href="<?php echo base_url(); ?>blog">BLOG</a></li>

                    <?php
                        $usertype = $this->session->userdata('you_are_appling_as');
                            if ($usertype == 8 || $usertype== 11) {
                    ?>
                        <li><a href="<?php echo base_url().'employee/time-clock'; ?>">TIME CLOCK</a></li>
                    <?php } ?>

                    <li><a href="<?php echo base_url(); ?>about-us"><?php echo (!empty($user_language[$user_selected]['lg_about'])) ? $user_language[$user_selected]['lg_about'] : $default_language['en']['lg_about']; ?></a></li>
                    <?php
                    $usertype = $this->session->userdata('usertype');
                    if ($usertype != 'provider') {
                        ?>    
                        <li><a href="<?php echo base_url(); ?>contact"><?php echo (!empty($user_language[$user_selected]['lg_contact'])) ? $user_language[$user_selected]['lg_contact'] : $default_language['en']['lg_contact']; ?></a></li>
                    <?php } ?>

                    <?php
                    $usertype = $this->session->userdata('usertype');
                    $applyingAs = $this->session->userdata('you_are_appling_as');
                    $vendorArr = [
                        C_YOUARE_SOLETRADER,
                        C_YOUARE_EMPLOYEE,
                        C_YOUARE_ORGANIZATION,
                        C_YOUARE_SELF_EMPLOYED
                    ];
                    if ($usertype == 'user' && !in_array($applyingAs, $vendorArr)) {
                        ?>    
                         <li><a href="<?php echo base_url(); ?>service-checkout">CHECKOUT</a></li> 
                    <?php } ?>
                    
                    <li><a href="<?php echo base_url(); ?>career">CAREER</a></li>
                    <?php 
                        if ($usertype == 'provider') {
                            ?>
                    <li><a href="<?php echo base_url(); ?>job/jobpost">POST A JOB</a></li>
                            <?php
                        }
                    ?>
                    <li><a href="<?php echo base_url(); ?>career/meeting">BOOK MEETING</a></li>
                    -->
                </ul>
            </div>       
            <ul class="nav header-navbar-rht">
                <?php   
                    if ($this->session->userdata('id')) {
                        ?>
                        <?php if ($this->session->userdata('usertype') == 'provider') { ?>
                            <!-- Notifications -->
                            <li class="nav-item dropdown logged-item" id="notification-nav">

                                <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                                    <i class="fa fa-bell"></i>
                                </a>
                                <div class="dropdown-menu notify-blk dropdown-menu-right notifications">
                                    <div class="topnav-dropdown-header">
                                        <span class="notification-title">Notifications</span>
                                        <a href="javascript:void(0)" class="clear-noti noty_clear" data-token="<?php echo $this->session->userdata('chat_token'); ?>"><?php echo (!empty($user_language[$user_selected]['lg_clear_all'])) ? $user_language[$user_selected]['lg_clear_all'] : $default_language['en']['lg_clear_all']; ?>  </a>
                                    </div>
                                    <div class="noti-content">
                                        <ul class="notification-list">

                                        </ul>
                                    </div>
                                    <div class="topnav-dropdown-footer">
                                        <a href="<?= base_url(); ?>notification-list"><?php echo (!empty($user_language[$user_selected]['lg_view_notification'])) ? $user_language[$user_selected]['lg_view_notification'] : $default_language['en']['lg_view_notification']; ?></a>
                                    </div>
                                </div>
                            </li>
                            <!-- /Notifications -->

                            <?php if (!empty($this->session->userdata('id'))) { ?>
                                <!-- chat -->
                                <li class="nav-item dropdown logged-item" id="chat-nav">

                                    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                                        <i class="fa fa-comments" aria-hidden="true"></i>
                                    </a>

                                    <div class="dropdown-menu comments-blk dropdown-menu-right notifications">
                                        <div class="topnav-dropdown-header">
                                            <span class="notification-title"><?php echo (!empty($user_language[$user_selected]['lg_chats'])) ? $user_language[$user_selected]['lg_chats'] : $default_language['en']['lg_chats']; ?></span>
                                            <a href="javascript:void(0)" class="clear-noti chat_clear_all" data-token="<?php echo $this->session->userdata('chat_token'); ?>" > <?php echo (!empty($user_language[$user_selected]['lg_clear_all'])) ? $user_language[$user_selected]['lg_clear_all'] : $default_language['en']['lg_clear_all']; ?> </a>
                                        </div>

                                        <div class="noti-content">
                                            <ul class="chat-list notification-list">

                                            </ul>
                                        </div>
                                        <div class="topnav-dropdown-footer">
                                            <a href="<?= base_url(); ?>user-chat">View all Chat</a>
                                        </div>
                                    </div>
                                </li>
                                <!-- /chat -->
                            <?php } ?>

                        <?php } elseif ($this->session->userdata('usertype') == 'user') { ?>
                            <!-- Notifications -->
                            <li class="nav-item dropdown logged-item" id="notification-nav">

                                <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                                    <i class="fa fa-bell"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right notifications">
                                    <div class="topnav-dropdown-header">
                                        <span class="notification-title">Notifications</span>
                                        <a href="javascript:void(0)" class="clear-noti noty_clear" data-token="<?php echo $this->session->userdata('chat_token'); ?>" > Clear All </a>
                                    </div>
                                    <div class="noti-content">
                                        <ul class="notification-list">
                                            
                                        </ul>
                                    </div>
                                    <div class="topnav-dropdown-footer">
                                        <a href="<?= base_url(); ?>notification-list">View all Notifications</a>
                                    </div>
                                </div>
                            </li>
                            <!-- /Notifications -->

                            <?php if (!empty($this->session->userdata('id'))) { ?>
                                <!-- chat -->
                                <li class="nav-item dropdown logged-item" id="chat-nav">

                                    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                                        <i class="fa fa-comments" aria-hidden="true"></i>
                                    </a>

                                    <div class="dropdown-menu comments-blk dropdown-menu-right notifications">
                                        <div class="topnav-dropdown-header">
                                            <span class="notification-title">Chats</span>
                                            <a href="javascript:void(0)" class="clear-noti chat_clear_all" data-token="<?php echo $this->session->userdata('chat_token'); ?>" > Clear All </a>
                                        </div>

                                        <div class="noti-content">
                                            <ul class="chat-list notification-list">
                                                
                                            </ul>
                                        </div>
                                        <div class="topnav-dropdown-footer">
                                            <a href="<?= base_url(); ?>user-chat">View all Chat</a>
                                        </div>
                                    </div>
                                </li>
                                <!-- /chat -->
                            <?php } ?>
                            
                            <?php
                        }
                    }
                ?>
            </ul>
        </nav>
    </header>
</div>

<script type="text/javascript">
    
</script>

