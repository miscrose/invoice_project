@extends('layout')
@section('contenu')
    



<div class="container px-4 mx-auto">

    <div class="p-6 m-10 bg-white rounded shadow">
{!! $invoice_count->container() !!}
</div>

<div class="p-6 m-10 bg-white rounded shadow">
    {!! $ttc_chart->container() !!}
</div>


</div>

@endsection
@section('script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
{!! $invoice_count->script() !!}
{!! $ttc_chart->script() !!}
              
    @endsection
