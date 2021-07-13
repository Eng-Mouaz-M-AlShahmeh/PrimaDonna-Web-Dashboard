@extends('layouts.front')

@section('styles')
<style>
    .wpwl-wrapper-cardNumber{
        direction: ltr !important;        
        text-align: right;
    }
</style>
@endsection

@section('content')

<!-------------------------------------->										
<!--------- hyper pay ------------------>

<section class="faq-section">
    <div class="container">
      <div class="row justify-content-center">

		<form action="{{route('front.hyperpayReq', ['id'=>$id, 'merchantTransactionId'=>$merchantTransactionId] )}}" class="paymentWidgets" data-brands="VISA MASTER"></form>
		
		
      </div>
    </div>
</section>
		
<!--------- end hyper pay ---------->										
<!-------------------------------------->


@endsection

@section('scripts')
<script>
    var wpwlOptions = {
        paymentTarget:"_top",
        locale: "ar",
        iframeStyles: {
            'card-number-placeholder': {
                'direction': 'rtl'
            }
        }
    }
</script>

<script src="https://oppwa.com/v1/paymentWidgets.js?checkoutId={{$id}}"></script>

@endsection