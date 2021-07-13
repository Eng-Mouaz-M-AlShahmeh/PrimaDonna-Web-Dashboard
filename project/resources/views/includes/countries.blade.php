<option value="">{{ $langg->lang157 }}</option>
@if(Auth::check())
	@foreach (DB::table('countries')->get() as $data)
	<option style="background-image:url({{asset('assets/front/images/flags/'.$data->country_flag)}});" value="{{ $data->country_name }}" {{ Auth::user()->country == $data->country_name ? 'selected' : '' }}>{{ $data->country_name }}
         <i class="flag flag-uae"></i>       					
	</option>		
	@endforeach
@else
	@foreach (DB::table('countries')->get() as $data)
	<option style="background-image:url({{asset('assets/front/images/flags/'.$data->country_flag)}});" value="{{ $data->country_name }}">
	    {{ $data->country_name }} 
        <i class="flag flag-uae"></i> 					
	</option>		
	@endforeach
@endif