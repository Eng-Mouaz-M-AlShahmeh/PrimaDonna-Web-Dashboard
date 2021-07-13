@extends('layouts.front')
@section('content')

<!-------------------------------------->										
<!--------- hyper pay ------------------>

<div>
    {{ $price }}
</div>

<section class="faq-section">
    <div class="container">
      <div class="row justify-content-center">

		<form action="{{route('front.hyperpayReq', $id )}}" class="paymentWidgets" data-brands="VISA MASTER MADA GOOGLEPAY"></form>
		
		
      </div>
    </div>
</section>
		
<!--------- end hyper pay ---------->										
<!-------------------------------------->



<section class="faq-section">
    <div class="container">
      <div class="row justify-content-center">
         
         
         
 <apple-pay-button buttonstyle="black" type="plain" locale="ar-AB">
     
     <form action="{{route('front.hyperpayReq', $id )}}" class="paymentWidgets" data-brands="APPLEPAY">
         
          
          
          
     </form>
     
         
 </apple-pay-button> 

        
		
      </div>
    </div>
</section>


@endsection

@section('scripts')
<script>
    var wpwlOptions = {
        locale: "ar"
    }
</script>


<script src="https://test.oppwa.com/v1/paymentWidgets.js?checkoutId={{$id}}"></script>


<script src="https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js"></script>  

@endsection