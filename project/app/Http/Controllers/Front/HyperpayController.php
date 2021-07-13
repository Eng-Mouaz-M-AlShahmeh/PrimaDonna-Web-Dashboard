<?php

namespace App\Http\Controllers\Front;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Generalsetting;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderTrack;
use App\Models\Pagesetting;
use App\Models\Product;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\VendorOrder;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Redirect;
use Stripe\Error\Card;
use URL;
use Validator;

class HyperpayController extends Controller
{
 
 
/*    
    public function sendSMS($orderNum, $phoneCus)
    {
         $success_url = action('Front\PaymentController@payreturn');
         
         
         
//Sending SMS To Buyer
       
if(strpos($phoneCus, "966") !== false)
{
$msg = "موقع بريمادونا: \r\n رقم الطلب: ".$orderNum;
$phone = str_replace("966 - ","966","".$phoneCus."");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.msegat.com/gw/sendsms.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, TRUE);
curl_setopt($ch, CURLOPT_POST, TRUE);
$fields = <<<EOT
{
"userName": "primadonna.ksa@gmail.com",
"numbers": "966533033568",
"userSender": "OTP",
"apiKey": "API_KEY",
"msg": "$msg"
}
EOT;
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
"Content-Type: application/json",));
$response = curl_exec($ch);
$info = curl_getinfo($ch);
}


//Sending SMS To Customer Service
/*
if(strpos($request['phone'], "966") !== false)
{
$msg = "موقع بريمادونا: \r\n رقم الطلب: ".$order->order_number;
//$phone = str_replace("966 - ","966",$request['phone']);
$phone = "966533033568";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.msegat.com/gw/sendsms.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, TRUE);
curl_setopt($ch, CURLOPT_POST, TRUE);
$fields = <<<EOT
{
"userName": "primadonna.ksa@gmail.com",
"numbers": "$phone",
"userSender": "PRIMADONNA",
"apiKey": "API_KEY",
"msg": "$msg"
}
EOT;
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
"Content-Type: application/json",));
$response = curl_exec($ch);
$info = curl_getinfo($ch);
}        
*/         
         
         
/*         
         
         return redirect($success_url)->with('success',"Successful Payment.");
        
        
    }
    */
    
    
    
