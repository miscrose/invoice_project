@extends('layout')

@section('contenu')
@if ($rest!=0)
    

    <!-- Bouton pour afficher le formulaire de paiement -->
    <button id="showPaymentFormBtn" class="btn btn-secondary mt-3">Add Payment Info</button>
<!-- Conteneur du formulaire de paiement -->
<div class="container" style="display: none;" id="paymentFormContainer">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Payment Information</div>
                <div class="card-body">
                    <form action="{{ route('payment_form_save') }}" method="POST" id="paymentForm">
                        @csrf
                        <input type="hidden" name="id" value="{{ $id }}">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="hidden" id="restValue" value="{{ $rest }}">
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">Payment Date</label>
                            <input type="date" class="form-control" id="payment_date" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="payment_amount" class="form-label">Payment Amount</label>
                            <input type="number" step="0.01" class="form-control" id="payment_amount" name="payment" required>
                            <div id="payment_amount_feedback" class="invalid-feedback">
                                Payment amount cannot exceed {{ $rest }}.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Save Payment Info</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endif
   
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
                                            <i class="fas {{ $type === 'sent' ? 'fa-arrow-up' : 'fa-arrow-down' }} 
                                            {{ $type === 'sent' ? 'text-success' : 'text-danger' }}" 
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
<script>
    document.getElementById('showPaymentFormBtn').addEventListener('click', function() {
        var paymentFormContainer = document.getElementById('paymentFormContainer');
        if (paymentFormContainer.style.display === "none") {
            paymentFormContainer.style.display = "block";
            this.textContent = "Hide Payment Info Form";
        } else {
            paymentFormContainer.style.display = "none";
            this.textContent = "Add Payment Info";
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentAmountInput = document.getElementById('payment_amount');
        const restValue = parseFloat(document.getElementById('restValue').value);

        paymentAmountInput.addEventListener('input', function() {
            const value = parseFloat(paymentAmountInput.value);
            const feedback = document.getElementById('payment_amount_feedback');

            if (value > restValue) {
                paymentAmountInput.classList.add('is-invalid');
                feedback.style.display = 'block';
            } else {
                paymentAmountInput.classList.remove('is-invalid');
                feedback.style.display = 'none';
            }
        });
    });
</script>

 
@endsection
