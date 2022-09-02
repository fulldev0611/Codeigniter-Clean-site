<?php
defined('BASEPATH') or exit('No direct script access allowed');
// @Leo: Stripe, Paypal library Load ----------------
use Stripe\Stripe;
require $_SERVER['DOCUMENT_ROOT'] . '/paypal/PayPalClient.php';
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
// --------------------------------------------------

class Booking extends MY_Controller
{ # upgrade maksimU for whitelabel using MY_Controller
    public $data;

    public function __construct()
    {

        parent::__construct();
        error_reporting(0);
        // if (empty($this->session->userdata('id')))
        // {
        //     redirect(base_url());
        // }
        $this->data['theme'] = 'user';
        $this->data['model'] = 'home';
        $this->data['base_url'] = base_url();

        $this->load->helper('custom_language');
        $this->load->helper('push_notifications');

        $this->load->model('booking_model', 'booking');
        $this->load->model('api_model', 'api');
        $this->site_name = 'Tazzer';
        $this->load->model('templates_model');

        $this->load->helper('user_timezone_helper');
        $user_id = $this->session->userdata('id');
        $this->data['user_id'] = $user_id;
        $this->load->helper('subscription_helper');

        $this->data['secret_key'] = '';
        $this->data['publishable_key'] = '';
        $this->data['website_logo_front'] = 'assets/img/logo.png';
        $publishable_key = '';
        $secret_key = '';
        $live_publishable_key = '';
        $live_secret_key = '';
        $stripe_option = '';

        $query = $this->db->query("select * from system_settings WHERE status = 1");
        $result = $query->result_array();
        if (!empty($result))
        {
            foreach ($result as $data)
            {

                if ($data['key'] == 'website_name')
                {
                    $this->website_name = $data['value'];
                }
                else if ($data['key'] == 'secret_key')
                {
                    $secret_key = $data['value'];
                }
                else if ($data['key'] == 'publishable_key')
                {
                    $publishable_key = $data['value'];
                }
                else if ($data['key'] == 'live_secret_key')
                {
                    $live_secret_key = $data['value'];
                }
                else if ($data['key'] == 'live_publishable_key')
                {
                    $live_publishable_key = $data['value'];
                }
                else if ($data['key'] == 'stripe_option')
                {
                    $stripe_option = $data['value'];
                }
                else if ($data['key'] == 'logo_front')
                {
                    $this->data['website_logo_front'] = $data['value'];
                }
            }
        }

        if (@$stripe_option == 1)
        {
            $this->data['publishable_key'] = $publishable_key;
            $this->data['secret_key'] = $secret_key;
        }

        if (@$stripe_option == 2)
        {
            $this->data['publishable_key'] = $live_publishable_key;
            $this->data['secret_key'] = $live_secret_key;
        }
        $config['publishable_key'] = $this->data['publishable_key'];
        $config['secret_key'] = $this->data['secret_key'];
        $this->load->library('stripe', $config);
    }

    public function index()
    {
        redirect(base_url('book-service'));
    }

