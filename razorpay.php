<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Razorpay extends CI_Controller {
    public function __construct() {
      parent::__construct(); 
        $this->load->model('Common_model','common');
    }



  public function index() {
    $this->checkout();
  }

  public function checkout() {
        $data['title']              = 'Checkout payment | Infovistar';  
        $data['callback_url']       = base_url().'razorpay/callback';
        $data['surl']               = base_url().'razorpay/success';;
        $data['furl']               = base_url().'razorpay/failed';;
        $data['currency_code']      = 'INR';
       
        $this->load->view('check-out', $data);
    }

    // initialized cURL Request
    private function curl_handler($payment_id, $amount)  {
        $url            = 'https://api.razorpay.com/v1/payments/'.$payment_id.'/capture';
        $key_id         = "rzp_test_DSmmh9cghFmEvH";
        $key_secret     = "BUQz8H5ptbTYZsXE32hgISco";
        $fields_string  = "amount=$amount";
        //cURL Request
        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $key_id.':'.$key_secret);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        return $ch;
    }   
        
    // callback method
    public function callback() {
        $email = $this->input->post('emailadd');
        if(empty($this->session->userdata('user'))){
           $is = $this->common->isExist('user',array('email'=>$email));
               // _dx($is);
           if ($is['status']) {
                $this->session->set_userdata('user',$is['response'][0]);
           }else{
                $insert = array(
                    'name' => $this->input->post('name'),
                    'initials' => ucfirst(substr($this->input->post('name'),0,1)),
                    'email' => $this->input->post('emailadd'),
                    'mobile' => $this->input->post('mobile'),
                    'password' => md5('changemenow'),
                    'xyz' => base64_encode('changemenow'),
                    // 'term_condition' => $this->input->post('term'),
                );
                $res = $this->common->insertData('user',$insert);
                $channel = $this->session->userdata('channel');
                if($res['status']){
                    
                    $data = array(
                        'userid'=>$res['insert_id'],
                        'channel'=>'',
                        'created_on'=>date('Y-m-d H:i:s')
                    );
                    $this->common->insertData('user_profile',$data);
                    $result = $this->common->getSelectedOneDataWithCondition('user',['*'],['id'=>$res['insert_id']]);
                    $this->session->set_userdata('user',$result);
                }
           }
          $_POST['uid'] = $this->session->userdata('user')['id'];
        }else{
            $_POST['uid'] = $this->input->post('uid') == '' ? $this->session->userdata('user')['id'] : $this->input->post('uid');
        }


        $ip_address = $this->get_ip_address();
        if (!empty($this->input->post('razorpay_payment_id')) && !empty($this->input->post('merchant_order_id'))) {
            $razorpay_payment_id = $this->input->post('razorpay_payment_id');
            $merchant_order_id = $this->input->post('merchant_order_id');
            $course_id = $this->input->post('cid');
            $user_id = $this->input->post('uid');
            $currency_code = $this->input->post('currency_code') ? $this->input->post('currency_code') : 'INR';
            $course = $this->common->getSelectedOneDataWithCondition('course',['*'],['id'=>$course_id]);

            $insertData = array(
                'order_id'  =>  $merchant_order_id,
                'user_id'   =>  $user_id,
                'course_id' =>  $course_id,
                'coupon'    =>  '',
                'currency'    =>  $currency_code,
                'price'     =>  $this->input->post('merchant_amount'),
                'discount'  =>  '0',
                'to_be_paid'    =>  $course['price_inr'],
                'paid'  =>  $this->input->post('merchant_amount'),
                'platform_fee'  =>  0,
                'payment_status'    =>  'pending',
                'gateway_order_id'  =>  $this->input->post('merchant_trans_id'),
                'geteway_payment_id'    =>  $razorpay_payment_id,
                'ip_address'    =>  $ip_address,
                'payment_on'    =>  date('Y-m-d H:i:s'),
            );
            // _dx($insertData);
            $exist = $this->common->isExist('sell_order',['geteway_payment_id'=>$razorpay_payment_id]);
            if ($exist['status']) {
                $res['status'] = true;
                $res['insert_id'] = $exist['response'][0]['id'];
            }else{
                $res = $this->common->insertData('sell_order',$insertData);
            }
            if ($res['status']) {
                $tid = $res['insert_id'];
                    
                    $this->session->set_flashdata('razorpay_payment_id', $this->input->post('razorpay_payment_id'));
                    $this->session->set_flashdata('merchant_order_id', $this->input->post('merchant_order_id'));

                    
                    $amount = $this->input->post('merchant_total');

                    $success = false;
                    $error = '';

                    try {                
                        $ch = $this->curl_handler($razorpay_payment_id, $amount);
                        //execute post
                        $result = curl_exec($ch);
                        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        if ($result === false) {
                            $success = false;
                            $error = 'Curl error: '.curl_error($ch);
                        } else {
                            $response_array = json_decode($result, true);
                            // _dx($response_array);
                                //Check success response
                                if ($http_status === 200 and isset($response_array['error']) === false) {
                                    $updateData = array(
                                        'payment_status'     =>  $response_array['status'],
                                        'payment_on'     =>  $response_array['created_at'],
                                        'response'  =>  json_encode($response_array),
                                        'platform_fee'     =>  $response_array['fee'],
                                        'payment_referanceId'   =>  json_encode($response_array['acquirer_data']),
                                        'payment_mode'  =>  $response_array['method'],
                                        'response_text'  =>  'In-Process',
                                    );
                                    $this->common->update('sell_order',$updateData,array('id'=>$tid));
                                    $this->UserCourseAccess($course,$user_id,$tid,$ip_address);
                                    $success = true;
                                } else {
                                    $success = false;
                                    if (!empty($response_array['error']['code'])) {
                                        $error = $response_array['error']['code'].':'.$response_array['error']['description'];
                                    } else {
                                        $error = 'RAZORPAY_ERROR:Invalid Response <br/>'.$result;
                                    }
                                    $updateData = array(
                                        'response'  =>  json_encode($response_array),
                                        'response_text'  =>  $error
                                    );
                                    $this->common->update('sell_order',$updateData,array('id'=>$tid));
                                }
                        }
                        //close curl connection
                        curl_close($ch);
                    } catch (Exception $e) {
                        $success = false;
                        $error = 'Request to Razorpay Failed';
                        $updateData = array(
                                        // 'response'  =>  json_encode($response_array),
                                        'response_text'  =>  $error,
                                    );
                        $this->common->update('sell_order',$updateData,array('id'=>$tid));
                    }
                    
                    if ($success === true) {
                       
                        redirect($this->input->post('merchant_surl_id'));

                    } else {
                        redirect($this->input->post('merchant_furl_id'));
                    }
                
            }
        } else {
            $error = 'An error occured. Contact site administrator, please!';
            $insertData = array(
                'order_id'  =>  $this->input->post('merchant_order_id'),
                'user_id'   =>  $this->input->post('uid'),
                'course_id' =>  $this->input->post('cid'),
                'coupon'    =>  '',
                'currency'    =>  $this->input->post('currency_code'),
                'price'     =>  $this->input->post('merchant_amount'),
                'discount'  =>  '0',
                'to_be_paid'    =>  $this->input->post('merchant_amount'),
                'paid'  =>  $this->input->post('merchant_amount'),
                'platform_fee'  =>  0,
                'payment_status'    =>  'pending',
                'gateway_order_id'  =>  $this->input->post('merchant_trans_id'),
                'geteway_payment_id'    =>  $this->input->post('razorpay_payment_id'),
                'response_text'  =>  $error,
                'ip_address'  =>  $ip_address,
            );
            $res = $this->common->insertData('sell_order',$insertData);
        }
    } 


    public function success() {
        // _dx($this->session->userdata('user'));
        $data['title'] = 'Razorpay Success | Lowcademy';
        echo "<h4>Your transaction is successful</h4>";  
        echo "<br/>";
        echo "Transaction ID: ".$this->session->flashdata('razorpay_payment_id');
        echo "<br/>";
        echo "Order ID: ".$this->session->flashdata('merchant_order_id');
    }  
    public function failed() {
        $data['title'] = 'Razorpay Failed | Lowcademy';  
        echo "<h4>Your transaction got Failed</h4>";            
        echo "<br/>";
        echo "Transaction ID: ".$this->session->flashdata('razorpay_payment_id');
        echo "<br/>";
        echo "Order ID: ".$this->session->flashdata('merchant_order_id');
    }

    public function UserCourseAccess($course,$user_id,$order_id,$ip_address){

        $insertData = array(
                'sell_order_id'  =>  $order_id,
                'user_id'   =>  $user_id,
                'course_id' =>  $course['id'],
                'ip'    =>  $ip_address,
                'is_money_back_protected'    =>  $course['is_money_back_protected'],
                'money_back_protection_removed_on'     =>  0,
                'material_id_watched'  =>  '',
                'is_shared_login'    =>  0,
                'is_active'  =>  1
            );
            $res = $this->common->insertData('user_course_access',$insertData);
            return $res;
    }



    public function get_ip_address()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP')) 
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

}
