@extends('layouts.front')
@section('content')

<!-- Breadcrumb Area Start -->
<div class="breadcrumb-area">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <ul class="pages">
          <li>
            <a href="{{ route('front.index') }}">
              {{ $langg->lang17 }}
            </a>
          </li>
          <li>
            <a href="{{ route('front.cart') }}">
              {{ $langg->lang121 }}
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- Breadcrumb Area End -->

<!-- Cart Area Start -->
<section class="cartpage">
  <div class="container">
    <div class="row">
      <div class="col-lg-8">
        <div class="left-area">
          <div class="cart-table">
            <table class="table">
              @include('includes.form-success')
                <thead>
                    <tr>
                        <th>{{ $langg->lang77 }}</th>
                      <th>{{ $langg->lang122 }}</th>
                      <th>{{ $langg->lang539 }}</th>
                      <th>{{ $langg->lang125 }}</th>
                      <th>{{ $langg->lang126 }}</th>
                      <th><i class="icofont-close-squared-alt"></i></th>
                    </tr>
                  </thead>
                  <tbody>
                    @if(Session::has('cart'))

                    @foreach($products as $product)
                    <tr class="cremove{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}">
                        
                        <td>
                            {{ $product['item']['sku']  }}
                        </td>
                        
                      <td class="product-img">
                        <div class="item">
                          <img src="{{ $product['item']['photo'] ? asset('assets/images/products/'.$product['item']['photo']):asset('assets/images/noimage.png') }}" alt="">
                          <p class="name"><a href="{{ route('front.product', $product['item']['slug']) }}">{{mb_strlen($product['item']['name'],'utf-8') > 35 ? mb_substr($product['item']['name'],0,35,'utf-8').'...' : $product['item']['name']}}</a>  
                          
                          </p>
                        </div>
                      </td>
                                            <td>
                                                @if(!empty($product['size']))
                                                <b>{{ $langg->lang312 }}</b>: {{ $product['item']['measure'] }}{{str_replace('-',' ',$product['size'])}} <br>
                                                @endif
                                                @if(!empty($product['color']))
                                                <div class="d-flex mt-2">
                                                <b>{{ $langg->lang313 }}</b>:  <span id="color-bar" style="border: 10px solid #{{$product['color'] == "" ? "white" : $product['color']}};"></span>
                                                </div>
                                                @endif

                                                    @if(!empty($product['keys']))

                                                    @foreach( array_combine(explode(',', $product['keys']), explode(',', $product['values']))  as $key => $value)

                                                        <b>{{ ucwords(str_replace('_', ' ', $key))  }} : </b> {{ $value }} <br>
                                                    @endforeach

                                                    @endif

                                                  </td>




                      <td class="unit-price quantity">
                        <p class="product-unit-price">

                          {{ App\Models\Product::convertPrice($product['item_price']) }}                        
                        </p>
          @if($product['item']['type'] == 'Physical')

                          <div class="qty">
                              <ul>
              <input type="hidden" class="prodid" value="{{$product['item']['id']}}">  
              <input type="hidden" class="itemid" value="{{$product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values'])}}">     
              <input type="hidden" class="size_qty" value="{{$product['size_qty']}}">     
              <input type="hidden" class="size_price" value="{{$product['size_price']}}">   
                                <li>
                                  <span class="qtminus1 reducing">
                                    <i class="icofont-minus"></i>
                                  </span>
                                </li>
                                <li>
                                  <span class="qttotal1" id="qty{{$product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values'])}}">{{ $product['qty'] }}</span>
                                </li>
                                <li>
                                  <span class="qtplus1 adding">
                                    <i class="icofont-plus"></i>
                                  </span>
                                </li>
                              </ul>
                          </div>
        @endif


                      </td>

                            @if($product['size_qty'])
                            <input type="hidden" id="stock{{$product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values'])}}" value="{{$product['size_qty']}}">
                            @elseif($product['item']['type'] != 'Physical') 
                            <input type="hidden" id="stock{{$product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values'])}}" value="1">
                            @else
                            <input type="hidden" id="stock{{$product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values'])}}" value="{{$product['stock']}}">
                            @endif

                      <td class="total-price">
                        <p id="prc{{$product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values'])}}">
                          {{ App\Models\Product::convertPrice($product['price']) }}                 
                        </p>
                      </td>
                      <td>
                        <span class="removecart cart-remove" data-class="cremove{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}" data-href="{{ route('product.cart.remove',$product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values'])) }}"><i class="icofont-ui-delete"></i> </span>
                      </td>
                    </tr>
                    @endforeach
                    @endif
                  </tbody>
            </table>
          </div>
        </div>
      </div>
      
      
      
      
      
      
      
	
	
	
	
      @if(Session::has('cart'))
      <div class="col-lg-4">
        <div class="right-area">
          <div class="order-box">
            <h4 class="title">{{ $langg->lang127 }}</h4>
            <ul class="order-list">
              <li>
                <p>
                  {{ $langg->lang128 }}
                </p>
                <P>
                  <b class="cart-total">{{ Session::has('cart') ? App\Models\Product::convertPrice($totalPrice) : '0.00' }}</b>
                </P>
              </li>
              
              
              
              
{{--              <li>  --}}
{{--                <p>  --}}
{{--                  {{ $langg->lang129 }}  --}}
{{--                </p>  --}}
{{--                <P>  --}}
{{--                  <b class="discount">{{ App\Models\Product::convertPrice(0)}}</b>  --}}
{{--                  <input type="hidden" id="d-val" value="{{ App\Models\Product::convertPrice(0)}}">  --}}
{{--                </P>  --}}
{{--              </li>  --}}
              
              
              
              
              <li>
                <p>
                  {{ $langg->lang130 }}
                </p>
                <P>
                  <b>{{$tx}}%</b>
                </P>
              </li>
            </ul>
            <div class="total-price">
              <p>
                  {{ $langg->lang131 }}
              </p>
              <p>
                  <span class="main-total">{{ Session::has('cart') ? App\Models\Product::convertPrice($mainTotal) : '0.00' }}</span>
              </p>
            </div>
            <div class="cupon-box">
              <div id="coupon-link">
                  {{ $langg->lang132 }}
              </div>
              <form id="coupon-form" class="coupon">
                <input type="text" placeholder="{{ $langg->lang133 }}" id="code" required="" autocomplete="off">
                <input type="hidden" class="coupon-total" id="grandtotal" value="{{ Session::has('cart') ? App\Models\Product::convertPrice($mainTotal) : '0.00' }}">
                <button type="submit">{{ $langg->lang134 }}</button>
              </form>
            </div>
            <a href="{{ route('front.checkout') }}" class="order-btn">
              {{ $langg->lang135 }}
            </a>
          </div>
        </div>
      </div>
      @endif
   
   
     </div>
  </div>
  
  
  
  
 <!-- 
  <div class="container">
 
    <div class="row"> 
      
