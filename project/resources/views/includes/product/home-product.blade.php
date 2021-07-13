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


@endphp

							<div class="col-lg-4 col-md-4 col-6 remove-padding">

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
												<h4 class="price">{{ $attrPrice != 0 ?  $gs->currency_format == 0 ? $curr->sign.$withSelectedAtrributePrice : $withSelectedAtrributePrice.$curr->sign :$prod->showPrice() }}</h4>
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
																<span class="add-to-cart add-to-cart-btn" data-href="{{ route('product.cart.add',$prod->id) }}">
																	<i class="icofont-cart"></i> {{ $langg->lang56 }}
																</span>
																<span class="add-to-cart-quick add-to-cart-btn"
																	data-href="{{ route('product.cart.quickadd',$prod->id) }}">
																	<i class="icofont-cart"></i> {{ $langg->lang251 }}
																</span>
																@endif
															@endif
														</div>
											</div>
										</a>
							</div>
