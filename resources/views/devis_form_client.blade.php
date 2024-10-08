@extends('layout')

@section('contenu')

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Create quote</div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    <form action="{{route('save_devis_client')}}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="date" class="form-label"> number</label>
                            <input type="text" class="form-control" id="devis_number" name="devis_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                       
                        <div class="mb-3">
                            <label for="company_id" class="form-label">Company</label>
                            <select class="form-control" id="company_id" name="company_id" required>
                                <option value=""></option> 
                                @foreach($companyinfo as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}/ {{$item->address }}</option>
                                @endforeach
                            </select>
                        </div>
                       
                        <div class="card mt-3">
                            <div class="card-header">Add  Item</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <input type="text" class="form-control" id="description" name="description" >
                                </div>
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" >
                                </div>
                                <div class="mb-3">
                                    <label for="unit_price" class="form-label">Unit Price</label>
                                    <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" >
                                </div>
                                <div class="mb-3">
                                    <label for="tva" class="form-label">TVA</label>
                                    <input type="number" step="0.01" class="form-control" id="tva" name="tva" value="20">
                                </div>
                                <button type="button" class="btn btn-primary" id="addItem">Add Item</button>
                            </div>
                        </div>

                        <table class="table mt-3" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>TVA %</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Items will be added here -->
                            </tbody>
                        </table>
                   
                       

                        <button type="submit" class="btn btn-primary mt-3">Save </button>
                      
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    

<script>
    $(document).ready(function() {
        
        $('#company_id').select2({
            placeholder: "company",
            allowClear: true
        });

        $('#addItem').on('click', function() {
            var description = $('#description').val();
            var quantity = $('#quantity').val();
            var unit_price = $('#unit_price').val();
            var tva = $('#tva').val();

            if (description && quantity && unit_price && tva) {
                var newRow = `
                    <tr>
                        <td><input type="hidden" name="descriptions[]" value="${description}">${description}</td>
                        <td><input type="hidden" name="quantities[]" value="${quantity}">${quantity}</td>
                        <td><input type="hidden" name="unit_prices[]" value="${unit_price}">${unit_price}</td>
                        <td><input type="hidden" name="tvas[]" value="${tva}">${tva}</td>
                        <td><button type="button" class="btn btn-danger remove-item">Remove</button></td>
                    </tr>
                `;
                $('#itemsTable tbody').append(newRow);
                
                
                $('#description').val('');
                $('#quantity').val('');
                $('#unit_price').val('');
            
            }
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
        });
    });
</script>

@endsection
