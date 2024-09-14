

<div class="table-responsive scrollbar">
  <table class="table table-hover table-striped overflow-hidden">
    <thead>
      <tr>
        <th scope="col">Devis Number</th>
        <th scope="col">Date</th>
        <th scope="col">Type</th>
        <th scope="col">confirmation</th>
        <th class="text-end" scope="col">Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($devis as $item)
        <tr class="align-middle">
          <td class="text-nowrap">
            @if ($item->type === 'sent')
               {{ $item->devis_number }}
            @else
                    {{ $item->id }}
            @endif
          </td>
          <td class="text-nowrap">{{ $item->date }}</td>
          <td class="text-nowrap">
            @if ($item->type === 'sent')
              @if (Auth::user()->usertype==='admin')
                  received
              @else
                 sent 
              @endif
            @else
                    @if (Auth::user()->usertype==='admin')
                    sent 
                @else
                   received
                @endif
            @endif
            <td class="text-nowrap">

              @if (Auth::user()->usertype==='admin')
                        @if ($item->is_confirmed==='true')
                            confirmed
                         @elseif($item->is_confirmed==='false')
                         <div class="d-flex">
                          <form action="{{ route('validation_quote_admin') }}" method="POST" class="me-2">
                              @csrf
                              <input type="hidden" name="quote_id" value="{{ $item->id }}">
                              <input type="hidden" name="type" value="{{$item->type}}">
                              <button type="submit" class="btn btn-warning btn-sm">Confirm</button>
                          </form>
                          <form action="{{ route('refus_quote_admin') }}" method="POST">
                              @csrf
                              <input type="hidden" name="quote_id" value="{{ $item->id }}">
                              <input type="hidden" name="type" value="{{$item->type}}">
                              <button type="submit" class="btn btn-danger btn-sm">Refuse</button>
                          </form>
                      </div>
                      
                        @else

                          refuse

                        
                         @endif
              @else

                        
                              @if ($item->is_confirmed==='true')
                               confirmed
                              @elseif($item->is_confirmed==='false')
                              Unconfirmed 
                              @else
                              refuse
                              @endif
 




             
              @endif

          </td>




          


          <td class="text-end">
            <form action="{{ route('detail_devis', ['type' => $item->type, 'id' => $item->id]) }}" method="POST" style="display: inline;">
              @csrf
              <button type="submit" class="btn btn-primary btn-sm">View Details</button>
          </form>         </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
