<?php
/**
 * @author Leo: Service Booking Page
*/
$business_hours = $this->db->where('provider_id', $service['user_id'])->get('business_hours')->row_array();
$availability_details = json_decode($business_hours['availability'], true);

$avg_rating = round($service['rating'], 2);

$user_currency = get_user_currency();
$user_currency_code = $user_currency['user_currency_code'];
$service_amount = get_gigs_currency($service['service_amount'], $service['currency_code'], $user_currency_code);

$user_currency_symbol = currency_conversion($user_currency_code);
$service_currency_symbol = currency_conversion($service['currency_code']);

$coupon = 0;

$userId = $this->session->userdata('id');

$showPrice = show_price();

?>

<!-- Top -->
<section class="top-section">
    <div class="layer">
        <div class="top-banner" style="background-image: url(<?=base_url().$service['subcategory_image']?>);"></div>  
        <div class="top-mask"></div>  
        <div class="top-content">
            <div class="row">
                <div class="col-12">
                    <h2><?=$service['subcategory_name']?></h2>
                </div>
            </div>
            <div class="row align-center justify-center"></div>
        </div>
    </div>
</section>

<section class="content-section">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="service-header">
                    <h2 class="service-title"><?=$service['service_title']?></h2>
                    <!-- <address class="service-location"><i class="fa fa-location-arrow"></i> <?php echo ucfirst($service['service_location']); ?></address>
                    <div class="rating">
                        <?php
                        for ($x = 1; $x <= $avg_rating; $x++) {
                            echo '<i class="fa fa-star filled"></i>';
                        }
                        if (strpos($avg_rating, '.')) {
                            echo '<i class="fa fa-star"></i>';
                            $x++;
                        }
                        while ($x <= 5) {
                            echo '<i class="fa fa-star"></i>';
                            $x++;
                        }
                        ?>
                        <span class="d-inline-block average-rating">(<?php echo $avg_rating; ?>)</span>
                    </div> -->
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-12">
                <?php 
                    $serviceAmountType = $service['serviceamounttype'];
                    if ($serviceAmountType == MONTHLY) {
                        ?>
                <!-- service images -->
                <div class="service-images service-carousel">
                    <div class="images-carousel owl-carousel">
                        <?php
                        if (!empty($service_image)) {
                            for ($i = 0; $i < count($service_image); $i++) {
                                echo'<div class="item"><img src="' . base_url() . $service_image[$i]['service_details_image'] . '" alt="" class="img-fluid"></div>';
                            }
                        }
                        ?>
                    </div>
                    <!--  -->
                    <div class="service-image-content">
                        <h2 class="header-text">Choose your frequency</h2>
                        <p>Please Choose Your desirable Service frequency.</p>
                    </div>
                </div>
                <!-- monthly, weekly, biweekly button -->
                <div class="row service-mode-box">
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <div class="service-mode active mrpx-10">
                            <input type="checkbox" class="custom-checkbox" checked="">
                            <span class="checkmark"></span>
                            <span class="text">Monthly</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <div class="service-mode mrpx-10">
                            <input type="checkbox" class="custom-checkbox">
                            <span class="checkmark"></span>
                            <span class="text">Biweekly</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <div class="service-mode">
                            <input type="checkbox" class="custom-checkbox">
                            <span class="checkmark"></span>
                            <span class="text">Weekly</span>
                        </div>
                    </div>
                </div>
                <!-- duration contract container -->
                <div class="duration-container">
                    <div class="box-banner" style="background-image: url(<?=base_url().$service['subcategory_image']?>);"></div>  
                    <div class="box-mask"></div>  
                    <div class="box-content">
                        <h2 class="box-header">Get more savings</h2>
                        <p>Please choose your desirable contrat</p>
                        <div class="row align-center justify-center duration-row">
                            <div class="duration-box mrpx-10" data-month="3">
                                <span>
                                    <h3>3 Months</h3>
                                    <h3 class="amount"><?=$service_currency_symbol . " " . round($service['service_amount'], 2)?></h3>
                                </span>
                            </div>
                            <div class="duration-box mrpx-10 active" data-month="6">
                                <span>
                                    <h3>6 Months</h3>
                                    <h3 class="amount"><?=$service_currency_symbol . " " . round($service['service_amount'] * (100 - 2) / 100, 2)?></h3>
                                    <h2 class="extra-info">Save 2%</h2>
                                </span>
                            </div>
                            <div class="duration-box" data-month="12">
                                <span>
                                    <h3>12 Months</h3>
                                    <h3 class="amount"><?=$service_currency_symbol . " " . round($service['service_amount'] * (100 - 7) / 100, 2)?></h3>
                                    <h2 class="extra-info">Save 7%</h2>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                        <?php
                    }
                    else if ($serviceAmountType == HOURLY) {
                        ?>
                <!-- service images -->
                <div class="service-images service-carousel">
                    <div class="images-carousel owl-carousel">
                        <?php
                        if (!empty($service_image)) {
                            for ($i = 0; $i < count($service_image); $i++) {
                                echo'<div class="item"><img src="' . base_url() . $service_image[$i]['service_details_image'] . '" alt="" class="img-fluid"></div>';
                            }
                        }
                        ?>
                    </div>
                    <!--  -->
                    <div class="service-image-content">
                        <h2 class="header-text">Choose Your Service Hour</h2>
                        <p>Choose Your Service hour</p>
                    </div>
                </div>
                        <?php
                    }
                    else if ($serviceAmountType == FIXED) {
                        ?>
                <!-- service images -->
                <div class="service-images service-carousel">
                    <div class="images-carousel owl-carousel">
                        <?php
                        if (!empty($service_image)) {
                            for ($i = 0; $i < count($service_image); $i++) {
                                echo'<div class="item"><img src="' . base_url() . $service_image[$i]['service_details_image'] . '" alt="" class="img-fluid"></div>';
                            }
                        }
                        ?>
                    </div>
                    <!--  -->
                    <div class="service-image-content">
                        <h2 class="header-text">Fixed Price</h2>
                        <p>This is fixed price Service.</p>
                    </div>
                </div>
                        <?php                        
                    }
                ?>

                <!-- booking options -->
                <div class="booking-option-container">
                    <?php 
                        $measurement = $service['measurement'];
                        if ($measurement != NONE) {
                            ?>
                    <h3 class="option-header">Service Measurement Amount</h3>
                    <div class="row">
                        <div class="col-lg-9 col-md-9 col-sm-9 col-9">
                            <input class="form-control" type="text" name="measurement" placeholder="<?=SERVICE_MEASUREMENTS[$measurement]?>">
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-3 col-3">
                            <!-- <label><?=$measurement?></label> -->
                        </div>
                    </div>
                            <?php
                        }
                    ?>
                    <h3 class="option-header">How did you hear about us ? (Optional)</h3>
                    <select class="form-control" name="booking_report" id="booking_report">
                        <option value="">Select one</option>
                        <option value="22">TV</option>
                        <option value="5">Facebook</option>
                        <option value="12">Subway / Tube Ad</option>
                        <option value="1">Handy Postcard/Mailer</option>
                        <option value="16">Friend/Family/Word of Mouth</option>
                        <option value="17">Search Engine (Google/Yahoo/Bing)</option>
                        <option value="30">Podcast</option>
                        <option value="33">HelloFresh Offer</option>
                        <option value="34">Airbnb</option>
                    </select>
                    <h3 class="option-header">Promo Code ? (Optional)</h3>
                    <div class="row">
                        <div class="col-lg-9 col-md-9 col-sm-9 col-8">
                            <input class="form-control" type="text" name="promo_code" placeholder="Promo Code">
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-3 col-4">
                            <button type="button" class="btn btn-outline-primary promo-code-apply">Apply</button>
                        </div>
                    </div>
                    <h3 class="option-header">Service Address</h3>
                    <div class="service-address">
                        <form action="#" id="address-form" method="post"  class="needs-validation" novalidate>
                            <?php 
                                $firstName = "";
                                $lastName = "";
                                if (isset($booking_data['booking_first_name'])) {
                                    $firstName = $booking_data['booking_first_name'];
                                }
                                if (isset($booking_data['booking_last_name'])) {
                                    $lastName = $booking_data['booking_last_name'];
                                }
                                $phone = "";
                                $email = "";
                                if (isset($booking_data['booking_email'])) {
                                    $email = $booking_data['booking_email'];
                                }
                                if (isset($booking_data['booking_phonenumber'])) {
                                    $phone = $booking_data['booking_phonenumber'];
                                }
                            ?>
                            <div class="form-row">
                                <div class="col col-lg-6 col-md-6 col-sm-12 prpx-10">
                                    <input class="form-control" type="text" name="first_name" placeholder="First Name" value="<?=$firstName?>" required>
                                </div>
                                <div class="col col-lg-6 col-md-6 col-sm-12">
                                    <input class="form-control" type="text" name="last_name" placeholder="Last Name" value="<?=$lastName?>" required>
                                </div>
                            </div>
                            <!--
                            <select class="form-control" name="country_region" required>
                                <option value="">Select Country</option>
                                <?php foreach($country as $row){?>
                                <option value='<?php echo $row['id'];?>'><?php echo $row['country_name'];?></option> 
                                <?php } ?>
                            </select>
                            <select class="form-control" name="state" required>
                                <option value="">Select State</option>
                            </select>
                            <select class="form-control" name="town_city" required>
                                <option value="">Select City</option>
                            </select>
                             -->


                            <input class="form-control" type="text" id = "c_country" name="country_region"  placeholder="Select Country" required>
                            <input class="form-control" type="text" id = "c_city" name="state"  placeholder="Select Country" required>
                            <input class="form-control" type="text" id = "c_town" name="town_city"  placeholder="Select Country" required>

                           
                            <input class="form-control" type="text" id = "c_st_address1" name="street_address_1"  placeholder="Street Address" required>
                            <input class="form-control" type="text" id = "c_st_address2" name="street_address_2" placeholder="Street Address">
                            <!-- <input class="form-control" type="text" name="town_city" placeholder="Town / City" required> -->
                            <input class="form-control" type="text" name="phone" placeholder="phone" value="<?=$phone?>" required minlength='5'>
                            <input class="form-control" type="text" name="email" placeholder="email" value="<?=$email?>" required>
                            <div class="form-group">
                                <div class="custom-control custom-control-lg custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="agree_checkbox_user" id="agree_checkbox_user1" value="1" required>
                                    <label class="custom-control-label" for="agree_checkbox_user1">I have read and accept the <a href="<?=base_url()?>terms-conditions" target="_blank">Terms & Conditions</a>
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-12 theiaStickySidebar">
                <div class="card order-service-detail">
                  <div class="card-header">Order Service Detail</div>
                  <div class="card-body">
                    <div class="card-item">
                        Minimum 6 Months
                    </div>
                    <?php 
                        $date = "---";
                        $time = "---";
                        if (isset($booking_data['booking_date'])) {
                            $date = date("j F, Y",strtotime($booking_data['booking_date']));
                        }
                        if (isset($booking_data['booking_time'])) {
                            $time = date("g:i a",strtotime($booking_data['booking_time']));
                        }
                    ?>
                    <div class="card-item">
                        <span class="item-title">Date of Booking</span>
                        <span class="item-value" id="booking-date"><?=$date?></span>
                    </div>
                    <div class="card-item">
                        <span class="item-title">Time</span>
                        <span class="item-value" id="booking-time"><?=$time?></span>
                    </div>
                    <hr>
                    <?php 
                        if ($showPrice) {
                            // code...
                            ?>
                    <div class="card-item">
                        <span class="item-title">Per service</span>
                        <span class="item-value" id="service-amount"><?=$service_currency_symbol." <text id='service_amount_value'>".$service['service_amount']."</text>"?></span>
                    </div>
                    <div class="card-item">
                        <span class="item-title">Coupon</span>
                        <span class="item-value green-text" id="coupon"><?=$service_currency_symbol." <text id='coupon_value'>".$coupon."</text>"?></span>
                    </div>

                    <!-- Vadim add Code  start -->
                    <?php           

                        // $user_info = $this->db->where('id', $userId)->get('users')->row_array();                      
                        // $user_type = $user_info ['you_are_appling_as'];
                        // $init_fee = 0.02;
                        // $init_fee_t = 0.01;   
                        
                        // if($user_type == '9') {
                        //     $com_fee = 0.011;
                        // }
                        // else {
                        //     $subscription_detail = $this->db->where('subscriber_id', $userId)->get('subscription_details')->row_array();
                        //     $subscription = $subscription_detail['subscription_id'];
                                                
                        //     $where = array('user_type =' => (int)$user_type, 'subscription =' => (int)$subscription);
                        //     $result_fee = $this->db->where($where)->get('commission_ta')->row_array();
                        
                        //     $com_fee = isset($result_fee['commission_fee']) ? $result_fee['commission_fee']:$init_fee;
                        // }                        
                          
                        // $result_fee_t = $this->db->where('user_type',$user_type)->get('transaction_fee_1')->row_array();
                        // $trans_fee = isset($result_fee['transaction_fee']) ? $result_fee_t['transaction_fee']: $init_fee_t;

                        $com_fee = getCommissionFee();
                        $trans_fee = getTransactionFee();
                                                   
                        $commission_fee = $service['service_amount'] * $com_fee;
                        $transaction_fee =  $service['service_amount'] * $trans_fee;

                        $sub_total = $service['service_amount'] + $commission_fee + $transaction_fee + $coupon;
                    ?>        

                    <div class="card-item">
                        <span class="item-title">Commission Fee</span>
                        <span class="item-value green-text" id="coupon"><?=$service_currency_symbol." <text id='coupon_value'>".$commission_fee."</text>"?></span>
                    </div>
                    <div class="card-item">
                        <span class="item-title">Transaction Fee</span>
                        <span class="item-value green-text" id="coupon"><?=$service_currency_symbol." <text id='coupon_value'>".$transaction_fee."</text>"?></span>
                    </div>   
                    
                   <!-- Vadim add Code end   --> 

                    <br>
                    <div class="card-item">
                        <span class="item-title">Sub Total</span>
                        <span class="item-value" id="sub-total-amount"><?=$service_currency_symbol." <text id='sub_total_value'>".$sub_total."</text>"?></span>
                    </div>
                    <hr>
                    <div class="card-item">
                        <span class="item-title blue-text">Total</span>
                        <span class="item-value blue-text" id="total-amount"><?=$service_currency_symbol." <text id='total_value'>".$sub_total."</text>"?></span>
                    </div>
                            <?php
                        }
                    ?>
                  </div>
                  <!-- <div class="card-footer">Footer</div> -->
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-12">
                <!-- <div class="form-group">
                    <div class="custom-control custom-control custom-radio">
                        <input type="radio" class="custom-control-input" name="payment_method" id="payment_method1" value="wallet" required>
                        <label class="custom-control-label" for="payment_method1">Pay From Your Wallet</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-control custom-radio">
                        <input type="radio" class="custom-control-input" name="payment_method" id="payment_method2" value="paypal" required>
                        <label class="custom-control-label" for="payment_method2">Pay By Other Payment Method</label>
                    </div>
                </div>
                <div class="form-group">
                    <button id="booking-complete" type="button" class="btn tazzergroup-btn btn-block">Complete Booking</button>
                    <div id="paypal-button-container"></div>
                </div> -->
                <div class="form-group">
                    <button id="booking-continue" type="button" class="btn tazzergroup-btn btn-block">Complete Booking</button>
                </div>
            </div>
        </div>
        
    </div>
</section>

<link rel="stylesheet" href="<?php echo base_url();?>assets/css/service_booking/index.css?v1.02">
<script type="text/javascript">
    var service = <?=json_encode($service)?>;
    // console.log(service);
    var bookingData = <?=json_encode($booking_data)?>;
    var coupon = 0;
    var userCurrencyCode = <?=json_encode($user_currency_code)?>;
    var serviceAmount = <?=json_encode($service_amount)?>;
</script>
<script src="<?php echo base_url(); ?>assets/js/country_list.js"></script>
<script src="<?php echo base_url(); ?>assets/js/service_booking/index.js?v1.11"></script>

<script src="https://www.paypal.com/sdk/js?client-id=<?=$paypal_client_id;?>&components=buttons,marks,messages,funding-eligibility&currency=<?=$user_currency['user_currency_code'];?>&enable-funding=venmo"> // Replace YOUR_CLIENT_ID with your sandbox client ID
</script>