<div class="form-check">
  <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" onclick="toggle()">
  <label class="form-check-label cart-out-of-stock" for="flexCheckDefault">
    باقة ورود
  </label>
</div>
</div>
</div> 

 -->     
 	


 
	
	
  
</section>
<!-- Cart Area End -->



  @if($ps->featured == 1)  
		<!-- Trending Item Area Start -->
		<section  class="trending">
			<div class="container">
				<div class="row">
					<div class="col-lg-12 remove-padding">
						<div class="section-top">
							<h2 class="section-title">
								{{ $langg->lang98765 }}
							</h2>
							{{-- <a href="#" class="link">View All</a> --}}
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12 remove-padding">
						<div class="trending-item-slider">
							@foreach($feature_products as $prod)  
{{--	@include('includes.product.slider-product')   --}}



	@php
		$attrPrice = 0;
		$sessionCur = session()->get('currency');
		$sessionCurr = DB::table('currencies')->where('id',$sessionCur)->first();
		$databaseCurr = DB::table('currencies')->where('is_default',1)->first();
		$curr = $sessionCurr ? $sessionCurr: $databaseCurr;

		if($prod->user_id != 0){
        $attrPrice = $prod->price + $gs->fixed_commission + ($prod->price/100) * $gs->percentage_commission ;
        }

    if(!empty($prod->size) && !empty($prod->size_price)){
          $attrPrice += $prod->size_price[0];
      }

      if(!empty($prod->attributes)){
        $attrArr = json_decode($prod->attributes, true);
      }
	@endphp
  
  @if (!empty($prod->attributes))
  @php
	$attrArr = json_decode($prod->attributes, true);
  @endphp
  @endif
  
  @if (!empty($attrArr))
	@foreach ($attrArr as $attrKey => $attrVal)
	  @if (array_key_exists("details_status",$attrVal) && $attrVal['details_status'] == 1)
		@foreach ($attrVal['values'] as $optionKey => $optionVal)
		  @if ($loop->first)
			@if (!empty($attrVal['prices'][$optionKey]))
			  @php
				  $attrPrice = $attrPrice + $attrVal['prices'][$optionKey] * $curr->value;
			  @endphp
			@endif
		  @endif
		@endforeach
	  @endif
	  @endforeach
  @endif
  
