<?php
defined('BASEPATH') or exit('No direct script access allowed');
// require $_SERVER['DOCUMENT_ROOT'].'/stripeonsite/Stripe.php';
// @Leo: Stripe, Paypal library Load ----------------
use Stripe\Stripe;
require $_SERVER['DOCUMENT_ROOT'] . '/paypal/PayPalClient.php';
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
// --------------------------------------------------

class Dashboard extends MY_Controller       # upgrade maksimU for whitelabel using MY_Controller
{

    public $data;

    public function __construct()
    {

        parent::__construct();
        error_reporting(0);
        checkLoginRedirect();   // Leo
        $this->data['theme'] = 'user';
        $this->data['module'] = 'home';
        $this->data['page'] = '';
        $this->data['base_url'] = base_url();
        $this->load->helper('user_timezone_helper');
        $this->load->model('service_model', 'service');
        $this->load->model('home_model', 'home');
        $this->load->model('Api_model', 'api');
        $this->load->model('Stripe_model');

        // Load pagination library
        $this->load->library('paypal_lib');
        $this->load->library('ajax_pagination');
        $this->load->helper('form');
        $this->data['csrf'] = array(
            'name' => $this->security->get_csrf_token_name() ,
            'hash' => $this->security->get_csrf_hash()
        );

        // Load post model
        $this->load->model('booking');
        $this->load->model('User_booking', 'userbooking');

        // Per page limit
        $this->perPage = 6;

        // @Leo: stripe key init ------
        $stripeKeys = stripeKeys();
        \Stripe\Stripe::setApiKey($stripeKeys['secret_key']);
        $this->stripeKeys = $stripeKeys;

        //public $data;
        $this->load->model('common_model', 'common_model');
        $this->data['base_url'] = base_url();
        $this->load->helper('user_timezone');
        $this->data['csrf'] = array(
            'name' => $this->security->get_csrf_token_name() ,
            'hash' => $this->security->get_csrf_hash()
        );
        $this->load->helper('ckeditor');
        $this->load->helper('common_helper');
        $this->data['ckeditor_editor252'] = array(
            'id' => 'ck_editor_textarea_id252',
            'path' => 'assets/js/ckeditor',
            'config' => array(
                'toolbar' => "Full",
                'filebrowserBrowseUrl' => base_url() . 'assets/js/ckfinder/ckfinder.html',
                'filebrowserImageBrowseUrl' => base_url() . 'assets/js/ckfinder/ckfinder.html?Type=Images',
                'filebrowserFlashBrowseUrl' => base_url() . 'assets/js/ckfinder/ckfinder.html?Type=Flash',
                'filebrowserUploadUrl' => base_url() . 'assets/js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
                'filebrowserImageUploadUrl' => base_url() . 'assets/js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
                'filebrowserFlashUploadUrl' => base_url() . 'assets/js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
            )
        );
    }

