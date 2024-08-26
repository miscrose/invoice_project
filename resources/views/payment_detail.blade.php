@extends('layout')

@section('contenu')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Payment Details for Invoice # @if ($type==='received')
                    {{ $invoice->id }}
                @else
                {{ $invoice->invoice_number }}
                @endif 
                <h3>{{$invoice->status}}  ({{$invoice->paymentamount}}Dh/{{$invoice->ttc}}DH)</h3>
            </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payment_detail as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date}}</td>
                                    <td>
                                        <i class="fas {{ $type === 'received' ? 'fa-arrow-up' : 'fa-arrow-down' }} 
                                        {{ $type === 'received' ? 'text-success' : 'text-danger' }}" 
                                        aria-hidden="true"></i>
                                        {{ number_format($payment->paye, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">No payment details available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection