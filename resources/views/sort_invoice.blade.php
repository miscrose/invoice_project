<div class="table-responsive scrollbar">
    <table class="table table-hover table-striped overflow-hidden">
        <thead>
            <tr>
                <th scope="col">Invoice Number</th>
                <th scope="col">Date</th>
                <th scope="col">Status</th>
                <th class="text-end" scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
                <tr class="align-middle">
                    <td class="text-nowrap">
                        @if ($invoice->type === 'sent')
                            {{ $invoice->invoice_number }}
                        @else
                            {{ $invoice->id }}
                        @endif
                    </td>
                    <td class="text-nowrap">{{ $invoice->date }}</td>
 
                        @if (Auth::user()->usertype==='admin')
                        <td class="text-nowrap">
                            <form action="{{route('payment_form')}}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $invoice->id }}">
                                <input type="hidden" name="type" value="{{ $invoice->type }}">
                               <span> {{$invoice->status}} ({{$invoice->paymentamount}}DH/{{$invoice->ttc}}DH)</span>
                               <button type="submit" class="btn btn-link p-0 m-0">
                                <small>details</small>
                            </button>
                            </form>
    
    
                        </td>
                        @else
                        <td>
                            <form action="{{route('payment_detail')}}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $invoice->id }}">
                                <input type="hidden" name="type" value="{{ $invoice->type }}">
                               <span> {{$invoice->status}} ({{$invoice->paymentamount}}DH/{{$invoice->ttc}}DH)</span>
                               <button type="submit" class="btn btn-link p-0 m-0">
                                <small>details</small>

                        </td>


                  
                        @endif
                    <td class="text-end">
                        <form action="{{ route('detail_invoice', ['type' => $invoice->type, 'id' => $invoice->id]) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">View Details</button>
                        </form>
                           </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