    public function index()
    {   
        $this->data['page'] = 'index';
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    public function company_time_clock()
    {
        $shift_data = [];
        $users = $this->db->select('name, id, profile_img')->where('you_are_appling_as', 8)->get('users')->result_array();
        $this->data['page'] = 'user_time_clock';
        $user_id = $this->session->userdata('id');
        $user_permission = $this->db->select('permission')
                                    ->where('id', $user_id)
                                    ->get('users')
                                    ->result_array();
        $this->data['status'] = $this->db->select('name')
                                    ->where('id', $user_permission[0]['permission'])
                                    ->get('user_permission')
                                    ->result_array();
        $today = date("D M d Y");
        $this->data['clocked_today'] = $this->db->like('work_date', $today)->get('shift_detail')->result_array();

        $this->data['users'] = $users;
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    public function company_job_scheduling()
    {   
        $shift_data = [];
        $users = $this->db->select('name, id, profile_img')->where('you_are_appling_as', 8)->get('users')->result_array();
        $user_id = $this->session->userdata('id');
        $user_permission = $this->db->select('permission')
                                    ->where('id', $user_id)
                                    ->get('users')
                                    ->result_array();
        $this->data['status'] = $this->db->select('name')
                                    ->where('id', $user_permission[0]['permission'])
                                    ->get('user_permission')
                                    ->result_array();
        $this->data['lists'] = $this->db->get('categories')->result_array();
        $this->data['users'] = $users;
        $this->data['page'] = 'user_job_scheduling';
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    public function shift_add_data()
    {
        $shift_data['shift_title'] = $this->input->post('shift_title');
        $shift_data['location'] = $this->input->post('shift_location');
        $shift_data['shift_start'] = $this->input->post('shift_start');
        $shift_data['shift_end'] = $this->input->post('shift_end');
        $shift_data['total_hours'] = $this->input->post('shift_end') - $this->input->post('shift_start');
        $shift_data['shift_date'] = $this->input->post('shift_date');
        $shift_data['schedule_job'] = $this->input->post('subcategory');
        $shift_data['schedule_note'] = $this->input->post('schedule_note');
        $shift_data['user_id'] = $this->input->post('user_id');

        if($shift_data !== null && $shift_data['schedule_job'] !== null) {
            $this->db->insert('shift_schedule', $shift_data);
            $shift_data['schedule_job'] = [];
        }
    }
    public function shift_update_data()
    {
        $shift_data['shift_title'] = $this->input->post('shift_title');
        $shift_data['location'] = $this->input->post('shift_location');
        $shift_data['shift_start'] = $this->input->post('shift_start');
        $shift_data['shift_end'] = $this->input->post('shift_end');
        $shift_data['total_hours'] = $this->input->post('shift_end') - $this->input->post('shift_start');
        $shift_data['shift_date'] = $this->input->post('shift_date');
        // $shift_data['schedule_job'] = $this->input->post('subcategory');
        $shift_data['schedule_note'] = $this->input->post('schedule_note');
        $shift_data['user_id'] = $this->input->post('user_id');
        if($shift_data !== null) {
            $this->db->where('shift_start', $shift_data['shift_start'])->where('shift_end', $shift_data['shift_end'])->where('user_id', $shift_data['user_id'])->update('shift_schedule', $shift_data);
            // $shift_data['schedule_job'] = [];
        }
    }
    public function shift_detail_add_data()
    {   
        $shift_data['user_id'] = $this->input->post('user_id');
        $shift_data['work_date'] = $this->input->post('work_date');
        $shift_data['job_title'] = $this->input->post('job_title');
        $shift_data['clocked_in'] = $this->input->post('clocked_in');
        $shift_data['clocked_out'] = $this->input->post('clocked_out');
        $shift_data['work_hour'] = $this->input->post('work_hour');
        $shift_data['note'] = $this->input->post('note');
        $result = $this->db->order_by('id','desc')->limit(1)->where('user_id', $shift_data['user_id'])->like('work_date', $shift_data['work_date'])->get('shift_detail')->result_array();
        if($result) {
            if($shift_data !== null && $shift_data['job_title'] !== null) {
                $this->db->order_by('id','desc')->limit(1)->where('user_id', $shift_data['user_id'])->like('work_date', $shift_data['work_date'])->update('shift_detail', $shift_data);
                $shift_data['job_title'] = [];
            }
        }
    }
    public function shift_get_data()
    {   
        $result = [];
        $i = 0;
        $week_dates = $this->input->post('weekDates');
        foreach($week_dates as $week_date) {
            $temp = $this->db->order_by('id','desc')->limit(1)->like('shift_date', $week_date)->get('shift_schedule')->result_array();
            $result[$i] = $temp;
            $i++;
        }
        if($result) echo json_encode($result);
    }
    public function shift_detail_get_data()
    {   
        $result = [];
        $result_absence = [];
        $i = 0;
        $j = 0;
        $week_dates = $this->input->post('weekDates');
        $post_dates = $this->input->post('postDates');
        $user_id = $this->input->post('userID');

        foreach($week_dates as $week_date) {
            $temp = $this->db->order_by('id','desc')->limit(1)->where('user_id', $user_id)->like('work_date', $week_date)->get('shift_detail')->result_array();
            $result['shift_detail'][$i] = $temp;
            $i++;
        }

        foreach($post_dates as $post_date) {
            $from_date = $this->db->order_by('id','desc')->limit(1)->where('user_id', $user_id)->like('absence_from', $post_date)->get('employee_absence')->result_array();
            $to_date = $this->db->order_by('id','desc')->limit(1)->where('user_id', $user_id)->like('absence_to', $post_date)->get('employee_absence')->result_array();
            $result['from_date'][$j] = $from_date;
            $result['to_date'][$j] = $to_date;
            $j++;
        }

        if($result) echo json_encode($result);
    }
    /**
     * @author Leo: create subscription with stripe
     * @created: 
    */
    public function create_stripe_pro_subscription()
    {

        $subscriptionId = $_POST['subscription_id'];
        $this->load->model('SubscriptionFee');
        $data = $this->SubscriptionFee->getData($subscriptionId);
        $quantity = intval($data['fee']);
        if ($quantity == 0) {
            // code...
            $quantity = 1;
        }

        $session = \Stripe\Checkout\Session::create(['customer_email' => $this->session->userdata['email'], 'payment_method_types' => ['card'], 'line_items' => [['price_data' => ['currency' => $data['currency_code'], 'product_data' => ['name' => $data['subscription_name'], ], 'unit_amount' => 100, ], 'quantity' => $quantity, ]], 'mode' => 'payment', 'success_url' => base_url() . 'pro-subscription-success/{CHECKOUT_SESSION_ID}?sub_id=' . $subscriptionId, 'cancel_url' => base_url() . 'provider-subscription', ]);

        echo json_encode(['id' => $session->id]);
        exit;
    }

    /**
     * @author Leo: subscription success redirection
     * @created: 
    */
    public function pro_subscription_success($sessionId)
    {
        // valid check
        $row = $this->db->select()->where('tokenid', $sessionId)->get('subscription_payment')->row_array();
        if (is_array($row) && count($row) > 0)
        {
            redirect(base_url() . 'provider-subscription');
            return;
        }

        $stripe = new \Stripe\StripeClient($this->stripeKeys['secret_key']);
        $session = $stripe->checkout->sessions->retrieve($sessionId, []);
        if (is_null($session) || !isset($session))
        {
            redirect(base_url() . 'provider-subscription');
        }

        $token = $this->session->userdata('chat_token');
        // $user_info	 		= 	$this->api->get_token_info($token);
        // $wallet 			= 	$this->api->get_wallet($token);
        // $curren_wallet 		= 	get_gigs_currency($wallet['wallet_amt'], $wallet['currency_code'], "USD");
        // $user_currency 		= 	get_user_currency();
        $user_data['token'] = $token;
        $user_data['subscription_id'] = $_REQUEST['sub_id'];
        $user_data['transaction_id'] = $session->id;

        $result = $this->api->subscription_success($user_data);

        $this->session->set_flashdata('success_message', 'Subscribed successfully');

        redirect(base_url() . 'provider-subscription');
    }

    /**
     * @author Leo: create subscription with stripe
     * @created: 
    */
    public function create_stripe_business_subscription()
    {

        $subscriptionId = $_POST['subscription_id'];
        $this->load->model('SubscriptionFee');
        $data = $this->SubscriptionFee->getData($subscriptionId);
        $quantity = intval($data['fee']);
        if ($quantity == 0) {
            // code...
            $quantity = 1;
        }

        $session = \Stripe\Checkout\Session::create(['customer_email' => $this->session->userdata['email'], 'payment_method_types' => ['card'], 'line_items' => [['price_data' => ['currency' => $data['currency_code'], 'product_data' => ['name' => $data['subscription_name'], ], 'unit_amount' => 100, ], 'quantity' => $quantity, ]], 'mode' => 'payment', 'success_url' => base_url() . 'business-subscription-success/{CHECKOUT_SESSION_ID}?sub_id=' . $subscriptionId, 'cancel_url' => base_url() . 'business-subscription', ]);

        echo json_encode(['id' => $session->id]);
        exit;
    }

    /**
     * @author Leo: subscription success redirection
     * @created: 
    */
    public function business_subscription_success($sessionId)
    {
        // valid check
        $row = $this->db->select()->where('tokenid', $sessionId)->get('subscription_payment')->row_array();
        if (is_array($row) && count($row) > 0)
        {
            redirect(base_url() . 'business-subscription');
            return;
        }

        $stripe = new \Stripe\StripeClient($this->stripeKeys['secret_key']);
        $session = $stripe->checkout->sessions->retrieve($sessionId, []);
        if (is_null($session) || !isset($session))
        {
            redirect(base_url() . 'business-subscription');
        }

        $token = $this->session->userdata('chat_token');
        // $user_info           =   $this->api->get_token_info($token);
        // $wallet          =   $this->api->get_wallet($token);
        // $curren_wallet       =   get_gigs_currency($wallet['wallet_amt'], $wallet['currency_code'], "USD");
        // $user_currency       =   get_user_currency();
        $user_data['token'] = $token;
        $user_data['subscription_id'] = $_REQUEST['sub_id'];
        $user_data['transaction_id'] = $session->id;

        $result = $this->api->subscription_success($user_data);

        $this->session->set_flashdata('success_message', 'Subscribed successfully');

        redirect(base_url() . 'business-subscription');
    }

    /**
     * @author Leo: create wallet Checkout Session with stripe
     * @created: 
    */
    public function create_stripe_user_wallet()
    {

        $walletAmt = $_POST['wallet_amt'];
        $user_currency = get_user_currency();

        $session = \Stripe\Checkout\Session::create(['customer_email' => $this->session->userdata['email'], 'payment_method_types' => ['card'], 'line_items' => [['price_data' => ['currency' => $user_currency['user_currency_code'], 'product_data' => ['name' => "Add My Wallet", ], 'unit_amount' => 100, ], 'quantity' => intval($walletAmt) , ]], 'mode' => 'payment', 'success_url' => base_url() . 'add-wallet-success/{CHECKOUT_SESSION_ID}', 'cancel_url' => base_url() . 'user-wallet', ]);

        echo json_encode(['id' => $session->id]);
        exit;
    }

    /**
     * @author Leo: wallet add checkout success redirection with stripe
     * @created: 
    */
    public function add_wallet_success($sessionId)
    {
        // valid check
        $walletRow = $this->db->select()->where('charge_id', $sessionId)->get('wallet_transaction_history')->row_array();
        if (is_array($walletRow) && count($walletRow) > 0)
        {
            redirect(base_url() . 'user-wallet');
            return;
        }
        $stripe = new \Stripe\StripeClient($this->stripeKeys['secret_key']);
        $session = $stripe->checkout->sessions->retrieve($sessionId, []);
        if (is_null($session) || !isset($session))
        {
            $this->session->set_flashdata('error_message', 'Invalid Stripe Checkout Session Id!');
            redirect(base_url() . 'user-wallet');
        }
        // print_r($session);
        // $all = $stripe->checkout->sessions->all();
        // print_r($all);
        $token = $this->session->userdata('chat_token');
        $user_info = $this->api->get_token_info($token);
        $user_currency = get_user_currency();

        $payment_intent = $stripe->paymentIntents->retrieve($session->payment_intent, []);
        $charges = $payment_intent->charges->data;
        foreach ($charges as $charge)
        {
            $balanceTransactionId = $charge->balance_transaction;
            $balanceTransaction = $stripe->balanceTransactions->retrieve($balanceTransactionId, []);

            $wallet = $this->api->get_wallet($token);
            $amount_total = $balanceTransaction->net / 100;
            if ($wallet['id'] == '')
            {
                $insert_main_wallet = array();
                $insert_main_wallet['token'] = $token;
                $insert_main_wallet['currency_code'] = strtoupper($balanceTransaction->currency);
                $insert_main_wallet['user_provider_id'] = $user_info->id;
                $insert_main_wallet['type'] = $user_info->type;
                $insert_main_wallet['wallet_amt'] = $amount_total;
                $insert_main_wallet['reason'] = 'Added by Stripe';;
                $this->db->insert('wallet_table', $insert_main_wallet);
            }
            else
            {
                $wallet_dat['currency_code'] = $wallet['currency_code'];
                $current_wallet = $wallet['wallet_amt'];
                $wallet_dat['wallet_amt'] = $current_wallet + $amount_total;
                $wallet_dat['updated_on'] = date('Y-m-d H:i:s');
                $WHERE = array(
                    'token' => $token
                );
                $result = $this->api->update_wallet($wallet_dat, $WHERE);
            }
            // transaction history
            $history_pay = array();
            $history_pay['token'] = $token;
            $history_pay['currency_code'] = strtoupper($balanceTransaction->currency);
            $history_pay['user_provider_id'] = $user_info->id;
            $history_pay['type'] = $user_info->type;
            $history_pay['tokenid'] = $token;
            $history_pay['payment_detail'] = json_encode(["id" => $session->id, 'amount_subtotal' => $session->amount_subtotal, 'amount_total' => $session->amount_total, 'customer' => $session->customer, 'currency' => $session->currency, 'customer_email' => $session->customer_email, 'payment_intent' => $session->payment_intent]);
            $history_pay['charge_id'] = $charge->id;
            $history_pay['transaction_id'] = $balanceTransaction->id;
            $history_pay['exchange_rate'] = '';
            $history_pay['paid_status'] = $session->payment_status;
            $history_pay['current_wallet'] = isset($wallet['wallet_amt']) ? $wallet['wallet_amt'] : 0;
            $history_pay['credit_wallet'] = $amount_total;
            $history_pay['debit_wallet'] = 0;
            $history_pay['avail_wallet'] = $amount_total;

            $history_pay['total_amt'] = $balanceTransaction->amount;
            $history_pay['fee_amt'] = $balanceTransaction->fee;
            $history_pay['net_amt'] = $balanceTransaction->net;

            $history_pay['reason'] = 'Added by Stripe';;
            $history_pay['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('wallet_transaction_history', $history_pay);
        }

        $this->session->set_flashdata('success_message', 'Amount added to wallet successfully');

        redirect(base_url() . 'user-wallet');
    }

    /**
     * @author Leo: payment verify page
     * @created: 
    */
    public function verify_payment_method()
    {
        $user_id = $this->session->userdata('id');
        $user_token = $this->session->userdata('chat_token');
        $this->load->model('StripeConnectAccounts');
        $accountInfo = $this->StripeConnectAccounts->getAccount($user_token);
        $this->data['stripe_account_exist'] = false;
        $this->data['stripe_verified'] = false;
        if (!is_null($accountInfo) && trim($accountInfo['account_id']) !== "")
        {
            $stripeAccount = \Stripe\Account::retrieve($accountInfo['account_id']);
            if (isset($stripeAccount->id))
            {
                $this->data['stripe_account_exist'] = true;
            }
            if ($stripeAccount->payouts_enabled == 1)
            {
                $this->data['stripe_verified'] = true;
            }
        }

        $this->data['page'] = 'verify_payment_method';
        // $this->data['user_details']=$user=$this->db->where('providers.id',$user_id)->join('provider_address','provider_address.provider_id=providers.id','left')->get('providers')->row_array();
        $this->data['  '] = $this->api->get_wallet($this->session->userdata('chat_token'));
        $this->data['wallet_history'] = $this->api->get_wallet_history_info($this->session->userdata('chat_token'));
        $this->load->vars($this->data);

        $this->load->view($this->data['theme'] . '/template');
    }

    /**
     * @author Leo: create stripe account
     * @created: 
    */
    public function createStripeAccount($params = array())
    {
        $config = ['type' => isset($params['type']) ? $params['type'] : "custom", 'capabilities' => ['card_payments' => ['requested' => true, ], 'transfers' => ['requested' => true, ], ], ];
        if (!isset($params['email']))
        {
            $config['email'] = $params['email'];
        }
        $account = \Stripe\Account::create($config);
        return $account;
    }

    /**
     * @author Leo: generate account link
     * @created: 
    */
    public function generateAccountLink($accountId)
    {

        $refreshUrl = base_url() . "onboard-stripe-refresh/" . $accountId;
        if (stripeMode() == "test")
        {
            $refreshUrl = str_replace("https://", "http://", $refreshUrl);
        }

        $account_link = \Stripe\AccountLink::create(['account' => $accountId, 'refresh_url' => $refreshUrl, 'return_url' => base_url() . "verify-payment-method", 'type' => 'account_onboarding', ]);
        return $account_link->url;
    }

    /**
     * @author Leo: stripe verify onboard
     * @created: 
    */
    public function onboard_stripe()
    {
        $user_token = $this->session->userdata('chat_token');
        $userInfo = $this->api->get_token_info($user_token);
        $this->load->model('StripeConnectAccounts');
        $accountInfo = $this->StripeConnectAccounts->getAccount($user_token);
        $account_id = "";
        if (is_null($accountInfo) || !isset($accountInfo))
        {
            $account = $this->createStripeAccount(['email' => $this->session->userdata['email']]);
            $accountData = ['user_token' => $user_token, 'user_provider_id' => $userInfo->id, 'user_type' => $userInfo->type, 'account_id' => $account->id, 'country' => $account->country, 'default_currency' => $account->default_currency, 'type' => $account->type];
            $this->StripeConnectAccounts->insertAccount($accountData);
            $account_id = $account->id;
        }
        else
        {
            if (trim($accountInfo['account_id']) == "")
            {
                $account = $this->createStripeAccount(['email' => $this->session->userdata['email']]);
                $accountData = ['account_id' => $account->id, 'country' => $account->country, 'default_currency' => $account->default_currency, 'type' => $account->type];
                $this->StripeConnectAccounts->update($user_token, $accountData);
                $account_id = $account->id;
            }
            else
            {
                $account_id = $accountInfo['account_id'];
            }
        }

        $this->session->set_flashdata('onboard_account_id', $account_id);

        $account_link_url = $this->generateAccountLink($account_id);

        echo json_encode(array(
            'url' => $account_link_url
        ));
        exit;
    }

    /**
     * @author Leo: stripe onboard refresh
     * @created: 
    */
    public function onboard_stripe_refresh($accountId)
    {
        if (empty($this->session->flashdata('onboard_account_id')))
        {
            header('Location: ' . base_url());
            http_response_code(302);
            exit;
        }
        $account_id = $this->session->flashdata('onboard_account_id');

        $account_link_url = $this->generateAccountLink($account_id);

        header('Location: ' . $account_link_url);
        http_response_code(302);
        exit;
    }

    /**
     * @author Leo: 
    */
    public function webhook_stripe_user_wallet()
    {
        $payload = @file_get_contents('php://input');
        $event = null;

        try
        {
            $event = \Stripe\Event::constructFrom(json_decode($payload, true));
        }
        catch(\UnexpectedValueException $e)
        {
            // Invalid payload
            http_response_code(400);
            exit();
        }

        // Handle the event
        switch ($event->type)
        {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                // Then define and call a method to handle the successful payment intent.
                // handlePaymentIntentSucceeded($paymentIntent);
                
            break;
            case 'payment_method.attached':
                $paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
                // Then define and call a method to handle the successful attachment of a PaymentMethod.
                // handlePaymentMethodAttached($paymentMethod);
                
            break;
                // ... handle other event types
                
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        http_response_code(200);
        // ----------------------------------------------
        // function print_log($val) {
        //   return file_put_contents('php://stderr', print_r($val, TRUE));
        // }
        // // You can find your endpoint's secret in your webhook settings
        // $endpoint_secret = 'whsec_vtOQYuIoHcMYRvRFH2jEXSioBzDcic7Z';
        // $payload = @file_get_contents('php://input');
        // $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        // $event = null;
        // try {
        //   $event = \Stripe\Webhook::constructEvent(
        //     $payload, $sig_header, $endpoint_secret
        //   );
        // } catch(\UnexpectedValueException $e) {
        //   // Invalid payload
        //   http_response_code(400);
        //   exit();
        // } catch(\Stripe\Exception\SignatureVerificationException $e) {
        //   // Invalid signature
        //   http_response_code(400);
        //   exit();
        // }
        // function fulfill_order($session) {
        //   // TODO: fill me in
        //   print_log("Fulfilling order...");
        //   print_log($session);
        // }
        // // Handle the checkout.session.completed event
        // if ($event->type == 'checkout.session.completed') {
        //   $session = $event->data->object;
        //   // Fulfill the purchase...
        //   fulfill_order($session);
        // }
        // http_response_code(200);
        // ----------------------------------------
        
    }

    public function get_paypal_access_token()
    {
        echo getPaypalAccessToken();
    }

    public function get_paypal_client_token()
    {
        $data = getPaypalClientToken();
        print_r($data);
        exit;
    }

    public function set_session()
    {
        $params = $_POST['params'];
    }

    /**
     * @author Leo: add wallet page with paypal
     * @created: 
    */
    public function paypal_add_wallet()
    {
        $paypalKeys = paypalKeys();
        $this->data['page'] = 'paypal_add_wallet';
        $this->data['client_id'] = $paypalKeys["client_id"];
        // $data = getPaypalClientToken();
        // $this->data['client_token'] = $data->client_token;
        $this->load->vars($this->data);
        // $this->load->view($this->data['theme'].'/'.$this->data['module'].'/paypal_add_wallet');
        $this->load->view($this->data['theme'] . '/template');
    }

    /**
     * @author Leo: create paypal user wallet
     * @created: 
    */
    public function create_paypal_user_wallet()
    {

        $walletAmt = $_POST['wallet_amt'];
        $user_currency = get_user_currency();

        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = array(
            'intent' => 'CAPTURE',
            'application_context' => ['return_url' => base_url() . 'user-wallet',
            'cancel_url' => base_url() . 'paypal-add-wallet'],
            'purchase_units' => [['amount' => ['currency_code' => $user_currency['user_currency_code'],
            'value' => $walletAmt]]]
        );
        // 3. Call PayPal to set up a transaction
        $client = PayPalClient::client();
        try
        {
            $response = $client->execute($request);
        }
        catch(\PayPal\Exception\PayPalHttpException $ex)
        {
            echo $ex->getData();
            exit();
        }

        $debug = false;
        if ($debug)
        {
            print "Status Code: {$response->statusCode}\n";
            print "Status: {$response->result->status}\n";
            print "Order ID: {$response->result->id}\n";
            print "Intent: {$response->result->intent}\n";
            print "Links:\n";
            foreach ($response->result->links as $link)
            {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }

            // To print the whole response body, uncomment the following line
            // echo json_encode($response->result, JSON_PRETTY_PRINT);
            
        }

        // 4. Return a successful response to the client.
        // print_r($response);
        // return $response;
        echo json_encode($response->result);
    }

    /**
     * @author Leo: capture paypal user wallet
     * @created: 
    */
    public function capture_paypal_user_wallet()
    {

        $orderId = $_POST['order_id'];
        $request = new OrdersCaptureRequest($orderId);
        // 3. Call PayPal to capture an authorization
        $client = PayPalClient::client();
        $response = $client->execute($request);
        // 4. Save the capture ID to your database. Implement logic to save capture to your database for future reference.
        $debug = false;
        if ($debug)
        {
            print "Status Code: {$response->statusCode}\n";
            print "Status: {$response->result->status}\n";
            print "Order ID: {$response->result->id}\n";
            print "Links:\n";
            foreach ($response->result->links as $link)
            {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            print "Capture Ids:\n";
            foreach ($response->result->purchase_units as $purchase_unit)
            {
                foreach ($purchase_unit->payments->captures as $capture)
                {
                    print "\t{$capture->id}";
                }
            }
            // To print the whole response body, uncomment the following line
            // echo json_encode($response->result, JSON_PRETTY_PRINT);
            
        }

        echo json_encode($response->result, JSON_PRETTY_PRINT);
    }

    /**
     * @author Leo: paypal add wallet success
     * @created: 
    */
    public function paypal_add_wallet_success($orderId)
    {
        // valid check
        $walletRow = $this->db->select()->where('charge_id', $orderId)->get('wallet_transaction_history')->row_array();
        if (is_array($walletRow) && count($walletRow) > 0)
        {
            redirect(base_url() . 'user-wallet');
            return;
        }
        $client = PayPalClient::client();
        $response = $client->execute(new OrdersGetRequest($orderId));

        // print_r($response); exit;
        $orderObj = $response->result;
        $token = $this->session->userdata('chat_token');
        $user_info = $this->api->get_token_info($token);
        $user_currency = get_user_currency();

        foreach ($orderObj->purchase_units as $purchase_unit)
        {
            foreach ($purchase_unit->payments->captures as $capture)
            {
                // code...
                $wallet = $this->api->get_wallet($token);
                $amount_total = $capture->seller_receivable_breakdown->net_amount->value;
                $currency_code = $capture->seller_receivable_breakdown->net_amount->currency_code;
                if ($wallet['id'] == '')
                {
                    $insert_main_wallet = array();
                    $insert_main_wallet['token'] = $token;
                    $insert_main_wallet['currency_code'] = strtoupper($currency_code);
                    $insert_main_wallet['user_provider_id'] = $user_info->id;
                    $insert_main_wallet['type'] = $user_info->type;
                    $insert_main_wallet['wallet_amt'] = $amount_total;
                    $insert_main_wallet['reason'] = 'Added by Paypal';;
                    $this->db->insert('wallet_table', $insert_main_wallet);
                }
                else
                {
                    $wallet_dat['currency_code'] = $wallet['currency_code'];
                    $current_wallet = $wallet['wallet_amt'];
                    $wallet_dat['wallet_amt'] = $current_wallet + $amount_total;
                    $wallet_dat['updated_on'] = date('Y-m-d H:i:s');
                    $WHERE = array(
                        'token' => $token
                    );
                    $result = $this->api->update_wallet($wallet_dat, $WHERE);
                }
                // transaction history
                $history_pay = array();
                $history_pay['token'] = $token;
                $history_pay['currency_code'] = strtoupper($currency_code);
                $history_pay['user_provider_id'] = $user_info->id;
                $history_pay['type'] = $user_info->type;
                $history_pay['tokenid'] = $token;
                $history_pay['payment_detail'] = json_encode(["capture" => $capture]);
                $history_pay['charge_id'] = $orderId;
                $history_pay['transaction_id'] = $capture->id;
                $history_pay['exchange_rate'] = '';
                $history_pay['paid_status'] = $capture->status;
                $history_pay['current_wallet'] = isset($wallet['wallet_amt']) ? $wallet['wallet_amt'] : 0;
                $history_pay['credit_wallet'] = $amount_total;
                $history_pay['debit_wallet'] = 0;
                $history_pay['avail_wallet'] = $amount_total;

                $history_pay['total_amt'] = floatval($capture->seller_receivable_breakdown->gross_amount->value) * 100;
                $history_pay['fee_amt'] = floatval($capture->seller_receivable_breakdown->paypal_fee->value) * 100;
                $history_pay['net_amt'] = floatval($capture->seller_receivable_breakdown->net_amount->value) * 100;

                $history_pay['reason'] = 'Added by Paypal';;
                $history_pay['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('wallet_transaction_history', $history_pay);
            }
        }

        $this->session->set_flashdata('success_message', 'Amount added to wallet successfully');

        redirect(base_url() . 'user-wallet');
    }

    /**
     * @author Leo: start subscription with paypal
     * @created: 
    */
    public function start_paypal_pro_subscription()
    {
        $subscription_id = $_POST['subscription_id'];
        $this->load->model('SubscriptionFee');
        $data = $this->SubscriptionFee->getData($subscription_id);
        if (trim($data['paypal_request_id']) == "")
        {
            $paypalRequestId = date('Ymd') . '-' . $data['id'];
            if ($this->SubscriptionFee->update($data['id'], ['paypal_request_id' => $paypalRequestId]))
            {
                $data['paypal_request_id'] = $paypalRequestId;
            }
        }
        if (trim($data['description']) == "")
        {
            $data['description'] = $data['subscription_name'];
        }
        $productParams = ['name' => $data['subscription_name'], 'description' => $data['description'], 'type' => 'SERVICE', 'category' => 'SOFTWARE',
        // 'image_url'=>base_url().'assets/img/'.$data['subscription_name'].'.jpg',
        // 'home_url'=>base_url().'provider-subscription',
        "image_url" => "https://example.com/streaming.jpg", "home_url" => "https://example.com/home", 'request_id' => $data['paypal_request_id']];
        $subscriptionProduct = paypalProduct($productParams);
        $this->load->model('SubscriptionPaypalProduct');
        if (!$this->SubscriptionPaypalProduct->isExist($subscription_id))
        {
            $product = ['subscription_id' => $subscription_id, 'product_id' => $subscriptionProduct->id, 'name' => $subscriptionProduct->name, 'description' => $subscriptionProduct->description, 'create_time' => $subscriptionProduct->create_time];
            $this->SubscriptionPaypalProduct->insert($product);
        }
        $planParams = ['product_id' => $subscriptionProduct->id, 'name' => $data['subscription_name'], 'description' => $data['description'], 'total_cycles' => $data['duration'], 'price_value' => $data['fee'], 'currency_code' => $data['currency_code'], 'request_id' => $data['paypal_request_id']];
        $subscriptionPlan = paypalPlan($planParams);
        $this->load->model('SubscriptionPaypalPlan');
        if (!$this->SubscriptionPaypalPlan->isExist($subscription_id))
        {
            $plan = ['subscription_id' => $subscription_id, 'plan_id' => $subscriptionPlan->id, 'product_id' => $subscriptionPlan->product_id, 'name' => $subscriptionPlan->name, 'status' => $subscriptionPlan->status, 'description' => $subscriptionPlan->description, 'usage_type' => $subscriptionPlan->usage_type, 'create_time' => $subscriptionPlan->create_time];
            $this->SubscriptionPaypalPlan->insert($plan);
        }
        $this->session->set_flashdata('paypal_subscription_id', $subscription_id);
        $this->session->set_flashdata('paypal_plan_id', $subscriptionPlan->id);
        // print_r($subscriptionPlan); exit;
        $result = [
            'paypal_subscription_id'=>$subscription_id,
            'paypal_plan_id'=>$subscriptionPlan->id
        ];
        echo json_encode($result);
        exit();
    }

    /**
     * @author Leo: provider subscription with paypal
     * @created: 
    */
    public function paypal_pro_subscription($subscription_id, $plan_id)
    {
        // $subscription_id = $this->session->flashdata('paypal_subscription_id');
        // $plan_id = $this->session->flashdata('paypal_plan_id');
        $paypalKeys = paypalKeys();
        $this->load->model('SubscriptionFee');
        $this->data['subscription'] = $this->SubscriptionFee->getData($subscription_id);
        $this->data['page'] = 'paypal_pro_subscription';
        $this->data['client_id'] = $paypalKeys["client_id"];
        $this->data['subscription_id'] = $subscription_id;
        $this->data['plan_id'] = $plan_id;
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }

    /**
     * @author Leo: business subscription with paypal
     * @created: 
    */
    public function paypal_business_subscription($subscription_id, $plan_id)
    {
        $paypalKeys = paypalKeys();
        $this->load->model('SubscriptionFee');
        $this->data['subscription'] = $this->SubscriptionFee->getData($subscription_id);
        $this->data['page'] = 'paypal_business_subscription';
        $this->data['client_id'] = $paypalKeys["client_id"];
        $this->data['subscription_id'] = $subscription_id;
        $this->data['plan_id'] = $plan_id;
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }

    /**
     * @author Leo: Paypal provider subscription success
     * @created: 
    */
    public function paypal_pro_subscription_success()
    {

        $plan_id = $_POST['plan_id'];
        $subscription_id = $_POST['subscription_id'];
        $subscriptionID = $_POST['subscriptionID'];
        // valid check
        $row = $this->db->select()->where('tokenid', $subscriptionID)->get('subscription_payment')->row_array();
        if (is_array($row) && count($row) > 0)
        {
            echo 0;
            exit;
        }

        $this->load->model('SubscriptionPaypalLog');
        $user_token = $this->session->userdata('chat_token');
        $userInfo = $this->api->get_token_info($user_token);
        $params = ['user_token' => $user_token, 'user_id' => $userInfo->id, 'user_type' => $userInfo->type, 'subscription_id' => $subscription_id, 'plan_id' => $plan_id, 'billingToken' => $_POST['billingToken'], 'facilitatorAccessToken' => $_POST['facilitatorAccessToken'], 'orderID' => $_POST['orderID'], 'paymentID' => $_POST['paymentID'], 'subscriptionID' => $_POST['subscriptionID'], ];
        $this->SubscriptionPaypalLog->insert($params);

        $token = $this->session->userdata('chat_token');
        $user_data['token'] = $token;
        $user_data['subscription_id'] = $subscription_id;
        $user_data['transaction_id'] = $subscriptionID;
        $user_data['payment_details'] = "paypal";

        $result = $this->api->subscription_success($user_data);

        $this->session->set_flashdata('success_message', 'Subscribed successfully');

        echo 1;
        exit;
    }

    public function user_settings()
    {
        $this->data['page'] = 'user_settings';
        $this->data['country'] = $this->db->select('id,country_name')->from('country_table')->get()->result_array();
        $this->data['city'] = $this->db->select('id,name')->from('city')->get()->result_array();
        $this->data['state'] = $this->db->select('id,name')->from('state')->get()->result_array();
        $this->data['user_address'] = $this->db->where('user_id', $this->session->userdata('id'))->get('user_address')->row_array();
        $this->data['profile'] = $this->service->get_profile($this->session->userdata('id'));
        $this->data['wallet'] = $this->api->get_wallet($this->session->userdata('chat_token'));
        $this->data['wallet_history'] = $this->api->get_wallet_history_info($this->session->userdata('chat_token'));
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }

    /**
     * @author Leo: User Security Setting
    */
    public function user_security()
    {
        $this->data['page'] = 'user_security';
        $this->load->model("User_model");

        // save security settings
        if ($this->input->post()) {
            $two_factor_auth = 0;
            if ($this->input->post("two_factor_auth") && $this->input->post("two_factor_auth") == "on") {
                // code...
                $two_factor_auth = 1;
            }

            $settings = [
                'user_id' => $this->session->userdata('id'),
                'two_factor_auth' => $two_factor_auth
            ];
            if ($this->User_model->updateUser($settings)) {
                // code...
                $this->session->set_flashdata("success_message", "Updated Security Setting Successfully !");
            }
        }
        
        $this->data['user_address'] = $this->db->where('user_id', $this->session->userdata('id'))->get('user_address')->row_array();
        $user_details = $this->User_model->get_user_details($this->session->userdata('id'));
        $this->data['user_details'] = $user_details;
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    
    public function userchangepassword()
    {
        $this->data['page'] = 'user_change_password';
        $this->data['profile'] = $this->service->get_profile($this->session->userdata('id'));
        //$this->data['wallet']=$this->api->get_wallet($this->session->userdata('chat_token'));
        //$this->data['wallet_history']=$this->api->get_wallet_history_info($this->session->userdata('chat_token'));
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }

    public function prochangepassword()
    {
        $this->data['page'] = 'provider_change_password';
        $user_id = $this->session->userdata('id');
        $this->data['profile'] = $this->db->where('id', $user_id)->get('providers')->row_array();
        //$this->data['wallet']=$this->api->get_wallet($this->session->userdata('chat_token'));
        //$this->data['wallet_history']=$this->api->get_wallet_history_info($this->session->userdata('chat_token'));
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }

    public function checkuserpwd()
    {

        $user_id = $this->session->userdata('id');
        $user = $this->db->where('id', $user_id)->where('password', md5($this->input->post('current_password')))->get('users')->row_array();
        if (!empty($user))
        {
            echo 1;
        }
        else
        {
            echo 0;
        }
    }

    public function checkproviderpwd()
    {
        $user_id = $this->session->userdata('id');
        $user = $this->db->where('id', $user_id)->where('password', md5($this->input->post('current_password')))->get('providers')->row_array();
        if (!empty($user))
        {
            echo 1;
        }
        else
        {
            echo 0;
        }
    }

    public function update_user_password()
    {
        if ($this->input->post())
        {
            removeTag($this->input->post());
            $user_id = $this->session->userdata('id');
            $confirm_password = $this->input->post('confirm_password');
            //$current_password = $this->input->post('current_password');
            $table_data = array(
                "password" => md5($confirm_password)
            );
            $this->db->where('id', $user_id);
            if ($this->db->update('users', $table_data))
            {
                $this->session->set_flashdata('success_message', 'Password changed successfully');
                redirect(base_url() . "change-password");

            }
            else
            {
                $this->session->set_flashdata('error_message', 'Something wrong, Please try again');
                redirect(base_url() . "change-password");
            }
        }
        // redirect(base_url('admin-profile'));
        
    }

    public function update_provider_password()
    {
        if ($this->input->post())
        {
            removeTag($this->input->post());
            $user_id = $this->session->userdata('id');
            $confirm_password = $this->input->post('confirm_password');
            //$current_password = $this->input->post('current_password');
            $table_data = array(
                "password" => md5($confirm_password)
            );
            $this->db->where('id', $user_id);
            if ($this->db->update('providers', $table_data))
            {
                $this->session->set_flashdata('success_message', 'Password changed successfully');
                redirect(base_url() . "provider-change-password");
            }
            else
            {
                $this->session->set_flashdata('error_message', 'Something wrong, Please try again');
                redirect(base_url() . "provider-change-password");
            }
        }
        // redirect(base_url('admin-profile'));
        
    }
    public function user_stipe_payment_process()
    {
        $currencyCode = get_user_currency();
        $params = stripeKeys();

        if (strtolower(['user_currency_code']) == 'jpy')
        {
            $required_amount = $_REQUEST['amount'];
        }
        else
        {
            $required_amount = $_REQUEST['amount'] * 100;
        }

        Stripe::setApiKey($params['secret_key']);
        $pubkey = $params['pub_key'];

        $invoiceid = time(); // Invoice ID
        $description = "Invoice #" . $invoiceid . " - " . $invoiceid;
        try
        {
            $charge = Stripe_Charge::create(array(
                "amount" => $required_amount,
                "currency" => $currencyCode['user_currency_code'],
                "source" => $_REQUEST['token'],
                "description" => $description
            ));

            if (isset($charge->card->address_zip_check) and $charge->card->address_zip_check == "fail")
            {
                $result = "Invalid Zip";
            }
            else if (isset($charge->card->address_line1_check) and $charge->card->address_line1_check == "fail")
            {
                $result = "Invalid Address";
            }
            else if (isset($charge->card->cvc_check) and $charge->card->cvc_check == "fail")
            {
                $result = "Invalid CVC";
            }
            else
            {
                $result = "success";
            }
            // Payment has succeeded, no exceptions were thrown or otherwise caught
            
        }
        catch(Stripe_CardError $e)
        {
            $error = $e->getMessage();
            $result = $e->getMessage();
        }
        catch(Stripe_InvalidRequestError $e)
        {
            $result = $e->getMessage();
        }
        catch(Stripe_AuthenticationError $e)
        {
            $result = $e->getMessage();
        }
        catch(Stripe_ApiConnectionError $e)
        {
            $result = $e->getMessage();
        }
        catch(Stripe_Error $e)
        {
            $result = $e->getMessage();
        }
        catch(Exception $e)
        {
            if ($e->getMessage() == "zip_check_invalid")
            {
                $result = $e->getMessage();
            }
            else if ($e->getMessage() == "address_check_invalid")
            {
                $result = $e->getMessage();
            }
            else if ($e->getMessage() == "cvc_check_invalid")
            {
                $result = $e->getMessage();
            }
            else
            {
                $result = $e->getMessage();
            }
        }
        if ($result == 'success')
        {
            $token = $this->session->userdata('chat_token');
            $user_info = $this->api->get_token_info($token);
            $wallet = $this->api->get_wallet($token);
            $curren_wallet = get_gigs_currency($wallet['wallet_amt'], $wallet['currency_code'], "USD");
            $user_currency = get_user_currency();
            if ($wallet['id'] == '')
            {
                $history_pay = array();
                $history_pay['token'] = $token;
                $history_pay['currency_code'] = $user_currency['user_currency_code'];
                $history_pay['user_provider_id'] = $user_info->id;
                $history_pay['type'] = $user_info->type;
                $history_pay['tokenid'] = $token;
                $history_pay['payment_detail'] = json_encode($_REQUEST);
                $history_pay['charge_id'] = $charge->id;
                $history_pay['transaction_id'] = $charge->id;
                $history_pay['exchange_rate'] = '';
                $history_pay['paid_status'] = 'paid';
                $history_pay['current_wallet'] = 0;
                $history_pay['credit_wallet'] = $_REQUEST['amount'];
                $history_pay['debit_wallet'] = 0;
                $history_pay['avail_wallet'] = $_REQUEST['amount'];
                $history_pay['reason'] = 'Added by Stripe';
                $history_pay['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('wallet_transaction_history', $history_pay);
                $insert_main_wallet = array();
                $insert_main_wallet['token'] = $token;
                $insert_main_wallet['currency_code'] = $user_currency['user_currency_code'];
                $insert_main_wallet['user_provider_id'] = $user_info->id;
                $insert_main_wallet['type'] = $user_info->type;
                $insert_main_wallet['wallet_amt'] = $_REQUEST['amount'];
                $insert_main_wallet['reason'] = 'Added by Stripe';;
                $this->db->insert('wallet_table', $insert_main_wallet);
            }
            else
            {
                $history_pay = array();
                $history_pay['token'] = $token;
                $history_pay['currency_code'] = $user_currency['user_currency_code'];
                $history_pay['user_provider_id'] = $user_info->id;
                $history_pay['type'] = $user_info->type;
                $history_pay['tokenid'] = $token;
                $history_pay['payment_detail'] = json_encode($_REQUEST);
                $history_pay['charge_id'] = $charge->id;
                $history_pay['transaction_id'] = $charge->id;
                $history_pay['exchange_rate'] = '';
                $history_pay['paid_status'] = 'paid';
                $history_pay['current_wallet'] = 0;
                $history_pay['credit_wallet'] = $_REQUEST['amount'];
                $history_pay['debit_wallet'] = 0;
                $history_pay['avail_wallet'] = $_REQUEST['amount'];
                $history_pay['reason'] = 'Added by Stripe';;
                $history_pay['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('wallet_transaction_history', $history_pay);

                $wallet_dat['currency_code'] = $wallet['currency_code'];
                $wallet_dat['wallet_amt'] = get_gigs_currency(($curren_wallet + $history_pay['credit_wallet']) , "USD", $wallet['currency_code']);
                $wallet_dat['updated_on'] = date('Y-m-d H:i:s');
                $WHERE = array(
                    'token' => $token
                );
                $result = $this->api->update_wallet($wallet_dat, $WHERE);
            }
            $this->session->set_flashdata('msg_success', 'Amount added to wallet successfully');
            echo 'success';
        }
        else
        {
            echo $result;
        }
        exit;
    }

    public function user_stripe_sub_payment_process()
    {
        $this->db->select('*');
        $this->db->where('id', $_REQUEST['subscription_id']);
        $subscription = $this->db->get('subscription_fee')->row_array();
        $currencyCode = $subscription['currency_code'];

        if (strtolower($currencyCode) == 'jpy')
        {
            $required_amount = $subscription['fee'];
        }
        else
        {
            $required_amount = $subscription['fee'] * 100;
        }

        $params = stripeKeys();
        Stripe::setApiKey($params['secret_key']);
        $pubkey = $params['pub_key'];

        $invoiceid = time(); // Invoice ID
        $description = "Invoice #" . $invoiceid . " - " . $invoiceid;
        try
        {
            $charge = Stripe_Charge::create(array(
                "amount" => $required_amount,
                "currency" => $currencyCode,
                "source" => $_REQUEST['token'],
                "description" => $description
            ));

            if (isset($charge->card->address_zip_check) and $charge->card->address_zip_check == "fail")
            {
                $result = "Invalid Zip";
            }
            else if (isset($charge->card->address_line1_check) and $charge->card->address_line1_check == "fail")
            {
                $result = "Invalid Address";
            }
            else if (isset($charge->card->cvc_check) and $charge->card->cvc_check == "fail")
            {
                $result = "Invalid CVC";
            }
            else
            {
                $result = "success";
            }
            // Payment has succeeded, no exceptions were thrown or otherwise caught
            
        }
        catch(Stripe_CardError $e)
        {
            $error = $e->getMessage();
            $result = $e->getMessage();
        }
        catch(Stripe_InvalidRequestError $e)
        {
            $result = $e->getMessage();
        }
        catch(Stripe_AuthenticationError $e)
        {
            $result = $e->getMessage();
        }
        catch(Stripe_ApiConnectionError $e)
        {
            $result = $e->getMessage();
        }
        catch(Stripe_Error $e)
        {
            $result = $e->getMessage();
        }
        catch(Exception $e)
        {
            if ($e->getMessage() == "zip_check_invalid")
            {
                $result = $e->getMessage();
            }
            else if ($e->getMessage() == "address_check_invalid")
            {
                $result = $e->getMessage();
            }
            else if ($e->getMessage() == "cvc_check_invalid")
            {
                $result = "Invalid card CVV. Please try again.";
            }
            else
            {
                $result = $e->getMessage();
            }
        }
        if ($result == 'success')
        {
            $token = $this->session->userdata('chat_token');
            $user_info = $this->api->get_token_info($token);
            $wallet = $this->api->get_wallet($token);
            $curren_wallet = get_gigs_currency($wallet['wallet_amt'], $wallet['currency_code'], "USD");
            $user_currency = get_user_currency();

            $user_data['token'] = $token;
            $user_data['subscription_id'] = $_REQUEST['subscription_id'];
            $user_data['transaction_id'] = $charge->id;

            $result = $this->api->subscription_success($user_data);
            $user_id = $this->api->get_user_id_using_token($token);

            $this->db->select('subscription_id');

            $this->db->where('subscriber_id', $user_id);

            $subscription = $this->db->get('subscription_details')->row_array();

            if (!empty($subscription))
            {

                $id = $subscription['subscription_id'];

                $this->db->select('id,subscription_name');

                $this->db->where('id', $id);

                $subscription = $this->db->get('subscription_fee')->row_array();

                $subscribed_user = 1;

                $subscribed_msg = $subscription['subscription_name'];
            }
            else
            {

                $subscribed_user = 0;

                $subscribed_msg = 'Free';
            }
            $this->session->set_flashdata('msg_success', 'Subscribed successfully');
            echo 'success';
        }
        else
        {
            echo $result;
        }
        exit;
    }

    public function user_pay_by_stripe($amount = 0)
    {
        $this->data['page'] = 'user_stripe_payment';
        $this->data['amount'] = $amount;
        $this->data['currency'] = get_user_currency();
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    public function user_subscription_pay_by_stripe($subscription_id = 0)
    {

        $this->db->select('*');
        $this->db->where('id', $subscription_id);
        $subscription = $this->db->get('subscription_fee')->row_array();

        $this->data['page'] = 'user_stripe_subscription_payment';
        $this->data['amount'] = $subscription['fee'];
        $this->data['subscription_id'] = $subscription_id;
        $this->data['currency'] = $subscription['currency_code'];
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    public function user_wallet_payment()
    {
        $token = $this->session->userdata('chat_token');
        $user_info = $this->api->get_token_info($token);
        $wallet = $this->api->get_wallet($token);
        $curren_wallet = get_gigs_currency($wallet['wallet_amt'], $wallet['currency_code'], "USD");
        $user_currency = get_user_currency();
        if ($wallet['id'] == '')
        {
            $history_pay = array();
            $history_pay['token'] = $token;
            $history_pay['currency_code'] = $user_currency['user_currency_code'];
            $history_pay['user_provider_id'] = $user_info->id;
            $history_pay['type'] = $user_info->type;
            $history_pay['tokenid'] = $token;
            $history_pay['payment_detail'] = json_encode($_REQUEST);
            $history_pay['charge_id'] = $_REQUEST['paymentID'];
            $history_pay['transaction_id'] = $_REQUEST['paymentID'];
            $history_pay['exchange_rate'] = '';
            $history_pay['paid_status'] = 'paid';
            $history_pay['current_wallet'] = 0;
            $history_pay['credit_wallet'] = $_REQUEST['amount'];
            $history_pay['debit_wallet'] = 0;
            $history_pay['avail_wallet'] = $_REQUEST['amount'];
            $history_pay['reason'] = TOPUP;
            $history_pay['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('wallet_transaction_history', $history_pay);
            $insert_main_wallet = array();
            $insert_main_wallet['token'] = $token;
            $insert_main_wallet['currency_code'] = $user_currency['user_currency_code'];
            $insert_main_wallet['user_provider_id'] = $user_info->id;
            $insert_main_wallet['type'] = $user_info->type;
            $insert_main_wallet['wallet_amt'] = $_REQUEST['amount'];
            $insert_main_wallet['reason'] = TOPUP;
            $this->db->insert('wallet_table', $insert_main_wallet);
        }
        else
        {
            $history_pay = array();
            $history_pay['token'] = $token;
            $history_pay['currency_code'] = $user_currency['user_currency_code'];
            $history_pay['user_provider_id'] = $user_info->id;
            $history_pay['type'] = $user_info->type;
            $history_pay['tokenid'] = $token;
            $history_pay['payment_detail'] = json_encode($_REQUEST);
            $history_pay['charge_id'] = $_REQUEST['paymentID'];
            $history_pay['transaction_id'] = $_REQUEST['paymentID'];
            $history_pay['exchange_rate'] = '';
            $history_pay['paid_status'] = 'paid';
            $history_pay['current_wallet'] = 0;
            $history_pay['credit_wallet'] = $_REQUEST['amount'];
            $history_pay['debit_wallet'] = 0;
            $history_pay['avail_wallet'] = $_REQUEST['amount'];
            $history_pay['reason'] = TOPUP;
            $history_pay['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('wallet_transaction_history', $history_pay);

            $wallet_dat['currency_code'] = $wallet['currency_code'];
            $wallet_dat['wallet_amt'] = get_gigs_currency(($curren_wallet + $history_pay['credit_wallet']) , "USD", $wallet['currency_code']);
            $wallet_dat['updated_on'] = date('Y-m-d H:i:s');
            $WHERE = array(
                'token' => $token
            );
            $result = $this->api->update_wallet($wallet_dat, $WHERE);
        }
        $this->session->set_flashdata('msg_success', 'Amount added to wallet successfully');
        redirect('user-wallet');
    }

    public function user_wallet()
    {
        $this->data['page'] = 'user_wallet';
        $user_id = $this->session->userdata('id');
        $this->data['wallet'] = $this->api->get_wallet($this->session->userdata('chat_token'));
        $this->data['wallet_history'] = $this->api->get_wallet_history_info($this->session->userdata('chat_token'));
        $this->data['user_details'] = $user = $this->db->where('users.id', $user_id)->join('user_address', 'user_address.user_id=users.id', 'left')->get('users')->row_array();
        $this->data['paypal_gateway'] = settingValue('paypal_gateway');
        $this->data['braintree_key'] = settingValue('braintree_key');
        $razor_option = settingValue('razor_option');

        if ($razor_option == 1)
        {
            $this->data['razorpay_apikey'] = settingValue('razorpay_apikey');
            $this->data['razorpay_apisecret'] = settingValue('razorpay_apisecret');
        }
        else if ($razor_option == 2)
        {
            $this->data['razorpay_apikey'] = settingValue('live_razorpay_apikey');
            $this->data['razorpay_apisecret'] = settingValue('live_razorpay_secret_key');
        }

        if (!empty($user['state_id']))
        {
            $this->data['states'] = $this->db->where('id', $user['state_id'])->get('state')->row()->name;
        }
        if (!empty($user['state_id']))
        {
            $this->data['state'] = $this->db->where('id', $user['state_id'])->get('state')->row()->name;
        }
        if (!empty($user['country_id']))
        {
            $this->data['country'] = $this->db->where('id', $user['country_id'])->get('country_table')->row()->country_code;
        }
        if (!empty($user['city_id']))
        {
            $this->data['city'] = $this->db->where('id', $user['city_id'])->get('city')->row()->name;
        }
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }

    public function paytab_payment()
    {
        //echo "hi";exit;
        removeTag($this->input->get());
        $params = $this->input->get(); //
        if (!empty($params))
        {
            $amount = $params['wallet_amt'];
            $user_id = $this->session->userdata('id');
            $user = $this->db->where('id', $user_id)->get('users')->row_array();
            $user_name = $user['name'];
            $user_token = $user['token'];
            $currency_type = $user['currency_code'];
            $this->paytabs_payments($amount, $user_id, $user_name, $currency_type, $user_token);
        }
    }

    public function paytabs_payments($amount, $user_id, $g_name, $currency_type, $user_token)
    {

        $paytab_option = settingValue('paytab_option');
        if ($paytab_option == 1)
        {
            $paytabemail = settingValue('sandbox_email');
            $paytabsecret = settingValue('sandbox_secretkey');
        }
        else if ($paytab_option == 2)
        {
            $paytabemail = settingValue('email');
            $paytabsecret = settingValue('secretkey');
        }

        //echo "<pre>";print_r($paytabsecret);exit;
        // $this->data['razorpay_apikey']=settingValue('razorpay_apikey');
        // $this->data['razorpay_apisecret']=settingValue('razorpay_apisecret');
        $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        $USERID = $this->session->userdata('id');
        $userdetails = $this->db->query('select m.email,m.name,m.mobileno,a.address,a.country_id,a.state_id,a.city_id,a.pincode,ci.name as city ,c.country_code,c.country_name,s.name as state_name from users as m 
		     LEFT JOIN user_address as a on a.user_id=m.id
			 LEFT JOIN city as ci on ci.id=a.city_id
		     LEFT JOIN country_table as c on c.id=a.country_id
		     LEFT JOIN state as s on s.id=a.state_id
		     WHERE m.id=' . $USERID . '')->row_array();

        $details = array(
            "merchant_email" => $paytabemail,
            "secret_key" => $paytabsecret,
            "site_url" => base_url($this->data['theme']) ,
            "return_url" => base_url($this->data['theme'] . '/dashboard/paytabs_success/') ,
            "title" => $g_name,
            "cc_first_name" => $userdetails['name'],
            "cc_last_name" => "Not Mentioned",
            "cc_phone_number" => !empty($userdetails['mobileno']) ? $userdetails['mobileno'] : '0000',
            "phone_number" => !empty($userdetails['mobileno']) ? $userdetails['mobileno'] : '0000',
            "email" => $userdetails['email'],
            "products_per_title" => $g_name,
            "unit_price" => $amount,
            "quantity" => "1",
            "other_charges" => "0",
            "amount" => $amount,
            "discount" => "0",
            "currency" => $currency_type,
            "reference_no" => $USERID,
            "ip_customer" => $ip,
            "ip_merchant" => $ip,
            "csrf_token_name" => $this->security->get_csrf_hash() ,
            //$this->security->get_csrf_token_name()=>$this->security->get_csrf_hash(),
            "billing_address" => !empty($userdetails['address']) ? $userdetails['address'] : 'Not Mentioned',
            "city" => !empty($userdetails['city']) ? $userdetails['city'] : 'Not Mentioned',
            "state" => !empty($userdetails['state_name']) ? $userdetails['state_name'] : 'Not Mentioned',
            "postal_code" => !empty($userdetails['pincode']) ? $userdetails['pincode'] : 'Not Mentioned',
            "country" => !empty($userdetails['country_code']) ? $userdetails['country_code'] : 'IND',
            "shipping_first_name" => $userdetails['name'],
            "shipping_last_name" => "Not Mentioned",
            "address_shipping" => !empty($userdetails['address']) ? $userdetails['address'] : 'Not Mentioned',
            "state_shipping" => !empty($userdetails['state_name']) ? $userdetails['state_name'] : 'Not Mentioned',
            "city_shipping" => !empty($userdetails['city']) ? $userdetails['city'] : 'Not Mentioned',
            "postal_code_shipping" => !empty($userdetails['pincode']) ? $userdetails['pincode'] : 'Not Mentioned',
            "country_shipping" => !empty($userdetails['country_code']) ? $userdetails['country_code'] : 'IND',
            "msg_lang" => "English",
            "cms_with_version" => "CodeIgniter 3.1.9"
        );

        // echo "<pre>";print_r($details);exit;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytabs.com/apiv2/create_pay_page");
        curl_setopt($ch, CURLOPT_POST, 1);
        // In real life you should use something like:
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($details));
        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        if (curl_errno($ch))
        {
            $error_msg = curl_error($ch);
        }
        curl_close($ch);
        $pay_tabs_response = json_decode($response);
        if (!empty($pay_tabs_response->payment_url))
        {
            redirect(urldecode($pay_tabs_response->payment_url));
        }
        else
        {
            $message = (!empty($this->user_language[$this->user_selected]['lg_something_went_wrong'])) ? $this->user_language[$this->user_selected]['lg_something_went_wrong'] : $this->default_language['en']['lg_something_went_wrong'];
            $this->session->set_flashdata('msg_error', $message);
            redirect('user-wallet');
        }
    }
    public function paytabs_success()
    {
        //print_r($this->security->get_csrf_token_name());//exit;
        //print_r($this->security->get_csrf_hash());exit;
        // echo "hi";exit;
        //removeTag($this->input->post());
        $paytab_option = settingValue('paytab_option');
        if ($paytab_option == 1)
        {

            $paytabemail = settingValue('sandbox_email');
            $paytabsecret = settingValue('sandbox_secretkey');
        }
        else if ($paytab_option == 2)
        {
            $paytabemail = settingValue('email');
            $paytabsecret = settingValue('secretkey');
        }
        $paytabInfo = $this->input->post(); //echo "<pre>";print_r($this->input->post());exit;
        if (!empty($paytabInfo))
        {
            $details = array(
                "merchant_email" => $paytabemail,
                "secret_key" => $paytabsecret,
                "payment_reference" => $paytabInfo['payment_reference']
            );

            //echo "<pre>";print_r($details);exit;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.paytabs.com/apiv2/verify_payment");
            curl_setopt($ch, CURLOPT_POST, 1);
            // In real life you should use something like:
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($details));
            // Receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            $pay_tabs_response = json_decode($response);
            //echo "<pre>";print_r($pay_tabs_response);exit;
            if ($pay_tabs_response->response_code == '100')
            {
                if (!empty($pay_tabs_response->reference_no))
                {
                    $user = $this->Stripe_model->get_user_info($pay_tabs_response->reference_no);
                    $user_id = $pay_tabs_response->reference_no;
                    $txn_id = $pay_tabs_response->transaction_id;
                    $amt = $pay_tabs_response->amount;
                    $result = $this->Stripe_model->user_wallet_history_flow($pay_tabs_response->reference_no, $txn_id, $amt);
                    if ($result == true)
                    {
                        $message = (!empty($this->user_language[$this->user_selected]['lg_wallet_amount_add_wallet'])) ? $this->user_language[$this->user_selected]['lg_wallet_amount_add_wallet'] : $this->default_language['en']['lg_wallet_amount_add_wallet'];
                        $this->session->set_flashdata('msg_success', $message);
                        redirect('user-wallet');
                    }
                    else
                    {
                        $message = (!empty($this->user_language[$this->user_selected]['lg_something_went_wrong'])) ? $this->user_language[$this->user_selected]['lg_something_went_wrong'] : $this->default_language['en']['lg_something_went_wrong'];
                        $this->session->set_flashdata('msg_error', $message);
                        redirect('user-wallet');
                    }
                }
            }
            else
            {
                $message = (!empty($this->user_language[$this->user_selected]['lg_something_went_wrong'])) ? $this->user_language[$this->user_selected]['lg_something_went_wrong'] : $this->default_language['en']['lg_something_went_wrong'];
                $this->session->set_flashdata('msg_error', $message);
                redirect('user-wallet');
            }
        }
    }

    public function user_payment()
    {
        $this->data['page'] = 'user_payment';

        $this->data['services'] = $this->db->where('b.user_id', $this->session->userdata('id'))
            // ->where_in('b.status', [5, 6, 7])
            ->from('book_service as b')
            ->join('users as u', 'u.id=b.user_id')
            ->join('services s', 's.id=b.service_id')
            ->select('b.*,b.currency_code as currency_code1,u.*,s.service_title,s.service_image,b.status as booking_status')
            ->order_by('b.id', 'desc')->get()->result_array();

        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    public function user_accountdetails()
    {
        $this->data['page'] = 'user_accountdetails';

        $this->data['wallet'] = $this->api->get_wallet($this->session->userdata('chat_token'));
        $this->data['wallet_history'] = $this->api->get_wallet_history_info($this->session->userdata('chat_token'));
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    /*cropping*/
    function prf_crop($av_src, $av_data, $av_file, $req_height, $req_width, $table_name, $redirect)
    {

        $directoryName = 'uploads/profile_img/';
        //Check if the directory already exists.
        if (!is_dir($directoryName))
        {
            //Directory does not exist, so lets create it.
            mkdir($directoryName, 0755);
        }

        if (!empty($av_src) && !empty($av_data) && !empty($av_file))
        {
            $av_src = $av_src;
            $av_data = $av_data;
            $av_file = $av_file;
            $av_file['name'] = str_replace(' ', '-', $av_file['name']);
            $src = $directoryName . $av_file['name'];
            $imageFileType = pathinfo($src, PATHINFO_EXTENSION);
            $info = pathinfo($src);
            $file_name = basename($src, '.' . $info['extension']);
            $src2 = $directoryName . $av_file['name'];
            move_uploaded_file($av_file['tmp_name'], $src2);
            $image_name = str_replace(' ', '-', $av_file['name']);
            $new_name1 = time() . '.' . $imageFileType;
            $image1 = $this->prf_crop_call($image_name, $av_data, $new_name1, $directoryName, 500, 250);
            $cropfliename = $new_name1;
            $data['success'] = 'Y';
        }
        else
        {
            $new_name1 = '';
            $imageFileType = '';
            $info = '';
            $cropfliename = '';
            $data['success'] = 'n';
        }
        $data['full_fliename'] = $new_name1;
        $data['image_extension'] = $imageFileType;
        $data['image_info'] = $info;
        $data['Date'] = date('d/m/y');
        $data['cropped_fliepath'] = 'uploads/profile_img/' . $cropfliename;
        $table_data['profile_img'] = 'uploads/profile_img/' . $cropfliename;

        $id = $this->session->userdata('id');

        $this->db->where('id', $id);
        if ($this->db->update($table_name, $table_data))
        {

        }
        else
        {

        }
        return $data;

    }

    function prf_crop_call($image_name, $av_data, $new_name, $directoryName, $t_width, $t_height)
    {
        $w = $av_data['width'];
        $h = $av_data['height'];
        $x1 = $av_data['x'];
        $y1 = $av_data['y'];
        list($imagewidth, $imageheight, $imageType) = getimagesize($directoryName . $image_name);
        $imageType = image_type_to_mime_type($imageType);
        $ratio = ($t_width / $w);
        $nw = ceil($w * $ratio);
        $nh = ceil($h * $ratio);
        $newImage = imagecreatetruecolor($nw, $nh);
        switch ($imageType)
        {
            case "image/gif":
                $source = imagecreatefromgif($directoryName . $image_name);
            break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                $source = imagecreatefromjpeg($directoryName . $image_name);
            break;
            case "image/png":
            case "image/x-png":
                $source = imagecreatefrompng($directoryName . $image_name);
            break;
        }
        imagecopyresampled($newImage, $source, 0, 0, $x1, $y1, $nw, $nh, $w, $h);
        switch ($imageType)
        {
            case "image/gif":
                imagegif($newImage, $directoryName . $new_name);
            break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                imagejpeg($newImage, $directoryName . $new_name, 100);
            break;
            case "image/png":
            case "image/x-png":
                imagepng($newImage, $directoryName . $new_name);
            break;
        }

    }

    public function profile_cropping()
    {
        extract($_POST);
        if (!empty($_FILES['profile_img']))
        {
            $av_data = json_decode($_POST['avatar_data'], true);
            $av_file = $_FILES['profile_img'];
            $av_src = $av_file['name'];
            $req_height = 250;

            $req_width = 250;
            $output = $this->prf_crop($av_src, $av_data, $av_file, $req_height, $req_width, $table_name, $redirect);

            echo json_encode($output);
            die();
        }
    }

    public function update_user()
    {
        if (!empty($_POST))
        {
            removeTag($this->input->post());
            $uploaded_file_name = '';
            if (isset($_FILES) && isset($_FILES['profile_img']['name']) && !empty($_FILES['profile_img']['name']))
            {
                $uploaded_file_name = $_FILES['profile_img']['name'];
                $uploaded_file_name_arr = explode('.', $uploaded_file_name);
                $filename = isset($uploaded_file_name_arr[0]) ? $uploaded_file_name_arr[0] : '';
                $this->load->library('common');
                $upload_sts = $this->common->global_file_upload('uploads/profile_img/', 'profile_img', time() . $filename);
                if (isset($upload_sts['success']) && $upload_sts['success'] == 'y')
                {
                    $uploaded_file_name = $upload_sts['data']['file_name'];
                    if (!empty($uploaded_file_name))
                    {
                        $image_url = 'uploads/profile_img/' . $uploaded_file_name;
                        $table_data['profile_img'] = $this->image_resize(100, 100, $image_url, $filename);
                    }
                }
            }
            $id = $this->session->userdata('id');
            $table_data['mobileno'] = $this->input->post('mobileno');
            if (!empty($this->input->post('dob')))
            {
                $table_data['dob'] = date('Y-m-d', strtotime($this->input->post('dob')));
            }
            else
            {
                $table_data['dob'] = NULL;
            }

            $table_data['postal_code'] = $this->input->post('pincode');
            
            $this->db->where('id', $id);

            // Leo: check profile fill up status
            if (!empty($_POST['dob']) && !empty($_POST['address']) && !empty($_POST['country_id']) && !empty($_POST['state_id']) && !empty($_POST['city_id']) && !empty($_POST['pincode'])) {
                // code...
                $table_data['fill_up_profile'] = 1;
                $this->session->set_userdata('fill_up_profile', 1);
            }
            if ($this->db->update('users', $table_data))
            {
                $table_datas['address'] = $_POST['address'];
                if (!empty($_POST['state_id']))
                {
                    $table_datas['state_id'] = $_POST['state_id'];
                }
                if (!empty($_POST['city_id']))
                {
                    $table_datas['city_id'] = $_POST['city_id'];
                }
                if (!empty($_POST['country_id']))
                {
                    $table_datas['country_id'] = $_POST['country_id'];
                }
                if (!empty($_POST['pincode']))
                {
                    $table_datas['pincode'] = $_POST['pincode'];
                }

                $user_count = $this->db->where('user_id', $id)->count_all_results('user_address');

                if (count($table_datas) > 0)
                {
                    if ($user_count == 1)
                    {
                        $this->db->where('user_id', $id);
                        $this->db->update('user_address', $table_datas);
                    }
                    else
                    {
                        $table_datas['user_id'] = $id;
                        $this->db->insert('user_address', $table_datas);
                    }
                    $this->session->set_flashdata('success_message', 'Profile updated successfully');
                    redirect(base_url() . "user-settings");
                }
                else
                {
                    $this->session->set_flashdata('error_message', 'Something wrong, Please try again');
                    redirect(base_url() . "user-settings");
                }
            }
        }
    }

    public function update_account()
    {

        $id = $this->session->userdata('id');
        $table_data['account_holder_name'] = $this->input->post('account_holder_name');
        $table_data['account_number'] = $this->input->post('account_number');
        $table_data['account_iban'] = $this->input->post('account_iban');
        $table_data['bank_name'] = $this->input->post('bank_name');
        $table_data['bank_address'] = $this->input->post('bank_address');
        $table_data['sort_code'] = $this->input->post('sort_code');
        $table_data['routing_number'] = $this->input->post('routing_number');
        $table_data['account_ifsc'] = $this->input->post('account_ifsc');

        $this->db->where('id', $id);
        $result = $this->db->update('users', $table_data);
        if ($result)
        {
            $this->session->set_flashdata('success_message', 'Account details updated successfully');
        }
        else
        {
            $this->session->set_flashdata('error_message', 'Something wrong, Please try again');
        }

        echo json_encode($result);

    }

    public function update_account_provider()
    {

        $id = $this->session->userdata('id');
        $table_data['account_holder_name'] = $this->input->post('account_holder_name');
        $table_data['account_number'] = $this->input->post('account_number');
        $table_data['account_iban'] = $this->input->post('account_iban');
        $table_data['bank_name'] = $this->input->post('bank_name');
        $table_data['bank_address'] = $this->input->post('bank_address');
        $table_data['sort_code'] = $this->input->post('sort_code');
        $table_data['routing_number'] = $this->input->post('routing_number');
        $table_data['account_ifsc'] = $this->input->post('account_ifsc');

        $this->db->where('id', $id);
        $result = $this->db->update('providers', $table_data);
        if ($result)
        {
            $data = array(
                'tab_ctrl' => 4,
                'success_message' => 'Account details updated successfully'
            );
            $this->session->set_flashdata($data);

        }
        else
        {
            $data = array(
                'tab_ctrl' => 4,
                'success_message' => 'Something wrong, Please try again'
            );

            $this->session->set_flashdata($data);
        }

        echo json_encode($result);
    }

    public function update_provider()
    {
        $uploaded_file_name = '';
        $id = $this->session->userdata('id');
        removeTag($this->input->post());
        $table_data['category'] = $this->input->post('categorys');
        $table_data['subcategory'] = $this->input->post('subcategorys');
        $table_data['mobileno'] = $this->input->post('mobileno');
        if (!empty($this->input->post('dob')))
        {
            $table_data['dob'] = date('Y-m-d', strtotime($this->input->post('dob')));
        }
        else
        {
            $table_data['dob'] = NULL;
        }
        $table_data['site_link'] = $this->input->post('site_link');

        $this->db->where('id', $id);
        if ($this->db->update('providers', $table_data))
        {
            $table_datas['address'] = $_POST['address'];
            if (!empty($_POST['state_id']))
            {
                $table_datas['state_id'] = $_POST['state_id'];
            }
            if (!empty($_POST['city_id']))
            {
                $table_datas['city_id'] = $_POST['city_id'];
            }
            if (!empty($_POST['country_id']))
            {
                $table_datas['country_id'] = $_POST['country_id'];
            }
            if (!empty($_POST['pincode']))
            {
                $table_datas['pincode'] = $_POST['pincode'];
            }

            $provider_count = $this->db->where('provider_id', $id)->count_all_results('provider_address');

            if (count($table_datas) > 0)
            {
                if ($provider_count == 1)
                {
                    $this->db->where('provider_id', $id);
                    $this->db->update('provider_address', $table_datas);
                }
                else
                {
                    $table_datas['provider_id'] = $id;
                    $this->db->insert('provider_address', $table_datas);
                }
            }

            $data = array(
                'tab_ctrl' => 1,
                'success_message' => 'Profile updated successfully'
            );
            $this->session->set_flashdata($data);
            redirect(base_url() . "provider-settings");
        }
        else
        {
            $data = array(
                'tab_ctrl' => 1,
                'error_message' => 'Something wrong, Please try again'
            );
            $this->session->set_flashdata($data);
            redirect(base_url() . "provider-settings");
        }

    }

    public function user_reviews()
    {
        $this->data['page'] = 'user_reviews';
        $this->db->select("r.*,u.profile_img,u.name,s.service_image,s.service_title");
        $this->db->from('rating_review r');
        $this->db->join('users u', 'u.id = r.user_id', 'LEFT');
        $this->db->join('services s', 's.id = r.service_id', 'LEFT');
        $this->db->where(array(
            'r.user_id' => $this->session->userdata('id') ,
            'r.status' => 1
        ))->order_by('r.id', 'desc');
        $this->data['reviews'] = $this->db->get()->result_array();
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    public function provider_reviews()
    {
        $this->data['page'] = 'provider_reviews';
        $this->db->select("r.*,u.profile_img,u.name,s.service_image,s.service_title");
        $this->db->from('rating_review r');
        $this->db->join('users u', 'u.id = r.user_id', 'LEFT');
        $this->db->join('services s', 's.id = r.service_id', 'LEFT');
        $this->db->where(array(
            'r.provider_id' => $this->session->userdata['id'],
            'r.status' => 1
        ))->order_by('r.id', 'desc');
        $this->data['reviews'] = $this->db->get()->result_array();
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    public function provider_settings()
    {
        $this->data['page'] = 'provider_settings';
        $this->data['country'] = $this->db->select('id,country_name')->from('country_table')->order_by('country_name', 'asc')->get()->result_array();
        $this->data['currency'] = $this->db->select('id,currency_code')->from('currency_rate')->get()->result_array();
        $this->data['city'] = $this->db->select('id,name')->from('city')->get()->result_array();
        $this->data['state'] = $this->db->select('id,name')->from('state')->get()->result_array();
        $this->data['wallet'] = $this->api->get_wallet($this->session->userdata('chat_token'));
        $this->data['wallet_history'] = $this->api->get_wallet_history_info($this->session->userdata('chat_token'));
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    public function provider_wallet()
    {
        $this->data['page'] = 'provider_wallet';
        $this->data['wallet'] = $this->api->get_wallet($this->session->userdata('chat_token'));
        $this->data['wallet_history'] = $this->api->get_wallet_history_info($this->session->userdata('chat_token'));
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    public function provider_payment()
    {
        $this->data['page'] = 'provider_payment';
        $this->data['services'] = $this->db->where('b.provider_id', $this->session->userdata('id'))->where_in('b.status', [5, 6, 7])->from('book_service as b')->join('users as u', 'u.id=b.user_id')->join('services s', 's.id=b.service_id')->order_by('b.id', 'desc')->select('b.*,u.*,s.service_title,s.service_image,b.status as payment_status')->get()->result_array();

        $this->load->vars($this->data);

        $this->load->view($this->data['theme'] . '/template');
    }
    public function provider_subscription()
    {
        $user_id = $this->session->userdata('id');
        $this->data['page'] = 'provider_subscription';
        $this->data['user_details'] = $user = $this->db->where('providers.id', $user_id)->join('provider_address', 'provider_address.provider_id=providers.id', 'left')->get('providers')->row_array();
        $this->data['paypal_gateway'] = settingValue('paypal_gateway');
        $this->data['braintree_key'] = settingValue('braintree_key');
        $razor_option = settingValue('razor_option');

        //echo "<pre>";print_r($razorpay_apikey);exit;
        if ($razor_option == 1)
        {
            $this->data['razorpay_apikey'] = settingValue('razorpay_apikey');
            $this->data['razorpay_apisecret'] = settingValue('razorpay_apisecret');
        }
        else if ($razor_option == 2)
        {
            $this->data['razorpay_apikey'] = settingValue('live_razorpay_apikey');
            $this->data['razorpay_apisecret'] = settingValue('live_razorpay_secret_key');
        }

        $this->data['wallet'] = $this->api->get_wallet($this->session->userdata('chat_token'));
        $this->data['wallet_history'] = $this->api->get_wallet_history_info($this->session->userdata('chat_token'));
        $this->load->vars($this->data);

        $this->load->view($this->data['theme'] . '/template');
    }

    // Leo: create business subscription
    public function business_subscription()
    {
        $user_id = $this->session->userdata('id');
        $this->data['page'] = 'business_subscription';
        $this->data['user_details'] = $user = $this->db->where('users.id', $user_id)->join('user_address', 'user_address.user_id=users.id', 'left')->get('users')->row_array();
        $this->data['paypal_gateway'] = settingValue('paypal_gateway');
        $this->data['braintree_key'] = settingValue('braintree_key');
        $razor_option = settingValue('razor_option');

        if ($razor_option == 1)
        {
            $this->data['razorpay_apikey'] = settingValue('razorpay_apikey');
            $this->data['razorpay_apisecret'] = settingValue('razorpay_apisecret');
        }
        else if ($razor_option == 2)
        {
            $this->data['razorpay_apikey'] = settingValue('live_razorpay_apikey');
            $this->data['razorpay_apisecret'] = settingValue('live_razorpay_secret_key');
        }

        $this->data['wallet'] = $this->api->get_wallet($this->session->userdata('chat_token'));
        $this->data['wallet_history'] = $this->api->get_wallet_history_info($this->session->userdata('chat_token'));
        $this->load->vars($this->data);

        $this->load->view($this->data['theme'] . '/template');
    }

    public function provider_availability()
    {
        $this->data['page'] = 'provider_availability';
        $this->data['wallet'] = $this->api->get_wallet($this->session->userdata('chat_token'));
        $this->data['wallet_history'] = $this->api->get_wallet_history_info($this->session->userdata('chat_token'));
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    public function provider_accountdetails()
    {
        $this->data['page'] = 'provider_accountdetails';
        $this->data['wallet'] = $this->api->get_wallet($this->session->userdata('chat_token'));
        $this->data['wallet_history'] = $this->api->get_wallet_history_info($this->session->userdata('chat_token'));
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    public function provider_bookings()
    {
        $data = array();

        $this->data['page'] = 'provider_bookings';
        $provider_id = $this->session->userdata('id');
        $status = $this->input->post('status');
        $sortBy = $this->input->post('sortBy');

        if (!empty($status))
        {
            $conditions['where']['b.status'] = $status;
        }

        // $conditions['where']['b.category_id']=$status;
        //     $conditions['where']['b.confirm_status']="0";
        //        $conditions['where']['b.emp_id']=seesion user id;
        

        $conditions['returnType'] = 'count';
        $totalRec = $this->booking->getRows($conditions);

        // Pagination configuration
        $config['target'] = '#dataList';
        $config['base_url'] = base_url('user/dashboard/ajaxPaginationData');
        $config['total_rows'] = $totalRec;
        $config['per_page'] = $this->perPage;

        // Initialize pagination library
        $this->ajax_pagination->initialize($config);

        // Get records
        $conditions = array(
            'limit' => $this->perPage
        );
        $this->data['all_bookings'] = $this->booking->getRows($conditions);

        // Load the list page view
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    function ajaxPaginationData()
    {
        // Define offset
        $page = $this->input->post('page');
        if (!$page)
        {
            $offset = 0;
        }
        else
        {
            $offset = $page;
        }

        // Set conditions for search and filter
        $status = $this->input->post('status');
        $sortBy = $this->input->post('sortBy');

        if (!empty($status))
        {
            $conditions['where']['b.status'] = $status;
        }

        // Get record count
        $conditions['returnType'] = 'count';

        $totalRec = $this->booking->getRows($conditions);

        // Pagination configuration
        $config['target'] = '#dataList';
        $config['base_url'] = base_url('user/dashboard/ajaxPaginationData');
        $config['total_rows'] = $totalRec;
        $config['per_page'] = $this->perPage;

        // Initialize pagination library
        $this->ajax_pagination->initialize($config);

        // Get records
        $conditions = array(
            'start' => $offset,
            'limit' => $this->perPage
        );
        if (!empty($status))
        {
            $conditions['where']['b.status'] = $status;
        }

        $this->data['all_bookings'] = $this->booking->getRows($conditions);

        // Load the data list view
        $this->load->view('user/home/ajax-data', $this->data, false);
    }

    public function rate_review_post()
    {
        $review_data = $this->input->post();
        $check_service_status = $this->home->check_booking_status($this->input->post('booking_id'));

        if ($check_service_status != '')
        {
            $result = $this->home->rate_review_for_service($review_data);

            if ($result == 1)
            {
                $this->session->set_flashdata('success_message', 'Thank you for your review');
                $token = $this->session->userdata('chat_token');

                $this->send_push_notification($token, $this->input->post('booking_id') , 1, ' Have Review The Service');

            }
            elseif ($result == 2)
            {
                $this->session->set_flashdata('error_message', 'You have already reviewed this service');
            }
            else
            {
                $this->session->set_flashdata('error_message', 'Booking not completed');
            }
            echo json_encode($result);
            $data = array(
                'status' => '6'
            );
            $this->db->where('id', $this->input->post('booking_id'));
            $this->db->update('book_service', $data);
        }
    }

    /*push notification*/

    public function send_push_notification($token, $service_id, $type, $msg = '')
    {

        $data = $this->api->get_book_info($service_id);

        if (!empty($data))
        {
            if ($type == 1)
            {
                $device_tokens = $this->api->get_device_info_multiple($data['provider_id'], 1);
            }
            else
            {
                $device_tokens = $this->api->get_device_info_multiple($data['user_id'], 2);
            }

            if ($type == 2)
            {
                $user_info = $this->api->get_user_info($data['user_id'], $type);

                $name = $this->api->get_user_info($data['provider_id'], 1);

            }
            else
            {
                $name = $this->api->get_user_info($data['user_id'], 2);

                $user_info = $this->api->get_user_info($data['provider_id'], $type);
            }

            /*insert notification*/

            $msg = ucfirst($name['name']) . ' ' . strtolower($msg);

            if (!empty($user_info['token']))
            {
                $this->api->insert_notification($token, $user_info['token'], $msg);
            }

            $title = $data['service_title'];

            if (!empty($device_tokens))
            {
                foreach ($device_tokens as $key => $device)
                {
                    if (!empty($device['device_type']) && !empty($device['device_id']))
                    {

                        if (strtolower($device['device_type']) == 'android')
                        {

                            $notify_structure = array(
                                'title' => $title,
                                'message' => $msg,
                                'image' => 'test22',
                                'action' => 'test222',
                                'action_destination' => 'test222',
                            );

                            sendFCMMessage($notify_structure, $device['device_id']);

                        }

                        if (strtolower($device['device_type'] == 'ios'))
                        {
                            $notify_structure = array(
                                'alert' => $msg,
                                'sound' => 'default',
                                'badge' => 0,
                            );

                            sendApnsMessage($notify_structure, $device['device_id']);

                        }
                    }
                }
            }
            /*apns push notification*/
        }
        else
        {
            $this->token_error();
        }
    }
    
    /*
        public function manage_jobs_view()
        {
            $this->data['page'] = 'manage_jobs_view';
            $this->load->vars($this->data);
            $this->load->view($this->data['theme'] . '/template');
        }
        public function manage_proposal()
        {
            $this->data['page'] = 'manage_proposal';
            $this->load->vars($this->data);
            $this->load->view($this->data['theme'] . '/template');
        }
        public function send_reviews()
        {
            $this->data['page'] = 'send_reviews';
            $this->load->vars($this->data);
            $this->load->view($this->data['theme'] . '/template');
        }

        public function send_reviews_form()
        {

            $data1 = $this->input->post();
            //print_r($data1); die;
            $id1 = $data1['proposal_id'];

            $id = $data1['id'];
            $reviews = $data1['reviews'];
            $title_of_review = $data1['title_of_review'];
            $job_post_id = $data1['job_post_id'];
            $review_comment = $data1['review_comment'];

            $review_sender_id = $this->session->userdata('id');

            $month = date("m");
            if ($month == 1)
            {
                $m = "January";
            }
            if ($month == 2)
            {
                $m = "February";
            }
            if ($month == 3)
            {
                $m = "March";
            }
            if ($month == 4)
            {
                $m = "April";
            }
            if ($month == 5)
            {
                $m = "May";
            }
            if ($month == 6)
            {
                $m = "June";
            }
            if ($month == 7)
            {
                $m = "July";
            }
            if ($month == 8)
            {
                $m = "August";
            }
            if ($month == 9)
            {
                $m = "September";
            }
            if ($month == 10)
            {
                $m = "October";
            }
            if ($month == 11)
            {
                $m = "November";
            }
            if ($month == 12)
            {
                $m = "December";
            }
            $date_time = $m . date("h:i:sa");

            if (($this->session->userdata('id') != '') && ($this->session->userdata('usertype') == 'user'))
            {
                //$provider_id = $data1['provider_id'];
                $provider_id = " ";
                $user_id = $this->session->userdata('id');

                $data = array(
                    'job_post_id' => $id1,
                    'user_id' => $user_id,
                    'provider_id' => $provider_id,
                    'reviews' => $reviews,
                    'title_of_review' => $title_of_review,
                    'review_comment' => $review_comment,
                    'date_time' => $date_time
                );
                $this->db->insert('job_reviews', $data);
                redirect(base_url() . 'view-reviews?proposal_id=' . $id . '&provider_id=' . $data1['provider_id']);

            }
            if (($this->session->userdata('id') != '') && ($this->session->userdata('usertype') == 'provider'))
            {
                // $user_id = $data1['id'];
                $user_id = " ";
                $provider_id = $this->session->userdata('id');

                $data = array(
                    'job_post_id' => $id1,
                    'user_id' => $user_id,
                    'provider_id' => $provider_id,
                    'reviews' => $reviews,
                    'title_of_review' => $title_of_review,
                    'review_comment' => $review_comment,
                    'date_time' => $date_time
                );
                $this->db->insert('job_reviews', $data);
                redirect(base_url() . 'view-reviews?proposal_id=' . $id . '&user_id=' . $data1['user_id']);

            }

        }
        public function view_reviews()
        {
            $this->data['page'] = 'view_reviews';
            $this->load->vars($this->data);
            $this->load->view($this->data['theme'] . '/template');
        }

        public function manage_jobs_edit()
        {
            $hidden_id = $this->input->post('hidden_id');
            $user_id = $this->session->userdata('id');
            $job_tittle = $this->input->post('job_tittle');
            $job_description = $this->input->post('job_description');
            $job_type = $this->input->post('job_type');
            $skills = $this->input->post('skills');
            $amount = $this->input->post('amount');

            $serviceamounttype = $this->input->post('serviceamounttype');
            $currency_code = $this->input->post('currency_code');
            $data = array(
                'user_id' => $user_id,
                'job_tittle' => $job_tittle,
                'job_description' => $job_description,
                'job_type' => $job_type,
                'skills' => implode(",", $skills) ,
                'amount' => $amount,
                'serviceamounttype' => $serviceamounttype,
                'currency_code' => $currency_code
            );
            $this->db->where('id', $hidden_id);
            $this->db->update('post_jobs_form', $data);
            redirect(base_url() . 'manage-jobs');
        }
        public function manage_jobs_delete()
        {
            $id = $_GET['id'];
            //echo $id; die;
            $this->db->where('id', $id);
            $this->db->delete('post_jobs_form');
            redirect(base_url() . 'manage-jobs');
        }
        public function post_jobs_form()
        {
            $user_id = $this->session->userdata('id');
            //echo $user_id;
            //die("============");
            $job_tittle = $this->input->post('job_tittle');
            $job_description = $this->input->post('job_description');
            $job_type = $this->input->post('job_type');
            $skills = $this->input->post('skills');
            $amount = $this->input->post('amount');

            $serviceamounttype = $this->input->post('serviceamounttype');
            $currency_code = $this->input->post('currency_code');
            if ($currency_code == "" || is_null($currency_code)) {
                $user_currency = get_user_currency();
                $currency_code = $user_currency['user_currency_code'];
            }
            $data = array(
                'user_id' => $user_id,
                'job_tittle' => $job_tittle,
                'job_description' => $job_description,
                'job_type' => $job_type,
                'skills' => implode(",", $skills) ,
                'amount' => $amount,
                'serviceamounttype' => $serviceamounttype,
                'currency_code' => $currency_code
            );
            $this->load->model("PostJobsForm");
            $this->PostJobsForm->addJob($data);
            redirect(base_url() . 'manage-jobs');

        }
        public function manage_jobs()
        {
            //die("====");
            $this->data['page'] = 'manage_jobs';
            $this->load->vars($this->data);
            $this->load->view($this->data['theme'] . '/template');
        }
        public function post_a_jobs()
        {
            $this->data['page'] = 'post_a_jobs';
            $this->data['subcategories'] = $this->db->get('subcategories')->result_array();
            $this->load->vars($this->data);
            $this->load->view($this->data['theme'] . '/template');
        }
    */
    public function post_a_jobs()
        {
            $this->data['page'] = 'post_a_jobs';
            $this->data['subcategories'] = $this->db->get('subcategories')->result_array();
            $this->load->vars($this->data);
            $this->load->view($this->data['theme'] . '/template');
    }

    public function user_bookings()
    {
        $this->data['page'] = 'user_bookings';
        $user_id = $this->session->userdata('id');
        $status = $this->input->post('status');
        $sortBy = $this->input->post('sortBy');

        if (!empty($status))
        {
            $conditions['where']['b.status'] = $status;
        }
        $conditions['returnType'] = 'count';
        $totalRec = $this->userbooking->getRows($conditions);

        // Pagination configuration
        $config['target'] = '#dataList';
        $config['base_url'] = base_url('user/dashboard/userajaxPaginationData');
        $config['total_rows'] = $totalRec;
        $config['per_page'] = $this->perPage;

        // Initialize pagination library
        $this->ajax_pagination->initialize($config);

        // Get records
        $conditions = array(
            'limit' => $this->perPage
        );
        $this->data['all_bookings'] = $this->userbooking->getRows($conditions);

        // Load the list page view
        $this->load->vars($this->data);
        $this->load->view($this->data['theme'] . '/template');
    }
    function userajaxPaginationData()
    {
        // Define offset
        $page = $this->input->post('page');
        if (!$page)
        {
            $offset = 0;
        }
        else
        {
            $offset = $page;
        }

        // Set conditions for search and filter
        $status = $this->input->post('status');
        $sortBy = $this->input->post('sortBy');

        if (!empty($status))
        {
            $conditions['where']['b.status'] = $status;
        }

        // Get record count
        $conditions['returnType'] = 'count';

        $totalRec = $this->userbooking->getRows($conditions);

        // Pagination configuration
        $config['target'] = '#dataList';
        $config['base_url'] = base_url('user/dashboard/userajaxPaginationData');
        $config['total_rows'] = $totalRec;
        $config['per_page'] = $this->perPage;

        // Initialize pagination library
        $this->ajax_pagination->initialize($config);

        // Get records
        $conditions = array(
            'start' => $offset,
            'limit' => $this->perPage
        );
        if (!empty($status))
        {
            $conditions['where']['b.status'] = $status;
        }

        $this->data['all_bookings'] = $this->userbooking->getRows($conditions);

        // Load the data list view
        $this->load->view('user/home/user-ajax-data', $this->data, false);
    }
    public function create_availability()
    { //print_r($_POST['availability']);exit;
        $data['tab_ctrl'] = 3;
        extract($_POST);
        if ($this->input->post())
        {
            $check_availability = 8;

            foreach ($_POST['availability'] as $row)
            {
                if (empty($row['from_time']))
                {
                    $check_availability--;
                }
            }
            if ($check_availability == 0)
            {
                $this->session->set_flashdata('error_message', 'Kindly Select min  one day..');
                redirect(base_url() . 'provider-availability', $data);
            }
            $params = $this->input->post();

            $check_provider = $this->home->provider_hours($this->session->userdata('id'));

            if (empty($check_provider))
            {

                $result = $this->home->create_availability($params);
            }
            elseif (!empty($check_provider))
            {

                $result = $this->home->update_availability($params);
            }

            if ($result)
            {
                $data = array(
                    'tab_ctrl' => 3,
                    'success_message' => 'Availability time Created successfully'
                );
                $this->session->set_flashdata($data);
                $this->session->set_flashdata('success_message', 'Availability time Created successfully');
                redirect(base_url() . 'provider-availability', $data);
            }

        }
    }

    public function image_resize($width = 0, $height = 0, $image_url, $filename)
    {

        $source_path = base_url() . $image_url;
        list($source_width, $source_height, $source_type) = getimagesize($source_path);
        switch ($source_type)
        {
            case IMAGETYPE_GIF:
                $source_gdim = imagecreatefromgif($source_path);
            break;
            case IMAGETYPE_JPEG:
                $source_gdim = imagecreatefromjpeg($source_path);
            break;
            case IMAGETYPE_PNG:
                $source_gdim = imagecreatefrompng($source_path);
            break;
        }

        $source_aspect_ratio = $source_width / $source_height;
        $desired_aspect_ratio = $width / $height;

        if ($source_aspect_ratio > $desired_aspect_ratio)
        {
            /*
             * Triggered when source image is wider
            */
            $temp_height = $height;
            $temp_width = ( int )($height * $source_aspect_ratio);
        }
        else
        {
            /*
             * Triggered otherwise (i.e. source image is similar or taller)
            */
            $temp_width = $width;
            $temp_height = ( int )($width / $source_aspect_ratio);
        }

        /*
         * Resize the image into a temporary GD image
        */

        $temp_gdim = imagecreatetruecolor($temp_width, $temp_height);
        imagecopyresampled($temp_gdim, $source_gdim, 0, 0, 0, 0, $temp_width, $temp_height, $source_width, $source_height);

        /*
         * Copy cropped region from temporary image into the desired GD image
        */

        $x0 = ($temp_width - $width) / 2;
        $y0 = ($temp_height - $height) / 2;
        $desired_gdim = imagecreatetruecolor($width, $height);
        imagecopy($desired_gdim, $temp_gdim, 0, 0, $x0, $y0, $width, $height);

        /*
         * Render the image
         * Alternatively, you can save the image in file-system or database
        */
        $filename_without_extension = preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);
        $image_url = "uploads/profile_img/" . $filename_without_extension . "_" . $width . "_" . $height . ".jpg";
        imagejpeg($desired_gdim, $image_url);

        return $image_url;

        /*
         * Add clean-up code here
        */
    }

    //paramesh
    public function razor_payment_success()
    {
        //echo "hi";exit;
        //removeTag($this->input->get());
        $user_id = $this->session->userdata('id');
        $user = $this->db->where('id', $user_id)->get('users')->row_array();
        $params = $this->input->get();

        //echo "<pre>";print_r($params);exit;
        $token = $this->session->userdata('chat_token');
        $user_name = $user['name'];
        $user_token = $user['token'];
        $currency_type = $user['currency_code'];

        $amt = $params['totalAmount'];

        $wallet = $this->db->where('user_provider_id', $user_id)->get('wallet_table')->row_array();
        $wallet_amt = $wallet['wallet_amt'];
        if ($wallet_amt)
        {
            $current_wallet = $wallet_amt;
        }
        else
        {
            $current_wallet = $amt;
        }

        $history_pay['token'] = $user_token;
        $history_pay['currency_code'] = $currency_type;
        $history_pay['user_provider_id'] = $user_id;
        $history_pay['type'] = '2';
        $history_pay['tokenid'] = $token;
        $history_pay['payment_detail'] = "Razorpay";
        $history_pay['charge_id'] = 1;
        //$history_pay['transaction_id']=$pay_transaction;
        $history_pay['exchange_rate'] = 0;
        $history_pay['paid_status'] = "pass";
        $history_pay['cust_id'] = "self";
        $history_pay['card_id'] = "self";
        $history_pay['total_amt'] = $amt;
        $history_pay['fee_amt'] = 0;
        $history_pay['net_amt'] = $amt;
        $history_pay['amount_refund'] = 0;
        $history_pay['current_wallet'] = $current_wallet;
        $history_pay['credit_wallet'] = $amt;
        $history_pay['debit_wallet'] = 0;
        $history_pay['avail_wallet'] = $amt + $wallet_amt;
        $history_pay['reason'] = TOPUP;
        $history_pay['created_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert('wallet_transaction_history', $history_pay))
        {

            $this->db->where('user_provider_id', $user_id)->update('wallet_table', array(
                'currency_code' => 'INR',
                'wallet_amt' => $amt + $current_wallet
            ));
            echo 0;
        }
        else
        {
            echo 1;
        }

    }
    public function razorthankyou()
    {
        $result = $_REQUEST['res'];
        if ($result == 0)
        {
            $message = (!empty($this->user_language[$this->user_selected]['lg_wallet_amount_add_wallet'])) ? $this->user_language[$this->user_selected]['lg_wallet_amount_add_wallet'] : $this->default_language['en']['lg_wallet_amount_add_wallet'];
            $this->session->set_flashdata('msg_success', $message);
            redirect('user-wallet');
        }
        else
        {
            $message = (!empty($this->user_language[$this->user_selected]['lg_something_went_wrong'])) ? $this->user_language[$this->user_selected]['lg_something_went_wrong'] : $this->default_language['en']['lg_something_went_wrong'];
            $this->session->set_flashdata('message', $message);
            redirect('user-wallet');
        }
    }

    public function razorpay_details()
    {
        //echo "hi";exit;
        removeTag($this->input->post());
        $params = $this->input->post();
        $user_id = $this->session->userdata('id');

        $query = $this->db->query("select * from system_settings WHERE status = 1");
        $result = $query->result_array();
        if (!empty($result))
        {
            foreach ($result as $data1)
            {

                if ($data1['key'] == 'razorpay_apikey')
                {
                    $apikey = $data1['value'];
                }

                if ($data1['key'] == 'razorpay_secret_key')
                {
                    $apisecret = $data1['value'];
                }

                if ($data1['key'] == 'live_razorpay_apikey')
                {
                    $apikey = $data1['value'];
                }

                if ($data1['key'] == 'live_razorpay_secret_key')
                {
                    $apisecret = $data1['value'];
                }

            }
        }

        // $razor_option = settingValue('razor_option');
        // if($razorpay_option == 1){
        // $apikey = settingValue('razorpay_apikey');
        // $apisecret = settingValue('razorpay_secret_key');
        // }else if($razorpay_option == 2){
        // $apikey = settingValue('live_razorpay_apikey');
        // $apisecret = settingValue('live_razorpay_secret_key');
        // }
        $user_currency = 'INR';
        if (!empty($params))
        {
            $url = "https://api.razorpay.com/v1/contacts";
            $unique = strtoupper(uniqid());
            $data = ' {
			  "name":"' . $params['name'] . '",
			  "email":"' . $params['email'] . '",
			  "contact":"' . $params['contact'] . '",
			  "type":"employee",
			  "reference_id":"' . $unique . '",
			  "notes":{}
			}';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_USERPWD, $apikey . ":" . $apisecret);
            $headers = array(
                'Content-Type:application/json'
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);

            if (curl_errno($ch))
            {
                $result = 'Error:' . curl_error($ch);
                echo json_encode(array(
                    'status' => false,
                    'msg' => $result
                ));
            }

            $results = json_decode($result);
            $user_id = $this->session->userdata('id');
            $cnotes = $results->notes;
            $serializedcnotes = serialize($cnotes);
            $contact_data = array(
                'user_id' => $user_id,
                'rp_contactid' => $results->id,
                'entity' => $results->entity,
                'name' => $results->name,
                'contact' => $results->contact,
                'email' => $results->email,
                'type' => $results->type,
                'reference_id' => $results->reference_id,
                'batch_id' => $results->batch_id,
                'active' => $results->active,
                'accountnumber' => $params['accountnumber'],
                'mode' => $params['mode'],
                'purpose' => $params['purpose'],
                'notes' => $serializedcnotes,
                'created_at' => $results->created_at
            );

            $createcontact = $this->db->insert('razorpay_contact', $contact_data);
            if (!empty($createcontact))
            {
                $faurl = "https://api.razorpay.com/v1/fund_accounts";
                $faunique = strtoupper(uniqid());
                $fadata = ' {
				  "contact_id": "' . $results->id . '",
				  "account_type": "bank_account",
				  "bank_account": {
					"name": "' . $params['bank_name'] . '",
					"ifsc": "' . $params['ifsc'] . '",
					"account_number":"' . $params['accountnumber'] . '"
				  }
				}';

                $fach = curl_init();
                curl_setopt($fach, CURLOPT_URL, $faurl);
                curl_setopt($fach, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($fach, CURLOPT_POSTFIELDS, $fadata);
                curl_setopt($fach, CURLOPT_POST, 1);
                curl_setopt($fach, CURLOPT_USERPWD, $apikey . ":" . $apisecret);
                $faheaders = array(
                    'Content-Type:application/json'
                );
                curl_setopt($fach, CURLOPT_HTTPHEADER, $faheaders);
                $faresult = curl_exec($fach);

                if (curl_errno($fach))
                {
                    $faresult = 'Error:' . curl_error($fach);
                    echo json_encode(array(
                        'status' => false,
                        'msg' => $faresult
                    ));
                }
                $faresults = json_decode($faresult);

                $fa_data = array(
                    'fund_account_id' => $faresults->id,
                    'entity' => $faresults->entity,
                    'contact_id' => $faresults->contact_id,
                    'account_type' => $faresults->account_type,
                    'ifsc' => $faresults->bank_account->ifsc,
                    'bank_name' => $faresults->bank_account->bank_name,
                    'name' => $faresults->bank_account->name,
                    'account_number' => $faresults->bank_account->account_number,
                    'active' => $faresults->active,
                    'batch_id' => $faresults->batch_id,
                    'created_at' => $faresults->created_at
                );

                $facreatecontact = $this->db->insert('razorpay_fund_account', $fa_data);

                if ($facreatecontact)
                {
                    $purl = "https://api.razorpay.com/v1/payouts";
                    $punique = strtoupper(uniqid());
                    $pdata = ' {
					  "account_number": "2323230032510196",
					  "fund_account_id": "' . $faresults->id . '",
					  "amount": "' . $params['amount'] . '",
					  "currency": "INR",
					  "mode": "' . $params['mode'] . '",
					  "purpose": "' . $params['purpose'] . '",
					  "queue_if_low_balance": true,
					  "reference_id": "' . $punique . '",
					  "narration": "",
					  "notes": {}
					}';

                    $pch = curl_init();
                    curl_setopt($pch, CURLOPT_URL, $purl);
                    curl_setopt($pch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($pch, CURLOPT_POSTFIELDS, $pdata);
                    curl_setopt($pch, CURLOPT_POST, 1);
                    curl_setopt($pch, CURLOPT_USERPWD, $apikey . ":" . $apisecret);
                    $pheaders = array(
                        'Content-Type:application/json'
                    );
                    curl_setopt($pch, CURLOPT_HTTPHEADER, $pheaders);
                    $presult = curl_exec($pch);

                    if (curl_errno($pch))
                    {
                        $presult = 'Error:' . curl_error($pch);
                        echo json_encode(array(
                            'status' => false,
                            'msg' => $presult
                        ));
                    }
                    $presults = json_decode($presult);

                    $pydata = array(
                        'payout_id' => $presults->id,
                        'entity' => $presults->entity,
                        'fund_account_id' => $presults->fund_account_id,
                        'amount' => $presults->amount,
                        'currency' => $presults->currency,
                        'fees' => $presults->fees,
                        'tax' => $presults->tax,
                        'status' => $presults->status,
                        'utr' => $presults->utr,
                        'mode' => $presults->mode,
                        'purpose' => $presults->purpose,
                        'reference_id' => $presults->reference_id,
                        'narration' => $presults->narration,
                        'batch_id' => $presults->batch_id,
                        'failure_reason' => $presults->failure_reason,
                        'created_at' => $presults->created_at
                    );
                    $payouts = $this->db->insert('razorpay_payouts', $pydata);
                    if ($payouts)
                    {
                        $wdata = array(
                            'user_id' => $user_id,
                            'amount' => $presults->amount,
                            'currency_code' => $presults->currency,
                            'transaction_status' => 1,
                            'transaction_date' => date('Y-m-d') ,
                            'request_payment' => 'RazorPay',
                            'status' => 1,
                            'created_by' => $user_id,
                            'created_at' => $presults->created_at
                        );

                        $payoutins = $this->db->insert('wallet_withdraw', $wdata);
                        if ($payoutins)
                        {
                            $amount = $presults->amount;
                            $user_id = $this->session->userdata('id');
                            $user = $this->db->where('id', $user_id)->get('providers')->row_array();
                            $user_name = $user['name'];
                            $user_token = $user['token'];
                            $currency_type = $user['currency_code'];
                            $wallet = $this->db->where('user_provider_id', $user_id)->where('type', 1)->get('wallet_table')->row_array();
                            $wallet_amt = $wallet['wallet_amt']; //echo json_encode($wallet_amt);exit;
                            $history_pay['token'] = $user_token;
                            $history_pay['user_provider_id'] = $user_id;
                            $history_pay['currency_code'] = 'INR';
                            $history_pay['credit_wallet'] = 0;
                            $history_pay['debit_wallet'] = $amount;

                            $history_pay['transaction_id'] = $presults->id;
                            $history_pay['paid_status'] = '1';
                            $history_pay['total_amt'] = $presults->amount;
                            if ($wallet_amt)
                            {
                                $current_wallet = $wallet_amt - $amount;
                            }
                            else
                            {
                                $current_wallet = $amount;
                            }
                            $history_pay['current_wallet'] = $wallet_amt;
                            $history_pay['avail_wallet'] = $current_wallet;
                            $history_pay['reason'] = 'Withdrawn Wallet Amt';
                            $history_pay['created_at'] = date('Y-m-d H:i:s');
                            if ($this->db->insert('wallet_transaction_history', $history_pay))
                            {
                                $this->db->where('user_provider_id', $user_id)->update('wallet_table', array(
                                    'currency_code' => 'INR',
                                    'wallet_amt' => $current_wallet
                                ));
                            }
                            $message = "Amount Withdrawn Successfully";
                            echo json_encode(array(
                                'status' => true,
                                'msg' => $message
                            ));
                        }
                        else
                        {
                            $message = "Payout details not Inserted";
                            echo json_encode(array(
                                'status' => false,
                                'msg' => $message
                            ));
                        }
                    }
                    else
                    {
                        $message = "Payout details not Inserted";
                        echo json_encode(array(
                            'status' => false,
                            'msg' => $message
                        ));
                    }
                }
            }
        }
        else
        {
            $message = (!empty($this->user_language[$this->user_selected]['lg_something_went_wrong'])) ? $this->user_language[$this->user_selected]['lg_something_went_wrong'] : $this->default_language['en']['lg_something_went_wrong'];
            echo json_encode(array(
                'status' => false,
                'msg' => $message
            ));
        }
    }

    public function bank_details()
    {
        removeTag($this->input->post());
        $params = $this->input->post();
        $user_id = $this->session->userdata('id');
        $user_currency = 'INR';
        if (!empty($params))
        {
            $check_bank = $this->db->where('user_id', $user_id)->get('bank_account')->num_rows();
            $user_det = $this->db->where('id', $user_id)->get('providers')->row_array();
            $data = array(
                'user_id' => $user_id,
                'account_number' => $params['account_no'],
                'account_holder_name' => $user_det['name'],
                'bank_name' => $params['bank_name'],
                'bank_address' => $params['bank_address'],
                'sort_code' => $params['sort_code'],
                'routing_number' => $params['routing_number'],
                'account_ifsc' => $params['ifsc_code'],
                'pancard_no' => $params['pancard_no'],
                'paypal_account' => $params['paypal_id'],
                'paypal_email_id' => $params['paypal_email_id']
            );
            if ($check_bank > 0)
            {
                $result = $this->db->where('user_id', $user_id)->update('stripe_bank_details', $data);
            }
            else
            {
                $result = $this->db->insert('stripe_bank_details', $data);
            }
            if ($result == true)
            {
                $wallet_data = array(
                    'user_id' => $user_id,
                    'amount' => $params['amount'],
                    'currency_code' => $user_currency,
                    'status' => 1,
                    'transaction_status' => 0,
                    'request_payment' => $params['payment_type'],
                    'created_by' => $user_id,
                    'created_at' => date('Y-m-d H:i:s')
                );
                $amount = $this->db->insert('wallet_withdraw', $wallet_data);

                //echo json_encode($user_id);exit;
                if ($amount == true)
                {
                    $amount_withdraw = $this->Stripe_model->wallet_withdraw_flow($params['amount'], $user_currency, $user_id, 1);
                }
                $message = 'Amount Withdrawn Successfully';
                echo json_encode(array(
                    'status' => true,
                    'msg' => $message
                ));
            }
            else
            {
                $message = (!empty($this->user_language[$this->user_selected]['lg_something_went_wrong'])) ? $this->user_language[$this->user_selected]['lg_something_went_wrong'] : $this->default_language['en']['lg_something_went_wrong'];
                echo json_encode(array(
                    'status' => false,
                    'msg' => $message
                ));
            }
        }
    }
    /*public function razorpay_details()
    {
        removeTag($this->input->post());
        $params        = $this->input->post();
        $user_id       = $this->session->userdata('SESSION_USER_ID');
    $razorpay_option = $this->data['razorpay_option'];
    if($razorpay_option == 1){
    $apikey = $this->data['razorpay_apikey'];
    $apisecret = $this->data['razorpay_apisecret'];
    }else if($razorpay_option == 2){
    $apikey = $this->data['razorpaylive_apikey'];
    $apisecret = $this->data['razorpaylive_apisecret'];
    }
        $user_currency = 'INR';
        if (!empty($params)) { 
    $url = "https://api.razorpay.com/v1/contacts";
    $unique = strtoupper(uniqid());
    $data   = ' {
     "name":"'.$params['name'].'",
     "email":"'.$params['email'].'",
     "contact":"'.$params['contact'].'",
     "type":"employee",
     "reference_id":"'.$unique.'",
     "notes":{}
    }';
    $ch     = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, $apikey . ":" . $apisecret);
    $headers = array(
    'Content-Type:application/json'
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    
    if (curl_errno($ch)) {
    $result = 'Error:' . curl_error($ch);
    echo json_encode(array(
                    'status' => false,
                    'msg' => $result
                ));
    }
    $results = json_decode($result);
    $user_id       = $this->session->userdata('SESSION_USER_ID');
    $cnotes = $results->notes;
    $serializedcnotes = serialize($cnotes);
    $contact_data = array(
    'user_id' => $user_id,
    'rp_contactid' => $results->id,
    'entity' => $results->entity,
    'name' => $results->name,
    'contact' => $results->contact,
    'email' => $results->email,
    'type' => $results->type,
    'reference_id' => $results->reference_id,
    'batch_id' => $results->batch_id,
    'active' => $results->active,
    'accountnumber' => $params['accountnumber'],
    'mode' => $params['mode'],
    'purpose' => $params['purpose'],
    'notes' => $serializedcnotes,
    'created_at' => $results->created_at
    );
    $createcontact = $this->db->insert('razorpay_contact', $contact_data);
    if(!empty($createcontact)){
    $faurl = "https://api.razorpay.com/v1/fund_accounts";
    $faunique = strtoupper(uniqid());
    $fadata   = ' {
      "contact_id": "'.$results->id.'",
      "account_type": "bank_account",
      "bank_account": {
    	"name": "'.$params['bank_name'].'",
    	"ifsc": "'.$params['ifsc'].'",
    	"account_number":"'.$params['accountnumber'].'"
      }
    }';
    				
    $fach     = curl_init();
    curl_setopt($fach, CURLOPT_URL, $faurl);
    curl_setopt($fach, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($fach, CURLOPT_POSTFIELDS, $fadata);
    curl_setopt($fach, CURLOPT_POST, 1);
    curl_setopt($fach, CURLOPT_USERPWD, $apikey . ":" . $apisecret);
    $faheaders = array(
    	'Content-Type:application/json'
    );
    curl_setopt($fach, CURLOPT_HTTPHEADER, $faheaders);
    $faresult = curl_exec($fach);
    
    if (curl_errno($fach)) {
    	$faresult = 'Error:' . curl_error($fach);
    	echo json_encode(array(
    		'status' => false,
    		'msg' => $faresult
    	));
    }
    $faresults = json_decode($faresult);
    
    $fa_data = array(
    	'fund_account_id' => $faresults->id,
    	'entity' => $faresults->entity,
    	'contact_id' => $faresults->contact_id,
    	'account_type' => $faresults->account_type,
    	'ifsc' => $faresults->bank_account->ifsc,
    	'bank_name' => $faresults->bank_account->bank_name,
    	'name' => $faresults->bank_account->name,
    	'account_number' => $faresults->bank_account->account_number,
    	'active' => $faresults->active,
    	'batch_id' => $faresults->batch_id,
    	'created_at' => $faresults->created_at
    );
    $facreatecontact = $this->db->insert('razorpay_fund_account', $fa_data);
    
    if($facreatecontact){
    	$purl = "https://api.razorpay.com/v1/payouts";
    	$punique = strtoupper(uniqid());
    	$pdata   = ' {
    	  "account_number": "2323230032510196",
    	  "fund_account_id": "'.$faresults->id.'",
    	  "amount": "'.$params['amount'].'",
    	  "currency": "INR",
    	  "mode": "'.$params['mode'].'",
    	  "purpose": "'.$params['purpose'].'",
    	  "queue_if_low_balance": true,
    	  "reference_id": "'.$punique.'",
    	  "narration": "",
    	  "notes": {}
    	}';
    	
    	$pch     = curl_init();
    	curl_setopt($pch, CURLOPT_URL, $purl);
    	curl_setopt($pch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($pch, CURLOPT_POSTFIELDS, $pdata);
    	curl_setopt($pch, CURLOPT_POST, 1);
    	curl_setopt($pch, CURLOPT_USERPWD, $apikey . ":" . $apisecret);
    	$pheaders = array(
    		'Content-Type:application/json'
    	);
    	curl_setopt($pch, CURLOPT_HTTPHEADER, $pheaders);
    	$presult = curl_exec($pch);
    	
    	if (curl_errno($pch)) {
    		$presult = 'Error:' . curl_error($pch);						
    		echo json_encode(array(
    			'status' => false,
    			'msg' => $presult
    		));
    	}
    	$presults = json_decode($presult);
    	
    	$pydata = array(
    		'payout_id' => $presults->id,
    		'entity' => $presults->entity,
    		'fund_account_id' => $presults->fund_account_id,
    		'amount' => $presults->amount,
    		'currency' => $presults->currency,
    		'fees' => $presults->fees,
    		'tax' => $presults->tax,
    		'status' => $presults->status,
    		'utr' => $presults->utr,
    		'mode' => $presults->mode,
    		'purpose' => $presults->purpose,						
    		'reference_id' => $presults->reference_id,
    		'narration' => $presults->narration,
    		'batch_id' => $presults->batch_id,
    		'failure_reason' => $presults->failure_reason,
    		'created_at' => $presults->created_at
    	);
    	$payouts = $this->db->insert('razorpay_payouts', $pydata);
    	if($payouts){
    		$wdata = array(
    			'user_id' => $user_id,
    			'amount' => $presults->amount,
    			'currency_code' => $presults->currency,
    			'transaction_status' => 1,
    			'transaction_date' => date('Y-m-d'),
    			'request_payment' => 'RazorPay',
    			'status' => 1,
    			'created_by' => $user_id,
    			'created_at' => $presults->created_at
    		);
    		$payoutins = $this->db->insert('wallet_withdraw', $wdata);
    		if($payoutins){
    			$amount        = $presults->amount;
    			$user_id       = $this->session->userdata('SESSION_USER_ID');
    			$user          = $this->db->where('USERID', $user_id)->get('members')->row_array();
    			$user_name     = $user['username'];
    			$user_token    = $user['unique_code'];
    			$currency_type = $user['currency_code'];
    			$wallet = $this->db->where('user_provider_id', $user_id)->get('wallet_table')->row_array();
    			$wallet_amt = $wallet['wallet_amt'];
    			$history_pay['token']=$user_token;
    			$history_pay['user_provider_id']=$user_id;
    			$history_pay['currency_code']='INR';
    			$history_pay['transaction_id']=$presults->id;
    			$history_pay['paid_status']='1';
    			$history_pay['total_amt']=$presults->amount;
    			if($wallet_amt){
    				$current_wallet = $wallet_amt-$amount;
    			}else{
    				$current_wallet = $amount;
    			}
    			$history_pay['current_wallet']=$current_wallet;
    			$history_pay['reason']='Withdrawn Wallet Amt';
    			$history_pay['created_at']=date('Y-m-d H:i:s');
    			if($this->db->insert('wallet_transaction_history',$history_pay)){								
    				$this->db->where('user_provider_id', $user_id)->update('wallet_table', array(
    					'currency_code' => 'INR',
    					'wallet_amt' => $current_wallet
    				));								               
    			}
    			$message = "Amount Withdrawn Successfully";
    			echo json_encode(array(
    				'status' => true,
    				'msg' => $message
    			));
    		}else{
    			$message = "Payout details not Inserted";
    			echo json_encode(array(
    				'status' => false,
    				'msg' => $message
    			));
    		}
    	}else{
    		$message = "Payout details not Inserted";
    		echo json_encode(array(
    			'status' => false,
    			'msg' => $message
    		));
    	}
    } 
    }
        }else{
    $message = (!empty($this->user_language[$this->user_selected]['lg_something_went_wrong'])) ? $this->user_language[$this->user_selected]['lg_something_went_wrong'] : $this->default_language['en']['lg_something_went_wrong'];
            echo json_encode(array(
                    'status' => false,
                    'msg' => $message
                ));
    }
    }*/

    public function service_map_list()
    {

        $this->db->select('tab_2.name,tab_1.service_latitude,tab_1.service_longitude,tab_1.service_title')->from('services tab_1');
        $val = $this->db->join('providers tab_2', 'tab_2.id=tab_1.user_id', 'LEFT')->get()->result_array();

        if (!empty($val))
        {

            $result_json = [];

            foreach ($val as $key => $value)
            {
                $temp = $temp2 = [];
                $temp2[] = $value["service_latitude"];
                $temp2[] = $value["service_longitude"];

                $temp['latLng'] = $temp2;
                $temp['name'] = $value['name'];

                $result_json[] = $temp;

            }

        }

        $data = json_encode($result_json);
        print ($data);
    }
    //
    
}
