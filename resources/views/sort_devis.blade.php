

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
                         @else
                            <form action="{{ route('validation_quote_admin') }}" method="POST">
                                @csrf
                                <input type="hidden" name="quote_id" value="{{ $item->id }}">
                                <input type="hidden" name="type" value="{{$item->type}}  ">
                                <button type="submit" class="btn btn-warning btn-sm">Confirm</button>
                            </form>
                        
                         @endif
              @else

                          @if ($item->type==='sent')
                              @if ($item->is_confirmed==='true')
                               confirmed
                              @else
                              Unconfirmed 

                              @endif

                          @else
                                  @if ($item->is_confirmed==='true')
                                  confirmed
                                @else
                                    <form action="{{ route('validation_quote') }}" method="POST">
                                      @csrf
                                      <input type="hidden" name="quote_id" value="{{ $item->id }}">
                
                                      <button type="submit" class="btn btn-warning btn-sm">Confirm</button>
                                  </form>

                                @endif
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