    /**
     * @author Leo: create paypal booking
    */
    public function create_paypal_booking() {

        $user_currency = get_user_currency();
        $user_currency_code = $user_currency['user_currency_code'];

        $serviceId = $this->input->post("service_id");
        $this->load->model("Service_model", "Service");
        $service = $this->Service->get_service_details($serviceId);
        $serviceAmount = get_gigs_currency($service['service_amount'], $service['currency_code'], $user_currency_code);

        $orderValue = round($serviceAmount/0.95, 2);
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = array(
            'intent' => 'CAPTURE',
            'application_context' => ['return_url' => base_url()."user-bookings",
            'cancel_url' => base_url()."service-booking/".replace_specials($service['service_title'])."?sid=".md5($serviceId)],
            'purchase_units' => [['amount' => ['currency_code' => $user_currency['user_currency_code'],
            'value' => $orderValue]]]
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
        echo json_encode($response->result);
    }

    /**
     * @author Leo: capture paypal booking
     * @created: 
    */
    public function capture_paypal_booking()
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
     * @author Leo: booking complete action
    */
    public function booking_complete_by_paypal($orderId) 
    {
        extract($_POST);
        $inputs = array(
            'service_id' => $this->input->post("service_id"),
            'provider_id' => $this->input->post('provider_id'),
            'user_id' => $this->session->userdata("id"),
            'service_date' => date("Y-m-d", strtotime($service_date)),
            'service_time' => date("h:i:s", strtotime($service_time)),
            'location' => $location,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'notes' => $notes,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'country' => $country,
            'town' => $town,
            'street_addr_1' => $street_addr_1,
            'street_addr_2' => $street_addr_2,
            'email' => $email,
            'phone' => $phone
        );
        $user_currency = get_user_currency();
        $user_currency_code = $user_currency['user_currency_code'];

        $inputs['currency_code'] = $user_currency_code;
        
        $serviceId = $this->input->post("service_id");
        $this->load->model("Service_model", "Service");
        $service = $this->Service->get_service_details($serviceId);
        $serviceAmount = get_gigs_currency($service['service_amount'], $service['currency_code'], $user_currency_code);

        $inputs['amount'] = $serviceAmount;

        $result = $this->booking->bookService($inputs);
        if ($result != '')
        {
            if ($service['user_type'] == "provider") {
                $this->load->model("ProvidersModel");
                $provider_data= $this->ProvidersModel->get($this->input->post('provider_id'));
            }
            else {
                $this->load->model("User_model");
                $provider_data = $this->User_model->get_user($this->input->post('provider_id'));
            }

            $preview_link = base_url();
            $bodyid = 3;
            $tempbody_details = $this->templates_model->get_usertemplate_data($bodyid);
            $providerbody = $tempbody_details['template_content'];
            $providerbody = str_replace('{user_name}', $provider_data['name'], $providerbody);
            $providerbody = str_replace('{sitetitle}', $this->site_name, $providerbody);
            $providerbody = str_replace('{user_person}', $this->session->userdata('name') , $providerbody);
            $providerbody = str_replace('{service_title}', $service['service_title'], $providerbody);
            $providerbody = str_replace('{service_date}', $this->input->post('booking_date') , $providerbody);
            $providerbody = str_replace('{service_time}', $time, $providerbody);
            $providerbody = str_replace('{location_user}', $service['service_location'], $providerbody);
            $providerbody = str_replace('{preview_link}', $preview_link, $providerbody);

            $phpmail_config = settingValue('mail_config');
            if (isset($phpmail_config) && !empty($phpmail_config))
            {
                if ($phpmail_config == "phpmail")
                {
                    $from_email = settingValue('email_address');
                }
                else
                {
                    $from_email = settingValue('smtp_email_address');
                }
            }
            $this->load->library('email');
            // $this->load->library('sms');
            //Send mail to provider
            if (!empty($from_email) && isset($from_email))
            {
                $mail = $this->email->from($from_email)->to($provider_data['email'])->subject('Service Booking')->message($providerbody)->send();
            }

            $message = 'You have booked successfully';

            $token = $this->session->userdata('chat_token');
            
            /* history entry */
            $data = $this->api->get_book_info_b($result);
            $device_token = $this->api->get_device_info_multiple($data['provider_id'], 1);

            $user_name = $this->api->get_user_info($data['user_id'], 2);

            if ($service['user_type'] == "provider") {
                $this->load->model("ProvidersModel");
                $provider_token= $this->ProvidersModel->get($data['provider_id']);
                $user_type_value = 1;
            }
            else {
                $this->load->model("User_model");
                $provider_token = $this->User_model->get_user($data['provider_id']);
                $user_type_value = 2;
            }
            $text = $user_name['name'] . " has booked your Service";
            $this->send_push_notification($token, $result, $user_type_value, $msg = $text);

            $this->session->set_flashdata('success_message', $message);
        }
        else
        {
            $message = 'Sorry, something went wrong';
            $this->session->set_flashdata('error_message', $message);
        }

        $result = "COMPLETE_BOOKING";
        echo json_encode(['success' => true,'result'=>$result,'msg' => $message]);
        exit;
    }

    /**
     * @author Leo: booking complete action
    */
    public function booking_complete() 
    {
        extract($_POST);
        $inputs = array(
            'service_id' => $this->input->post("service_id"),
            'provider_id' => $this->input->post('provider_id'),
            'user_id' => $this->session->userdata("id"),
            'service_date' => date("Y-m-d", strtotime($service_date)),
            'service_time' => date("h:i:s", strtotime($service_time)),
            'location' => $location,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'notes' => $notes,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'country' => $country,
            'town' => $town,
            'street_addr_1' => $street_addr_1,
            'street_addr_2' => $street_addr_2,
            'email' => $email,
            'phone' => $phone
        );
        $user_currency = get_user_currency();
        $user_currency_code = $user_currency['user_currency_code'];

        $inputs['currency_code'] = $user_currency_code;
        
        $serviceId = $this->input->post("service_id");
        $this->load->model("Service_model", "Service");
        $service = $this->Service->get_service_details($serviceId);
        $serviceAmount = get_gigs_currency($service['service_amount'], $service['currency_code'], $user_currency_code);

        $inputs['amount'] = $serviceAmount;

        //check server side validation while booking
        $this->load->model('api_model', 'api');
        $wallet = $this->api->get_wallet($this->session->userdata('chat_token'));

        $wallet_amount = $wallet['wallet_amt'];

        /* check wallet amount */
        // Leo: 18/8/21 - remove wallet check // back 
        if ($serviceAmount * 1.02 > $wallet_amount)
        {
            $message = 'You do not have sufficient balance in your wallet account. Please Topup to book the service.';
            $this->session->set_flashdata('error_message', $message);
            echo json_encode(['success' => false,'result'=>'ADD_WALLET','msg' => $message]);
            exit;
        }
        # will add available check here ...

        # ...
        $result = $this->booking->bookService($inputs);
        if ($result != '')
        {
            if ($service['user_type'] == "provider") {
                $this->load->model("ProvidersModel");
                $provider_data= $this->ProvidersModel->get($this->input->post('provider_id'));
            }
            else {
                $this->load->model("User_model");
                $provider_data = $this->User_model->get_user($this->input->post('provider_id'));
            }

            $preview_link = base_url();
            $bodyid = 3;
            $tempbody_details = $this->templates_model->get_usertemplate_data($bodyid);
            $providerbody = $tempbody_details['template_content'];
            $providerbody = str_replace('{user_name}', $provider_data['name'], $providerbody);
            $providerbody = str_replace('{sitetitle}', $this->site_name, $providerbody);
            $providerbody = str_replace('{user_person}', $this->session->userdata('name') , $providerbody);
            $providerbody = str_replace('{service_title}', $service['service_title'], $providerbody);
            $providerbody = str_replace('{service_date}', $this->input->post('booking_date') , $providerbody);
            $providerbody = str_replace('{service_time}', $time, $providerbody);
            $providerbody = str_replace('{location_user}', $service['service_location'], $providerbody);
            $providerbody = str_replace('{preview_link}', $preview_link, $providerbody);

            $phpmail_config = settingValue('mail_config');
            if (isset($phpmail_config) && !empty($phpmail_config))
            {
                if ($phpmail_config == "phpmail")
                {
                    $from_email = settingValue('email_address');
                }
                else
                {
                    $from_email = settingValue('smtp_email_address');
                }
            }
            $this->load->library('email');
            // $this->load->library('sms');
            //Send mail to provider
            if (!empty($from_email) && isset($from_email))
            {
                $mail = $this->email->from($from_email)->to($provider_data['email'])->subject('Service Booking')->message($providerbody)->send();
            }

            $message = 'You have booked successfully';

            $token = $this->session->userdata('chat_token');
            /* history entry */
            $this->api->booking_wallet_history_flow($result, $token);

            /* history entry */
            $data = $this->api->get_book_info_b($result);
            $device_token = $this->api->get_device_info_multiple($data['provider_id'], 1);

            $user_name = $this->api->get_user_info($data['user_id'], 2);

            if ($service['user_type'] == "provider") {
                $this->load->model("ProvidersModel");
                $provider_token= $this->ProvidersModel->get($data['provider_id']);
                $user_type_value = 1;
            }
            else {
                $this->load->model("User_model");
                $provider_token = $this->User_model->get_user($data['provider_id']);
                $user_type_value = 2;
            }
            $text = $user_name['name'] . " has booked your Service";
            $this->send_push_notification($token, $result, $user_type_value, $msg = $text);

            $this->session->set_flashdata('success_message', $message);
        }
        else
        {
            $message = 'Sorry, something went wrong';
            $this->session->set_flashdata('error_message', $message);
        }

        $result = "COMPLETE_BOOKING";
        echo json_encode(['success' => true,'result'=>$result,'msg' => $message]);
        exit;
    }

    /**
     * @author Leo: booking checkout complete action
    */
    public function booking_checkout_complete() 
    {

        // caculate total price
        $serviceIds = json_decode($this->input->post("service_ids"));
        $this->load->model("Service_model");
        $params = [
            'md5_ids'=> $serviceIds
        ];
        $serviceList = $this->Service_model->getServiceList($params);
        $user_currency = get_user_currency();
        $user_currency_code = $user_currency["user_currency_code"];
        $totalPrice = 0;
        $serviceIds = [];
        if (count($serviceList) > 0) {
            foreach ($serviceList as $key => $service) {
                // code...
                array_push($serviceIds, $service['id']);
                $service_amount = get_gigs_currency($service['service_amount'], $service['currency_code'], $user_currency_code);
                $totalPrice += $service_amount;
            }
        }
       
        //check server side validation while booking
        $this->load->model('api_model', 'api');
        $wallet = $this->api->get_wallet($this->session->userdata('chat_token'));
        $wallet_amount = $wallet['wallet_amt'];
        /* check wallet amount */
        $totalFee = getCommissionFee() + getTransactionFee();
        // Leo: 18/8/21 - remove wallet check // back 
        if ($totalPrice * (1 + $totalFee) > $wallet_amount)
        {
            $message = 'You do not have sufficient balance in your wallet account. Please Topup to book the service.';
            $this->session->set_flashdata('error_message', $message);
            echo json_encode(['success' => false,'result'=>'ADD_WALLET','msg' => $message]);
            exit;
        }
        # will add available check here ...

        # ...

        extract($_POST);

        $nameArr = explode(" ", trim($name));
        $first_name = $nameArr[0];
        $last_name = $nameArr[1];

        // update flag added by Vadim
        $params = array();
        $params['add_flag'] = "0";
        $this->db->where(add_flag, '1');
        $this->db->update("invoice_ta", $params); //updata flag 0
        
        foreach ($serviceIds as $serviceId) {
            $inputs = array(
                'service_id' => $serviceId,
                'user_id' => $this->session->userdata("id"),
                'service_date' => date("Y-m-d", strtotime($service_date)),
                'service_time' => date("h:i:s", strtotime($service_time)),
                'location' => $location,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'notes' => $notes,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'country' => $country,
                'town' => $town,
                'street_addr_1' => $street_addr_1,
                'street_addr_2' => "",
                'email' => $email,
                'phone' => $phone
            );
        
            $inputs['currency_code'] = $user_currency_code;
            $this->load->model("Service_model", "Service");
            $service = $this->Service->get_service_details($serviceId);
          
            $serviceAmount = get_gigs_currency($service['service_amount'], $service['currency_code'], $user_currency_code);

            $inputs['amount'] = $serviceAmount * (1 + $totalFee);
            $inputs['provider_id'] = $service['user_id'];

            $category_id = $service['category'];
            $category_detail = $this->Service->get_category_details($category_id);

            //commssion fee and transaction fee calcu
            $com_fee = getCommissionFee();
            $trans_fee = getTransactionFee();
            // end commission fee and transaction fee.    
            
            // insert invoice table added by Vadim
            $inv_data = array();
            $inv_data['seller_code'] =  $inputs['provider_id'];
            $inv_data['service_code'] = $service['unique_id'] ;
            $inv_data['buyer_code'] = $this->session->userdata('id');
            $inv_data['category_code'] = $category_detail['unique_id'];
            $inv_data['first_name'] = $inputs['first_name'];
            $inv_data['last_name'] = $inputs['last_name'];
            $inv_data['address'] = $inputs['street_addr_1'];
            $inv_data['service_id'] = $inputs['service_id'];
            $inv_data['qty'] = 1;
            $inv_data['commission'] = $com_fee;
            $inv_data['add_flag'] = '1';
            $inv_data['created_date'] = date("Y-m-d");
            $inv_data['currency_code'] = $user_currency_code;
            $inv_data['transaction_fee'] = $trans_fee;

            $this->db->insert('invoice_ta', $inv_data);
            // new insert to invoice table end  
                  
            $result = $this->booking->bookService($inputs);
         
            if ($result != '')
            {
                if ($service['user_type'] == "provider") {
                    $this->load->model("ProvidersModel");
                    $provider_data= $this->ProvidersModel->get($this->input->post('provider_id'));
                }
                else {
                    $this->load->model("User_model");
                    $provider_data = $this->User_model->get_user($this->input->post('provider_id'));
                }

                $preview_link = base_url();
                $bodyid = 3;
                $tempbody_details = $this->templates_model->get_usertemplate_data($bodyid);
                $providerbody = $tempbody_details['template_content'];
                $providerbody = str_replace('{user_name}', $provider_data['name'], $providerbody);
                $providerbody = str_replace('{sitetitle}', $this->site_name, $providerbody);
                $providerbody = str_replace('{user_person}', $this->session->userdata('name') , $providerbody);
                $providerbody = str_replace('{service_title}', $service['service_title'], $providerbody);
                $providerbody = str_replace('{service_date}', $this->input->post('booking_date') , $providerbody);
                $providerbody = str_replace('{service_time}', $time, $providerbody);
                $providerbody = str_replace('{location_user}', $service['service_location'], $providerbody);
                $providerbody = str_replace('{preview_link}', $preview_link, $providerbody);

                $phpmail_config = settingValue('mail_config');
                if (isset($phpmail_config) && !empty($phpmail_config))
                {
                    if ($phpmail_config == "phpmail")
                    {
                        $from_email = settingValue('email_address');
                    }
                    else
                    {
                        $from_email = settingValue('smtp_email_address');
                    }
                }
                $this->load->library('email');
                // $this->load->library('sms');
                //Send mail to provider
                if (!empty($from_email) && isset($from_email))
                {
                    $mail = $this->email->from($from_email)->to($provider_data['email'])->subject('Service Booking')->message($providerbody)->send();
                }

                $message = 'You have booked successfully';

                $token = $this->session->userdata('chat_token');
                /* history entry */
                $this->api->booking_wallet_history_flow($result, $token);

                /* history entry */
                $data = $this->api->get_book_info_b($result);
                $device_token = $this->api->get_device_info_multiple($data['provider_id'], 1);

                $user_name = $this->api->get_user_info($data['user_id'], 2);

                if ($service['user_type'] == "provider") {
                    $this->load->model("ProvidersModel");
                    $provider_token= $this->ProvidersModel->get($data['provider_id']);
                    $user_type_value = 1;
                }
                else {
                    $this->load->model("User_model");
                    $provider_token = $this->User_model->get_user($data['provider_id']);
                    $user_type_value = 2;
                }
                $text = $user_name['name'] . " has booked your Service";
                $this->send_push_notification($token, $result, $user_type_value, $msg = $text);
                $this->session->set_flashdata('success_message', $message);
            }
            else
            {
                $message = 'Sorry, something went wrong';
                $this->session->set_flashdata('error_message', $message);
            }
        }
        
        $result = "COMPLETE_BOOKING";
        echo json_encode(['success' => true,'result'=>$result,'msg' => $message]);
        exit;
    }

    /**
     * @author Leo: create paypal booking
    */
    public function create_paypal_checkout_booking() {

        // caculate total price
        $serviceIds = json_decode($this->input->post("service_ids"));
        $this->load->model("Service_model");
        $params = [
            'md5_ids'=> $serviceIds
        ];
        $serviceList = $this->Service_model->getServiceList($params);
        $user_currency = get_user_currency();
        $user_currency_code = $user_currency["user_currency_code"];
        $totalPrice = 0;
        $serviceIds = [];
        if (count($serviceList) > 0) {
            foreach ($serviceList as $key => $service) {
                // code...
                array_push($serviceIds, $service['id']);
                $service_amount = get_gigs_currency($service['service_amount'], $service['currency_code'], $user_currency_code);
                $totalPrice += $service_amount;
            }
        }

        $orderValue = round($totalPrice/0.95, 2);
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = array(
            'intent' => 'CAPTURE',
            'application_context' => ['return_url' => base_url()."user-bookings",
                'cancel_url' => base_url()."service-checkout/"],
            'purchase_units' => [['amount' => ['currency_code' => $user_currency['user_currency_code'],
                'value' => $orderValue]]]
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
        echo json_encode($response->result);
    }

    /**
     * @author Leo: booking complete action
    */
    public function booking_checkout_complete_by_paypal($orderId) 
    {
        $serviceIds = json_decode($this->input->post("service_ids"));
        $this->load->model("Service_model");
        $params = [
            'md5_ids'=> $serviceIds
        ];
        $serviceList = $this->Service_model->getServiceList($params);
        $serviceIds = [];
        if (count($serviceList) > 0) {
            foreach ($serviceList as $key => $service) {
                // code...
                array_push($serviceIds, $service['id']);
            }
        }

        $user_currency = get_user_currency();
        $user_currency_code = $user_currency['user_currency_code'];

        extract($_POST);

        $nameArr = explode(" ", trim($name));
        $first_name = $nameArr[0];
        $last_name = $nameArr[1]; 

        foreach ($serviceIds as $serviceId) {
            $inputs = array(
                'service_id' => $serviceId,
                'service_date' => date("Y-m-d", strtotime($service_date)),
                'service_time' => date("h:i:s", strtotime($service_time)),
                'location' => $location,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'notes' => $notes,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'country' => $country,
                'town' => $town,
                'street_addr_1' => $street_addr_1,
                'street_addr_2' => "",
                'email' => $email,
                'phone' => $phone
            );
            if ($this->session->userdata('id')) {
                $inputs['user_id'] = $this->session->userdata("id");
            }

            $inputs['currency_code'] = $user_currency_code;
            
            $this->load->model("Service_model", "Service");
            $service = $this->Service->get_service_details($serviceId);
            $serviceAmount = get_gigs_currency($service['service_amount'], $service['currency_code'], $user_currency_code);

            $inputs['amount'] = $serviceAmount;
            $inputs['provider_id'] = $service['user_id'];

            $result = $this->booking->bookService($inputs);
            if ($result != '')
            {
                if ($service['user_type'] == "provider") {
                    $this->load->model("ProvidersModel");
                    $provider_data= $this->ProvidersModel->get($this->input->post('provider_id'));
                }
                else {
                    $this->load->model("User_model");
                    $provider_data = $this->User_model->get_user($this->input->post('provider_id'));
                }

                $preview_link = base_url();
                $bodyid = 3;
                $tempbody_details = $this->templates_model->get_usertemplate_data($bodyid);
                $providerbody = $tempbody_details['template_content'];
                $providerbody = str_replace('{user_name}', $provider_data['name'], $providerbody);
                $providerbody = str_replace('{sitetitle}', $this->site_name, $providerbody);
                $providerbody = str_replace('{user_person}', $this->session->userdata('name') , $providerbody);
                $providerbody = str_replace('{service_title}', $service['service_title'], $providerbody);
                $providerbody = str_replace('{service_date}', $this->input->post('booking_date') , $providerbody);
                $providerbody = str_replace('{service_time}', $time, $providerbody);
                $providerbody = str_replace('{location_user}', $service['service_location'], $providerbody);
                $providerbody = str_replace('{preview_link}', $preview_link, $providerbody);

                $phpmail_config = settingValue('mail_config');
                if (isset($phpmail_config) && !empty($phpmail_config))
                {
                    if ($phpmail_config == "phpmail")
                    {
                        $from_email = settingValue('email_address');
                    }
                    else
                    {
                        $from_email = settingValue('smtp_email_address');
                    }
                }
                $this->load->library('email');
                // $this->load->library('sms');
                //Send mail to provider
                if (!empty($from_email) && isset($from_email))
                {
                    $mail = $this->email->from($from_email)->to($provider_data['email'])->subject('Service Booking')->message($providerbody)->send();
                }

                $message = 'You have booked successfully';

                $token = $this->session->userdata('chat_token');

                if ($service['user_type'] == "provider") {
                    $this->load->model("ProvidersModel");
                    $provider_token= $this->ProvidersModel->get($data['provider_id']);
                    $user_type_value = 1;
                }
                else {
                    $this->load->model("User_model");
                    $provider_token = $this->User_model->get_user($data['provider_id']);
                    $user_type_value = 2;
                }

                if ($token) {
                    /* history entry */
                    $data = $this->api->get_book_info_b($result);
                    $device_token = $this->api->get_device_info_multiple($data['provider_id'], 1);

                    $user_name = $this->api->get_user_info($data['user_id'], 2);

                    $text = $user_name['name'] . " has booked your Service";
                    $this->send_push_notification($token, $result, $user_type_value, $msg = $text);
                }
                else {
                    $text = $name . " has booked your Service";
                    $this->send_push_notification(null, $result, $user_type_value, $msg = $text);
                }
                
                $this->session->set_flashdata('success_message', $message);
            }
            else
            {
                $message = 'Sorry, something went wrong';
                $this->session->set_flashdata('error_message', $message);
            }
        }

        $result = "COMPLETE_BOOKING";
        echo json_encode(['success' => true,'result'=>$result,'msg' => $message]);
        exit;
    }

    /**
     * @Leo --- not used --- old booking action --------------
    */
    public function book_service()
    {

        $user_currency = get_user_currency();
        $user_currency_code = $user_currency['user_currency_code'];

        removeTag($this->input->post());
        $time = $this->input->post('booking_time');
        $booking_time = explode('-', $time);
        $start_time = strtotime($booking_time[0]);
        $end_time = strtotime($booking_time[1]);
        $from_time = date('G:i:s', ($start_time));
        $to_time = date('G:i:s', ($end_time));

        $inputs = array();
        $service_id = $this->input->post('service_id'); // Package ID
        $records = $this->booking->get_service($service_id);
        $inputs['service_id'] = $service_id;
        $inputs['provider_id'] = $this->input->post('provider_id');
        $inputs['user_id'] = $this->session->userdata('id');
        $inputs['provider_id'] = $this->input->post('provider_id');
        $inputs['currency_code'] = $user_currency_code;

        $inputs['token'] = 'old type';
        $inputs['service_id'] = $service_id;

        $inputs['provider_id'] = $this->input->post('provider_id');
        $inputs['user_id'] = $this->session->userdata('id');
        $inputs['amount'] = get_gigs_currency($records['service_amount'], $records['currency_code'], $user_currency_code);
        $inputs['service_date'] = date('Y-m-d', strtotime($this->input->post('booking_date')));
        $inputs['location'] = $this->input->post('service_location');
        $inputs['latitude'] = $this->input->post('service_latitude');
        $inputs['longitude'] = $this->input->post('service_longitude');
        $inputs['from_time'] = $from_time;
        $inputs['to_time'] = $to_time;
        $inputs['notes'] = $this->input->post('notes');
        $inputs['args'] = 'no response that field ld flow';

        $query = $this->db->query('SELECT * FROM services WHERE id=' . $service_id);
        $result = $query->result();
        // (status 0 OR emp_id:session id)   //1  empid :10
        $row = $result['0'];
        $cat_id = $row->category;
        // echo $cat_id; die("===========");
        $inputs['category_id'] = $cat_id;

        //check server side validation while booking
        $this->load->model('api_model', 'api');
        $wallet = $this->api->get_wallet($this->session->userdata('chat_token'));

        $curren_wallet = $wallet['wallet_amt'];

        /* check wallet amount */

        if ($inputs['amount'] > $curren_wallet)
        {

            $message = 'You do not have sufficient balance in your wallet account. Please Topup to book the service.';
            $this->session->set_flashdata('error_message', $message);
            exit;
        }
        /* check wallet amount */

        $timestamp = strtotime($inputs['service_date']);
        $day = date('D', $timestamp);

        $inputs1 = $inputs['service_id'];

        $result = $this->api->get_service_id($inputs1);
        $provider_details = $this->api->provider_hours($result['user_id']);
        $availability_details = json_decode($provider_details['availability'], true);

        $alldays = false;
        foreach ($availability_details as $details)
        {

            if (isset($details['day']) && $details['day'] == 0)
            {

                if (isset($details['from_time']) && !empty($details['from_time']))
                {

                    if (isset($details['to_time']) && !empty($details['to_time']))
                    {
                        $from_time1 = $details['from_time'];
                        $to_time1 = $details['to_time'];
                        $alldays = true;
                        break;
                    }
                }
            }
        }

        if ($alldays == false)
        {

            if ($day == 'Mon')
            {
                $weekday = '1';
            }
            elseif ($day == 'Tue')
            {
                $weekday = '2';
            }
            elseif ($day == 'Wed')
            {
                $weekday = '3';
            }
            elseif ($day == 'Thu')
            {
                $weekday = '4';
            }
            elseif ($day == 'Fri')
            {
                $weekday = '5';
            }
            elseif ($day == 'Sat')
            {
                $weekday = '6';
            }
            elseif ($day == 'Sun')
            {
                $weekday = '7';
            }
            elseif ($day == '0')
            {
                $weekday = '0';
            }

            foreach ($availability_details as $availability)
            {

                if ($weekday == $availability['day'] && $availability['day'] != 0)
                {

                    $availability_day = $availability['day'];
                    $from_time1 = $availability['from_time'];
                    $to_time1 = $availability['to_time'];
                }
            }
        }

        if (!empty($from_time1))
        {
            $temp_start_time = $from_time1;
            $temp_end_time = $to_time1;
        }
        else
        {
            $message = 'Booking not available';
            $this->session->set_flashdata('error_message', $message);
            exit;
        }

        $start_time_array = '';
        $end_time_array = '';

        $timestamp_start = strtotime($temp_start_time);
        $timestamp_end = strtotime($temp_end_time);

        $timing_array = array();

        $counter = 1;

        $from_time_railwayhrs = date('H:i:s', ($timestamp_start));
        $to_time_railwayhrs = date('H:i:s', ($timestamp_end));

        $timestamp_start_railwayhrs = strtotime($from_time_railwayhrs);
        $timestamp_end_railwayhrs = strtotime($to_time_railwayhrs);

        $i = 1;
        while ($timestamp_start_railwayhrs < $timestamp_end_railwayhrs)
        {

            $temp_start_time_ampm = date('H:i:s', ($timestamp_start_railwayhrs));
            $temp_end_time_ampm = date('H:i:s', (($timestamp_start_railwayhrs) + 60 * 60 * 1));

            $timestamp_start_railwayhrs = strtotime($temp_end_time_ampm);

            $timing_array[] = array(
                'id' => $i,
                'start_time' => $temp_start_time_ampm,
                'end_time' => $temp_end_time_ampm
            );

            if ($counter > 24)
            {
                break;
            }

            $counter += 1;
            $i++;
        }

        // Booking availability
        $booking_from_time = $booking_time[0];
        $booking_end_time = $booking_time[1];

        $timestamp_from = strtotime($booking_from_time);
        $timestamp_to = strtotime($booking_end_time);

        $from_time_railwayhrs = date('H:i:s', ($timestamp_from));
        $to_time_railwayhrs = date('H:i:s', ($timestamp_to));

        $service_date = $inputs['service_date'];
        $service_id = $inputs['service_id'];

        $booking_count = $this->api->get_bookings($service_date, $service_id);

        $new_timingarray = array();

        if (is_array($booking_count) && empty($booking_count))
        {
            $new_timingarray = $timing_array;
        }
        elseif (is_array($booking_count) && $booking_count != '')
        {
            foreach ($timing_array as $timing)
            {
                $match_found = false;

                $explode_st_time = explode(':', $timing['start_time']);
                $explode_value = $explode_st_time[0];

                $explode_endtime = explode(':', $timing['end_time']);
                $explode_endval = $explode_endtime[0];

                if (strlen($explode_value) == 1)
                {
                    $timing['start_time'] = "0" . $explode_st_time[0] . ":" . $explode_st_time[1] . ":" . $explode_st_time[2];
                }

                if (strlen($explode_endval) == 1)
                {
                    $timing['end_time'] = "0" . $explode_endtime[0] . ":" . $explode_endtime[1] . ":" . $explode_endtime[2];
                }

                foreach ($booking_count as $bookings)
                {

                    if ($timing['start_time'] == $bookings['from_time'] && $timing['end_time'] == $bookings['to_time'])
                    {

                        $match_found = true;
                        break;
                    }
                }

                if ($match_found == false)
                {
                    $new_timingarray[] = array(
                        'start_time' => $timing['start_time'],
                        'end_time' => $timing['end_time']
                    );
                }
            }
        }

        $new_timingarray = array_filter($new_timingarray);

        $booking = false;
        foreach ($new_timingarray as $booked_time)
        {

            if ($booked_time['start_time'] == $from_time_railwayhrs && $booked_time['end_time'] == $to_time_railwayhrs)
            {
                $booking = true;
            }
        }

        if ($booking == false)
        {

            $message = 'Booking not available';
            $this->session->set_flashdata('error_message', $message);
            exit;
        }
        //check server side
        $result = $this->booking->booking_success($inputs);

        if ($result != '')
        {
            $this->data['user'] = $this->session->userdata();

            $service = $this->db->where('id', $service_id)->from('services')->get()->row_array();
            $this->data['service'] = $service;
            //$this->data['service'] = $this->db->where('id', $service_id)->from('services')->get()->row_array();
            $provider_data = $this->db->where('id', $this->input->post('provider_id'))->from('providers')->get()->row_array();
            $user_data = $this->db->where('id', $this->session->userdata('id'))->from('users')->get()->row_array();
            $this->data['provider'] = $provider_data;

            $preview_link = base_url();
            // $bodyid = 2;
            // $tempbody_details= $this->templates_model->get_usertemplate_data($bodyid);
            // $body = $tempbody_details['template_content'];
            // $body = str_replace('{user_name}', $this->session->userdata('name'), $body);
            // $body = str_replace('{sitetitle}',$this->site_name, $body);
            // $body = str_replace('{preview_link}',$preview_link, $body);
            // $body = str_replace('{service_title}',$service['service_title'], $body);
            // $body = str_replace('{location_user}',$service['service_location'], $body);
            $bodyid = 3;
            $tempbody_details = $this->templates_model->get_usertemplate_data($bodyid);
            $providerbody = $tempbody_details['template_content'];
            $providerbody = str_replace('{user_name}', $provider_data['name'], $providerbody);
            $providerbody = str_replace('{sitetitle}', $this->site_name, $providerbody);
            $providerbody = str_replace('{user_person}', $this->session->userdata('name') , $providerbody);
            $providerbody = str_replace('{service_title}', $service['service_title'], $providerbody);
            $providerbody = str_replace('{service_date}', $this->input->post('booking_date') , $providerbody);
            $providerbody = str_replace('{service_time}', $time, $providerbody);
            $providerbody = str_replace('{location_user}', $service['service_location'], $providerbody);
            $providerbody = str_replace('{preview_link}', $preview_link, $providerbody);

            $phpmail_config = settingValue('mail_config');
            if (isset($phpmail_config) && !empty($phpmail_config))
            {
                if ($phpmail_config == "phpmail")
                {
                    $from_email = settingValue('email_address');
                }
                else
                {
                    $from_email = settingValue('smtp_email_address');
                }
            }
            $this->load->library('email');
            // $this->load->library('sms');
            //Send mail to provider
            if (!empty($from_email) && isset($from_email))
            {
                $mail = $this->email->from($from_email)->to($provider_data['email'])->subject('Service Booking')->message($providerbody)->send();
            }

            // $body = $this->load->view('user/email/service_email', $this->data, true);
            // $phpmail_config = settingValue('mail_config');
            // if (isset($phpmail_config) && !empty($phpmail_config)) {
            // if ($phpmail_config == "phpmail") {
            // $from_email = settingValue('email_address');
            // } else {
            // $from_email = settingValue('smtp_email_address');
            // }
            // }
            // $this->load->library('email');
            // if (!empty($from_email) && isset($from_email)) {
            // $mail = $this->email
            // ->from($from_email)
            // ->to($this->session->userdata('email'))
            // ->subject('Service Booking')
            // ->message($body)
            // ->send();
            // }
            $message = 'You have booked successfully';

            $token = $this->session->userdata('chat_token');
            /* history entry */
            $this->api->booking_wallet_history_flow($result, $token);

            /* history entry */
            $data = $this->api->get_book_info_b($result);
            $device_token = $this->api->get_device_info_multiple($data['provider_id'], 1);

            $user_name = $this->api->get_user_info($data['user_id'], 2);

            $provider_token = $this->api->get_user_info($data['provider_id'], 1);
            $text = $user_name['name'] . " has booked your Service";
            $this->send_push_notification($token, $result, 1, $msg = $text);

            $this->session->set_flashdata('success_message', $message);
        }
        else
        {
            $message = 'Sorry, something went wrong';
            $this->session->set_flashdata('error_message', $message);
        }
        echo json_encode(['success' => true, 'msg' => $result]);
        exit;
    }

    /* stripe booking method OLD METHOD */

    public function stripe_payment()
    {

        $time = $this->input->post('booking_time');
        $booking_time = explode('-', $time);
        $start_time = strtotime($booking_time[0]);
        $end_time = strtotime($booking_time[1]);
        $from_time = date('G:i:s', ($start_time));
        $to_time = date('G:i:s', ($end_time));

        $inputs = array();
        $service_id = $this->input->post('service_id'); // Package ID
        $records = $this->booking->get_service($service_id);
        $inputs['service_id'] = $service_id;
        $inputs['provider_id'] = $this->input->post('provider_id');
        $inputs['user_id'] = $this->session->userdata('id');
        $inputs['provider_id'] = $this->input->post('provider_id');

        $inputs['token'] = $this->input->post('tokenid');
        $charges_array = array();
        $amount = (!empty($records['service_amount'])) ? $records['service_amount'] : 2;
        $amount = ($amount * 100);
        $charges_array['amount'] = $amount;
        $charges_array['currency'] = settings('currency');
        $charges_array['description'] = (!empty($records['service_amount'])) ? $records['service_amount'] : 'Booking';
        $charges_array['source'] = 'tok_visa';

        $result = $this->stripe->stripe_charges($charges_array);

        $result = json_decode($result, true);
        if (empty($result['error']))
        {
            $inputs['token'] = $result['id'];
            $inputs['service_id'] = $service_id;
            $inputs['provider_id'] = $this->input->post('provider_id');
            $inputs['user_id'] = $this->session->userdata('id');
            $inputs['amount'] = $records['service_amount'];
            $inputs['service_date'] = date('Y-m-d', strtotime($this->input->post('booking_date')));
            $inputs['location'] = $this->input->post('service_location');
            $inputs['latitude'] = $this->input->post('service_latitude');
            $inputs['longitude'] = $this->input->post('service_longitude');
            $inputs['from_time'] = $from_time;
            $inputs['to_time'] = $to_time;
            $inputs['notes'] = $this->input->post('notes');
            $inputs['args'] = json_encode($result);
            $result = $this->booking->booking_success($inputs);
            if ($result != '')
            {
                $message = 'You have booked successfully';
                $token = $this->session->userdata('chat_token');
                $data = $this->api->get_book_info_b($result);
                $device_token = $this->api->get_device_info_multiple($data['provider_id'], 1);
                $user_name = $this->api->get_user_info($data['user_id'], 2);
                $provider_token = $this->api->get_user_info($data['provider_id'], 1);
                $text = $user_name['name'] . " has booked your Service";
                $this->send_push_notification($token, $result, 1, $msg = $text);
                $this->session->set_flashdata('success_message', $message);
            }
            else
            {
                $message = 'Sorry, something went wrong';
                $this->session->set_flashdata('error_message', $message);
            }
        }
        else
        {
            $inputs['token'] = 'Issue - token_already_used';
            $message = 'Sorry, something went wrong';
            $this->session->set_flashdata('error_message', $message);
        }

        echo json_encode($result);
    }

    /* stripe booking method OLD METHOD */

    public function stripe_payments()
    {
        $inputs = array();
        $sub_id = $this->input->post('sub_id'); // Package ID
        $records = $this->subscription->get_subscription($sub_id);
        $inputs['subscription_id'] = $sub_id;
        $inputs['user_id'] = $this->session->userdata('id');
        $inputs['token'] = 'Free subscription';
        $inputs['args'] = '';
        $result = $this->subscription->subscription_success($inputs);
        if ($result)
        {
            $message = 'You have been subscribed';
            $this->session->set_flashdata('success_message', $message);
        }
        else
        {
            $message = 'Sorry, something went wrong';
            $this->session->set_flashdata('error_message', $message);
        }

        echo json_encode($result);
    }

    /* push notification */

    public function send_push_notification($token, $service_id, $type, $msg)
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
            }
            else
            {
                $user_info = $this->api->get_user_info($data['provider_id'], $type);
            }
            /* insert notification */
            $msg = ucfirst(strtolower($msg));
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
            /* apns push notification */
        }
        else
        {
            $this->token_error();
        }
    }

    /**
     * @author Vadim: insertion product fee 0.2p
    */
    public function insertion_pay_complete() 
    {

        // caculate total price            
            
        $user_currency_code = 'GBP';
        $totalPrice = 0.2;
           
        //check server side validation while booking
        $this->load->model('api_model', 'api');
        $wallet = $this->api->get_wallet($this->session->userdata('chat_token'));
        $wallet_amount = $wallet['wallet_amt'];
   

        if ($totalPrice > $wallet_amount)
        {
            $message = 'You do not have sufficient balance in your wallet account. Please Topup to book the service.';
            $this->session->set_flashdata('error_message', $message);
            echo json_encode(['success' => false,'result'=>'ADD_WALLET','msg' => $message]);
            exit;
        }   
        
        $token = $this->session->userdata('chat_token');
        /* history entry */
        $this->api->product_insert_wallet_history_flow($totalPrice,$user_currency_code ,$token);


        $result = "COMPLETE_Pay";
        echo json_encode(['success' => true,'result'=>$result,'msg' => $message]);
        exit;
      
    }

    /**
     * @author Vadim: insertion product fee 0.2p
    */

    public function priority_pay_complete() 
    
    {

        // caculate total price            
            
        $user_currency_code = 'GBP';
        $totalPrice = 12;
           
        //check server side validation while booking
        $this->load->model('api_model', 'api');
        $wallet = $this->api->get_wallet($this->session->userdata('chat_token'));
        $wallet_amount = $wallet['wallet_amt'];
             

        if ($totalPrice > $wallet_amount)
        {
            $message = 'You do not have sufficient balance in your wallet account. Please Topup to book the service.';
            $this->session->set_flashdata('error_message', $message);
            echo json_encode(['success' => false,'result'=>'ADD_WALLET','msg' => $message]);
            exit;
        } 
        
        $token = $this->session->userdata('chat_token');
        /* history entry */
        $this->api->priority_wallet_history_flow($totalPrice,$user_currency_code ,$token);


        $result = "COMPLETE_Pay";
        echo json_encode(['success' => true,'result'=>$result,'msg' => $message]);
        exit;
      
    }

}