@php
  $withSelectedAtrributePrice = $attrPrice+$prod->price;
  $withSelectedAtrributePrice = round(($withSelectedAtrributePrice) * $curr->value,2);

//   if($gs->currency_format == 0){
//        $curr->sign.$withSelectedAtrributePrice;
//     }
//     else{
//          $withSelectedAtrributePrice.$curr->sign;
//     }
@endphp

	<a href="{{ route('front.product', $prod->slug) }}" class="item">
		<div class="item-img">
			@if(!empty($prod->features))
				<div class="sell-area">
				@foreach($prod->features as $key => $data1)
					<span class="sale" style="background-color:{{ $prod->colors[$key] }}">{{ $prod->features[$key] }}</span>
					@endforeach 
				</div>
			@endif
				<div class="extra-list">
					<ul>
						<li>
							@if(Auth::guard('web')->check())

							<span class="add-to-wish" data-href="{{ route('user-wishlist-add',$prod->id) }}" data-toggle="tooltip" data-placement="right" title="{{ $langg->lang54 }}" data-placement="right"><i class="icofont-heart-alt" ></i>
							</span>

							@else 

							<span rel-toggle="tooltip" title="{{ $langg->lang54 }}" data-toggle="modal" id="wish-btn" data-target="#comment-log-reg" data-placement="right">
								<i class="icofont-heart-alt"></i>
							</span>

							@endif
						</li>
						<li>
						<span class="quick-view" rel-toggle="tooltip" title="{{ $langg->lang55 }}" href="javascript:;" data-href="{{ route('product.quick',$prod->id) }}" data-toggle="modal" data-target="#quickview" data-placement="right"> <i class="icofont-eye"></i>
						</span>
						</li>
						<li>
							<span class="add-to-compare" data-href="{{ route('product.compare.add',$prod->id) }}"  data-toggle="tooltip" data-placement="right" title="{{ $langg->lang57 }}" data-placement="right">
								<i class="icofont-exchange"></i>
							</span>
						</li>
					</ul>
				</div>
			<img class="img-fluid" src="{{ $prod->thumbnail ? asset('assets/images/thumbnails/'.$prod->thumbnail):asset('assets/images/noimage.png') }}" alt="">
		</div>
		<div class="info">
			<div class="stars">
				<div class="ratings">
					<div class="empty-stars"></div>
					<div class="full-stars" style="width:{{App\Models\Rating::ratings($prod->id)}}%"></div>
				</div>
			</div>
			<h4 class="price">{{ $attrPrice != 0 ?  $gs->currency_format == 0 ? $curr->sign.$withSelectedAtrributePrice : $withSelectedAtrributePrice.$curr->sign :$prod->showPrice() }} <del><small>{{ $prod->showPreviousPrice() }}</small></del></h4>
					<h5 class="name">{{ $prod->showName() }}</h5>
					<div class="item-cart-area">
						@if($prod->product_type == "affiliate")
							<span class="add-to-cart-btn affilate-btn"
								data-href="{{ route('affiliate.product', $prod->slug) }}"><i class="icofont-cart"></i>
								{{ $langg->lang251 }}
							</span>
						@else
							@if($prod->stock === 0)
							<span class="add-to-cart-btn cart-out-of-stock">
								<i class="icofont-close-circled"></i> {{ $langg->lang78 }}
							</span>													
							@else
	
	
	
							<span class="add-to-cart-quick add-to-cart-btn"
								data-href="{{ route('product.cart.quickadd',$prod->id) }}">
								<i class="icofont-cart"></i> {{ $langg->lang56 }}
							</span>
							@endif
						@endif
					</div>
		</div>
	</a>




							@endforeach  
						</div>
					</div>

				</div>
			</div>
		</section>
		<!-- Tranding Item Area End -->
  @endif  
	    
	    
	    
@endsection 

@section('scripts')
	<script>
    	function toggle() {
          // Get the checkbox
          var checkBox = document.getElementById("flexCheckDefault");
          // Get the output div
          var flowers = document.getElementById("flowers");
        
          // If the checkbox is checked, display the output text
          if (checkBox.checked == true){
              //checkBox.checked = false;
              flowers.style.display = "block";
          } else {
              //checkBox.checked = true;
              flowers.style.display = "none";
          }
        }


	</script>
@endsection

