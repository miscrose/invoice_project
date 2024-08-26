@extends('layout')

@section('contenu')

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Invoice Information</div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    <form action="{{ route('save_invoice_admin') }}" method="POST">
                        @csrf
                 
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                      
                        <div class="mb-3">
                            <label for="client_id" class="form-label">Client</label>
                            <select class="form-control" id="client_id" name="client_id" required>
                                <option value=""></option> 
                                @foreach($client as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addClientModal">Add Client</button>
                        </div>

                        <div class="mb-3">
                            <label for="company_id" class="form-label">Company Information</label>
                            <select class="form-control" id="company_id" name="company_id" required>
                              
                                @foreach($companyinfo as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} / {{ $item->address }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                     
                        <div class="card mt-3">
                            <div class="row align-items-center card-header ">
                
                                <div class="col-md-6">
                                  Add Invoice Item
                                </div>
                        
                                <div class="col-md-6">
                               
                                    <select class="form-select" id="product_select">
                                        <option value="">Select a product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-description="{{ $product->description }}" data-price="{{ $product->price }}">
                                                {{ $product->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>



                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                            <input type="text" class="form-control" id="description" name="description">
                                </div>
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" value="1">
                                </div>
                                <div class="mb-3">
                                    <label for="unit_price" class="form-label">Unit Price</label>
                                    <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price">
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
                               
                            </tbody>
                        </table>
                     
                        <button type="submit" class="btn btn-primary mt-3">Save Invoice</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding Client -->
<div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClientModalLabel">Add Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addClientForm">
                    @csrf
                    <div class="mb-3">
                        <label for="client_name" class="form-label">Client Name</label>
                        <input type="text" class="form-control" id="client_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="client_address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="client_address" name="address" required>
                    </div>
                    <div class="mb-3">
                        <label for="client_tel" class="form-label">Telephone</label>
                        <input type="text" class="form-control" id="client_tel" name="tel" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Client</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    

<script>
    $(document).ready(function() {

        $('#product_select').on('change', function() {
        var selectedProduct = $(this).find('option:selected');
        var description = selectedProduct.data('description');
        var price = selectedProduct.data('price');

        // Remplir les champs de saisie avec les valeurs sélectionnées
        $('#description').val(description);
        $('#unit_price').val(price);
    });




        $('#client_id').select2({
            placeholder: "Select a client",
            allowClear: true
        });

        $('#company_id').select2({
            placeholder: "Select a company",
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
                        <td><input type="hidden" name="descriptions[]" value="${description}" >${description}</td>
                        <td><input type="hidden" name="quantities[]" value="${quantity}">${quantity}</td>
                        <td><input type="hidden" name="unit_prices[]" value="${unit_price}">${unit_price}</td>
                        <td><input type="hidden" name="tvas[]" value="${tva}">${tva}</td>
                        <td><button type="button" class="btn btn-danger remove-item">Remove</button></td>
                    </tr>
                `;
                $('#itemsTable tbody').append(newRow);
                
                // Clear the form fields
                $('#description').val('');

                $('#unit_price').val('');
              
                $('#product_select').val('').change();
            }
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
        });

        $('#addClientForm').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission
            var formData = {
        name: $('#client_name').val(),
        address: $('#client_address').val(),
        tel: $('#client_tel').val(),
        _token: '{{ csrf_token() }}'
    };
            $.ajax({
                url: "{{ route('add_client_ajax') }}", // Adjust the route as necessary
                method: 'POST',
                data: formData,
                success: function(response) {    // Add the new client to the select
                    $('#addClientModal').modal('hide');
                    $('.modal-backdrop').remove(); 
        
    
            $('#client_id').append(new Option(response.name + ' / ' + response.address, response.id));
                        
       
            $('#addClientModal').modal('hide');
            
            // Optionally, select the new client in the select
            $('#client_id').val(response.id).trigger('change');

                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        });
        document.querySelector('body').addEventListener('hidden.bs.modal', (event) => {
    // remove the overflow: hidden and padding-right: 15px
    document.querySelector('body').removeAttribute('style');
 });
    });
    
   

</script>
@endsection