    public function requestVisaMaster(Request $request)
    {
        //$form = $request->all(); 
        Session::put('requestSession', $request->all());
        
        if($request->pass_check) {
            $users = User::where('email','=',$request->personal_email)->get();
            if(count($users) == 0) {
                if ($request->personal_pass == $request->personal_confirm){
                    $user = new User;
                    $user->name = $request->personal_name; 
                    $user->email = $request->personal_email;   
                    $user->password = bcrypt($request->personal_pass);
                    $token = md5(time().$request->personal_name.$request->personal_email);
                    $user->verification_link = $token;
                    $user->affilate_code = md5($request->name.$request->email);
                    $user->emai_verified = 'Yes';
                    $user->save();
                    Auth::guard('web')->login($user);                     
                }else{
                    return redirect()->back()->with('unsuccess',"Confirm Password Doesn't Match.");     
                }
            }
            else {
                return redirect()->back()->with('unsuccess',"This Email Already Exist.");  
            }
        }


        if (!Session::has('cart')) {
            return redirect()->route('front.cart')->with('success',"You don't have any product to checkout.");
        }
        
        
        global $price;
        $price = $request->total;

        global $merchantTransactionId;
        $merchantTransactionId = Str::random(10);
        //$request->request->add(['order_number' => $merchantTransactionId]);
        
        global $customer_email;
        $customer_email = $request->email;
        
        global $customer_givenName;
        $customer_givenName = $request->name;
        
        global $customer_surname;
        $customer_surname = $request->name;
        
        global $billing_street1;
        $billing_street1 = $request->address;
        
        global $billing_city;
        $billing_city = $request->city;
        
        global $billing_state;
        $billing_state = $request->customer_country;
        
        global $billing_country;
        $billing_country = $request->billing_country;
        
        global $billing_postcode;
        $billing_postcode = $request->zip;
        
        global $curr;
        
        if (Session::has('currency')) 
        {
            global $curr;
            $curr = Currency::find(Session::get('currency'))->name;
        }
        else
        {
            global $curr;
            $curr = Currency::where('is_default','=',1)->first()->name;
        }
        
         
        function request() {
            global $price;
            global $curr;
            global $merchantTransactionId;
            global $customer_email;
            global $customer_givenName;
            global $customer_surname;
            global $billing_street1;
            global $billing_city;
            global $billing_state;
            global $billing_country;
            global $billing_postcode;
            
        	$url = "https://oppwa.com/v1/checkouts";
        	$data = "entityId=API_KEY" .
                        "&amount=" . $price .
                        "&currency=" . $curr .
                        "&paymentType=DB" .
                        "&merchantTransactionId=" . $merchantTransactionId .
                        "&customer.email=" . $customer_email .
                        "&customer.givenName=" . $customer_givenName .
                        "&customer.surname=" . $customer_surname .
                        "&billing.street1=" . $billing_street1 .      
                        "&billing.city=" . $billing_city .     
                        "&billing.state=" . $billing_state .         
                        "&billing.country=" . $billing_country .
                        "&billing.postcode=" . $billing_postcode;
        
        	$ch = curl_init();
        	curl_setopt($ch, CURLOPT_URL, $url);
        	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                           'Authorization:Bearer API_KEY'));
        	curl_setopt($ch, CURLOPT_POST, 1);
        	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        	$responseData = curl_exec($ch);
        	if(curl_errno($ch)) {
        		return curl_error($ch);
        	}
        	curl_close($ch);
        	return $responseData;
        }
        $responseData = request();
        
        $resArray = json_decode($responseData);
        
        $id = $resArray->id;
        
        return view('front.visamaster',compact('id', 'request', 'merchantTransactionId'))->withInput($request);
        
    }
    
    public function requestStcpay(Request $request)
    {
        //$form = $request;
        Session::put('requestSession', $request->all());
        
        if($request->pass_check) {
            $users = User::where('email','=',$request->personal_email)->get();
            if(count($users) == 0) {
                if ($request->personal_pass == $request->personal_confirm){
                    $user = new User;
                    $user->name = $request->personal_name; 
                    $user->email = $request->personal_email;   
                    $user->password = bcrypt($request->personal_pass);
                    $token = md5(time().$request->personal_name.$request->personal_email);
                    $user->verification_link = $token;
                    $user->affilate_code = md5($request->name.$request->email);
                    $user->emai_verified = 'Yes';
                    $user->save();
                    Auth::guard('web')->login($user);                     
                }else{
                    return redirect()->back()->with('unsuccess',"Confirm Password Doesn't Match.");     
                }
            }
            else {
                return redirect()->back()->with('unsuccess',"This Email Already Exist.");  
            }
        }


        if (!Session::has('cart')) {
            return redirect()->route('front.cart')->with('success',"You don't have any product to checkout.");
        }
        
        
        global $price;
        $price = $request->total;
        
        global $merchantTransactionId;
        $merchantTransactionId = Str::random(10);
        //$request->request->add(['order_number' => $merchantTransactionId]);
        
        global $customer_email;
        $customer_email = $request->email;
        
        global $customer_givenName;
        $customer_givenName = $request->name;
        
        global $customer_surname;
        $customer_surname = $request->name;
        
        global $billing_street1;
        $billing_street1 = $request->address;
        
        global $billing_city;
        $billing_city = $request->city;
        
        global $billing_state;
        $billing_state = $request->customer_country;
        
        global $billing_country;
        $billing_country = $request->billing_country;
        
        global $billing_postcode;
        $billing_postcode = $request->zip;
        
        global $curr;
        
        if (Session::has('currency')) 
        {
            $curr = Currency::find(Session::get('currency'))->name;
        }
        else
        {
            $curr = Currency::where('is_default','=',1)->first()->name;
        }
        
        function request() {
            global $price;
            global $curr;
            global $merchantTransactionId;
            global $customer_email;
            global $customer_givenName;
            global $customer_surname;
            global $billing_street1;
            global $billing_city;
            global $billing_state;
            global $billing_country;
            global $billing_postcode;
            
        	$url = "https://oppwa.com/v1/checkouts";
        	$data = "entityId=API_KEY" .
                        "&amount=" . $price .
                        "&currency=" . $curr .
                        "&paymentType=DB" .
                        "&merchantTransactionId=" . $merchantTransactionId .
                        "&customer.email=" . $customer_email .
                        "&customer.givenName=" . $customer_givenName .
                        "&customer.surname=" . $customer_surname .
                        "&billing.street1=" . $billing_street1 .      
                        "&billing.city=" . $billing_city .     
                        "&billing.state=" . $billing_state .         
                        "&billing.country=" . $billing_country .
                        "&billing.postcode=" . $billing_postcode;
        
        	$ch = curl_init();
        	curl_setopt($ch, CURLOPT_URL, $url);
        	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                           'Authorization:Bearer API_KEY'));
        	curl_setopt($ch, CURLOPT_POST, 1);
        	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        	$responseData = curl_exec($ch);
        	if(curl_errno($ch)) {
        		return curl_error($ch);
        	}
        	curl_close($ch);
        	return $responseData;
        }
        $responseData = request();
        
        $resArray = json_decode($responseData);
        
        $id = $resArray->id;
        
        return view('front.stcpay', compact('id', 'request', 'merchantTransactionId'))->withInput($request);
        
    }
    
    public function requestMada(Request $request)
    {
        //$form = $request;
        Session::put('requestSession', $request->all());
        
        if($request->pass_check) {
            $users = User::where('email','=',$request->personal_email)->get();
            if(count($users) == 0) {
                if ($request->personal_pass == $request->personal_confirm){
                    $user = new User;
                    $user->name = $request->personal_name; 
                    $user->email = $request->personal_email;   
                    $user->password = bcrypt($request->personal_pass);
                    $token = md5(time().$request->personal_name.$request->personal_email);
                    $user->verification_link = $token;
                    $user->affilate_code = md5($request->name.$request->email);
                    $user->emai_verified = 'Yes';
                    $user->save();
                    Auth::guard('web')->login($user);                     
                }else{
                    return redirect()->back()->with('unsuccess',"Confirm Password Doesn't Match.");     
                }
            }
            else {
                return redirect()->back()->with('unsuccess',"This Email Already Exist.");  
            }
        }


        if (!Session::has('cart')) {
            return redirect()->route('front.cart')->with('success',"You don't have any product to checkout.");
        }
        
        
        global $price;
        $price = $request->total;
        
         global $merchantTransactionId;
        $merchantTransactionId = Str::random(10);
        //$request->request->add(['order_number' => $merchantTransactionId]);
        
        global $customer_email;
        $customer_email = $request->email;
        
        global $customer_givenName;
        $customer_givenName = $request->name;
        
        global $customer_surname;
        $customer_surname = $request->name;
        
        global $billing_street1;
        $billing_street1 = $request->address;
        
        global $billing_city;
        $billing_city = $request->city;
        
        global $billing_state;
        $billing_state = $request->customer_country;
        
        global $billing_country;
        $billing_country = $request->billing_country;
        
        global $billing_postcode;
        $billing_postcode = $request->zip;
        
        global $curr;
        
        if (Session::has('currency')) 
        {
            $curr = Currency::find(Session::get('currency'))->name;
        }
        else
        {
            $curr = Currency::where('is_default','=',1)->first()->name;
        }
        
        function request() {
            global $price;
            global $curr;
            global $merchantTransactionId;
            global $customer_email;
            global $customer_givenName;
            global $customer_surname;
            global $billing_street1;
            global $billing_city;
            global $billing_state;
            global $billing_country;
            global $billing_postcode;
            
        	$url = "https://oppwa.com/v1/checkouts";
        	$data = "entityId=API_KEY" .
                        "&amount=" . $price .
                        "&currency=" . $curr .
                        "&paymentType=DB" .
                        "&merchantTransactionId=" . $merchantTransactionId .
                        "&customer.email=" . $customer_email .
                        "&customer.givenName=" . $customer_givenName .
                        "&customer.surname=" . $customer_surname .
                        "&billing.street1=" . $billing_street1 .      
                        "&billing.city=" . $billing_city .     
                        "&billing.state=" . $billing_state .         
                        "&billing.country=" . $billing_country .
                        "&billing.postcode=" . $billing_postcode;
        
        	$ch = curl_init();
        	curl_setopt($ch, CURLOPT_URL, $url);
        	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                           'Authorization:Bearer API_KEY'));
        	curl_setopt($ch, CURLOPT_POST, 1);
        	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        	$responseData = curl_exec($ch);
        	if(curl_errno($ch)) {
        		return curl_error($ch);
        	}
        	curl_close($ch);
        	return $responseData;
        }
        $responseData = request();
        
        $resArray = json_decode($responseData);
        
        $id = $resArray->id;
        
        return view('front.mada', compact('id', 'request', 'merchantTransactionId'))->withInput($request);
        
    }
    


    public function store(Request $request){

        if($request->pass_check) {
            $users = User::where('email','=',$request->personal_email)->get();
            if(count($users) == 0) {
                if ($request->personal_pass == $request->personal_confirm){
                    $user = new User;
                    $user->name = $request->personal_name; 
                    $user->email = $request->personal_email;   
                    $user->password = bcrypt($request->personal_pass);
                    $token = md5(time().$request->personal_name.$request->personal_email);
                    $user->verification_link = $token;
                    $user->affilate_code = md5($request->name.$request->email);
                    $user->email_verified = 'Yes';
                    $user->save();
                    Auth::guard('web')->login($user);                     
                }else{
                    return redirect()->back()->with('unsuccess',"Confirm Password Doesn't Match.");     
                }
            }
            else {
                return redirect()->back()->with('unsuccess',"This Email Already Exist.");  
            }
        }
        
        if (!Session::has('cart')) {
            return redirect()->route('front.cart')->with('success',"You don't have any product to checkout.");
        }
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
            if (Session::has('currency')) 
            {
              $curr = Currency::find(Session::get('currency'));
            }
            else
            {
                $curr = Currency::where('is_default','=',1)->first();
            }

        $settings = Generalsetting::findOrFail(1);
        $order = new Order;
        $success_url = action('Front\PaymentController@payreturn');
        $item_name = $settings->title." Order";
        $item_number = Str::random(10);
        $item_amount = $request->total;

        $validator = Validator::make($request->all(),[
                        'cardNumber' => 'required',
                        'cardCVC' => 'required',
                        'month' => 'required',
                        'year' => 'required',
                    ]);

        if ($validator->passes()) {

            $stripe = Stripe::make(Config::get('services.stripe.secret'));
            try{
                $token = $stripe->tokens()->create([
                    'card' =>[
                            'number' => $request->cardNumber,
                            'exp_month' => $request->month,
                            'exp_year' => $request->year,
                            'cvc' => $request->cardCVC,
                        ],
                    ]);
                if (!isset($token['id'])) {
                    return back()->with('error','Token Problem With Your Token.');
                }

                $charge = $stripe->charges()->create([
                    'card' => $token['id'],
                    'currency' => $curr->name,
                    'amount' => $item_amount,
                    'description' => $item_name,
                    ]);

                if ($charge['status'] == 'succeeded') {
                        foreach($cart->items as $key => $prod)
                        {
                            if(!empty($prod['item']['license']) && !empty($prod['item']['license_qty']))
                            {
                                    foreach($prod['item']['license_qty']as $ttl => $dtl)
                                    {
                                        if($dtl != 0)
                                        {
                                            $dtl--;
                                            $produc = Product::findOrFail($prod['item']['id']);
                                            $temp = $produc->license_qty;
                                            $temp[$ttl] = $dtl;
                                            $final = implode(',', $temp);
                                            $produc->license_qty = $final;
                                            $produc->update();
                                            $temp =  $produc->license;
                                            $license = $temp[$ttl];
                                            $oldCart = Session::has('cart') ? Session::get('cart') : null;
                                            $cart = new Cart($oldCart);
                                            $cart->updateLicense($prod['item']['id'],$license);  
                                            Session::put('cart',$cart);
                                            break;
                                        }                    
                                    }
                            }
                        }
                    $order['user_id'] = $request->user_id;
                    $order['cart'] = utf8_encode(bzcompress(serialize($cart), 9));
                    $order['totalQty'] = $request->totalQty;
                    $order['pay_amount'] = round($item_amount / $curr->value, 2);
                    $order['method'] = "Stripe";
                    $order['customer_email'] = $request->email;
                    $order['customer_name'] = $request->name;
                    $order['customer_phone'] = $request->phone;
                    $order['order_number'] = $item_number;
                    $order['shipping'] = $request->shipping;
                    $order['pickup_location'] = $request->pickup_location;
                    $order['customer_address'] = $request->address;
                    $order['customer_country'] = $request->customer_country;
                    $order['customer_city'] = $request->city;
                    $order['customer_zip'] = $request->zip;
                    $order['shipping_email'] = $request->shipping_email;
                    $order['shipping_name'] = $request->shipping_name;
                    $order['shipping_phone'] = $request->shipping_phone;
                    $order['shipping_address'] = $request->shipping_address;
                    $order['shipping_country'] = $request->shipping_country;
                    $order['shipping_city'] = $request->shipping_city;
                    $order['shipping_zip'] = $request->shipping_zip;
                    $order['order_note'] = $request->order_notes;
                    $order['coupon_code'] = $request->coupon_code;
                    $order['coupon_discount'] = $request->coupon_discount;
                    $order['payment_status'] = "Completed";
                    $order['txnid'] = $charge['balance_transaction'];
                    $order['charge_id'] = $charge['id'];
                    $order['currency_sign'] = $curr->sign;
                    $order['currency_value'] = $curr->value;
                    $order['shipping_cost'] = $request->shipping_cost;
                    $order['packing_cost'] = $request->packing_cost;
                    $order['tax'] = $request->tax;
                    $order['dp'] = $request->dp;
                    $order['vendor_shipping_id'] = $request->vendor_shipping_id;
                    $order['vendor_packing_id'] = $request->vendor_packing_id;
                    
                    if($order['dp'] == 1)
                    {
                        $order['status'] = 'completed';
                    }
                    if (Session::has('affilate')) 
                    {
                        $val = $request->total / $curr->value;
                        $val = $val / 100;
                        $sub = $val * $gs->affilate_charge;
                        $order['affilate_user'] = Session::get('affilate');
                        $order['affilate_charge'] = $sub;
                    }
                    $order->save();

                if($order->dp == 1){
                    $track = new OrderTrack;
                    $track->title = 'Completed';
                    $track->text = 'Your order has completed successfully.';
                    $track->order_id = $order->id;
                    $track->save();
                }
                else {
                    $track = new OrderTrack;
                    $track->title = 'Pending';
                    $track->text = 'You have successfully placed your order.';
                    $track->order_id = $order->id;
                    $track->save();
                }

                    
                    $notification = new Notification;
                    $notification->order_id = $order->id;
                    $notification->save();
                    if($request->coupon_id != "")
                    {
                       $coupon = Coupon::findOrFail($request->coupon_id);
                       $coupon->used++;
                       if($coupon->times != null)
                       {
                            $i = (int)$coupon->times;
                            $i--;
                            $coupon->times = (string)$i;
                       }
                        $coupon->update();

                    }
        foreach($cart->items as $prod)
        {
            $x = (string)$prod['size_qty'];
            if(!empty($x))
            {
                $product = Product::findOrFail($prod['item']['id']);
                $x = (int)$x;
                $x = $x - $prod['qty'];
                $temp = $product->size_qty;
                $temp[$prod['size_key']] = $x;
                $temp1 = implode(',', $temp);
                $product->size_qty =  $temp1;
                $product->update();               
            }
        }


        foreach($cart->items as $prod)
        {
            $x = (string)$prod['stock'];
            if($x != null)
            {

                $product = Product::findOrFail($prod['item']['id']);
                $product->stock =  $prod['stock'];
                $product->update();  
                if($product->stock <= 5)
                {
                    $notification = new Notification;
                    $notification->product_id = $product->id;
                    $notification->save();                    
                }              
            }
        }

        $notf = null;

        foreach($cart->items as $prod)
        {
            if($prod['item']['user_id'] != 0)
            {
                $vorder =  new VendorOrder;
                $vorder->order_id = $order->id;
                $vorder->user_id = $prod['item']['user_id'];
                $notf[] = $prod['item']['user_id'];
                $vorder->qty = $prod['qty'];
                $vorder->price = $prod['price'];
                $vorder->order_number = $order->order_number;             
                $vorder->save();
            }

        }

        if(!empty($notf))
        {
            $users = array_unique($notf);
            foreach ($users as $user) {
                $notification = new UserNotification;
                $notification->user_id = $user;
                $notification->order_number = $order->order_number;
                $notification->save();    
            }
        }

        $gs = Generalsetting::find(1);

        //Sending Email To Buyer

        if($gs->is_smtp == 1)
        {
        $data = [
            'to' => $request->email,
            'type' => "new_order",
            'cname' => $request->name,
            'oamount' => "",
            'aname' => "",
            'aemail' => "",
            'wtitle' => "",
            'onumber' => $order->order_number,
        ];

        $mailer = new GeniusMailer();
        $mailer->sendAutoOrderMail($data,$order->id);            
        }
        else
        {
           $to = $request->email;
           $subject = "Your Order Placed!!";
           $msg = "Hello ".$request->name."!\nYou have placed a new order.\nYour order number is ".$order->order_number.".Please wait for your delivery. \nThank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
           mail($to,$subject,$msg,$headers);            
        }
        //Sending Email To Admin
        if($gs->is_smtp == 1)
        {
            $data = [
                'to' => Pagesetting::find(1)->contact_email,
                'subject' => "New Order Recieved!!",
                'body' => "Hello Admin!<br>Your store has received a new order.<br>Order Number is ".$order->order_number.".Please login to your panel to check. <br>Thank you.",
            ];

            $mailer = new GeniusMailer();
            $mailer->sendCustomMail($data);            
        }
        else
        {
           $to = Pagesetting::find(1)->contact_email;
           $subject = "New Order Recieved!!";
           $msg = "Hello Admin!\nYour store has recieved a new order.\nOrder Number is ".$order->order_number.".Please login to your panel to check. \nThank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
           mail($to,$subject,$msg,$headers);
        }



            Session::put('temporder_id',$order->id);
            Session::put('tempcart',$cart);
            Session::forget('cart');

            Session::forget('already');
            Session::forget('coupon');
            Session::forget('coupon_total');
            Session::forget('coupon_total1');
            Session::forget('coupon_percentage');
                    
                    return redirect($success_url);
                }
                
            }catch (Exception $e){
                return back()->with('unsuccess', $e->getMessage());
            }catch (\Cartalyst\Stripe\Exception\CardErrorException $e){
                return back()->with('unsuccess', $e->getMessage());
            }catch (\Cartalyst\Stripe\Exception\MissingParameterException $e){
                return back()->with('unsuccess', $e->getMessage());
            }
        }
        return back()->with('unsuccess', 'Please Enter Valid Credit Card Informations.');
    }


    // Capcha Code Image
    private function  code_image()
    {
        $actual_path = str_replace('project','',base_path());
        $image = imagecreatetruecolor(200, 50);
        $background_color = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image,0,0,200,50,$background_color);

        $pixel = imagecolorallocate($image, 0,0,255);
        for($i=0;$i<500;$i++)
        {
            imagesetpixel($image,rand()%200,rand()%50,$pixel);
        }

        $font = $actual_path.'assets/front/fonts/NotoSans-Bold.ttf';
        $allowed_letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $length = strlen($allowed_letters);
        $letter = $allowed_letters[rand(0, $length-1)];
        $word='';
        //$text_color = imagecolorallocate($image, 8, 186, 239);
        $text_color = imagecolorallocate($image, 0, 0, 0);
        $cap_length=6;// No. of character in image
        for ($i = 0; $i< $cap_length;$i++)
        {
            $letter = $allowed_letters[rand(0, $length-1)];
            imagettftext($image, 25, 1, 35+($i*25), 35, $text_color, $font, $letter);
            $word.=$letter;
        }
        $pixels = imagecolorallocate($image, 8, 186, 239);
        for($i=0;$i<500;$i++)
        {
            imagesetpixel($image,rand()%200,rand()%50,$pixels);
        }
        session(['captcha_string' => $word]);
        imagepng($image, $actual_path."assets/images/capcha_code.png");
    }

}
