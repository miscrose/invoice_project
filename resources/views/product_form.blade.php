@extends('layout')

@section('contenu')

    <!-- Bouton pour afficher le formulaire de produit -->
    <button id="showProductFormBtn" class="btn btn-secondary mt-3">Add Product Info</button>

    <!-- Conteneur du formulaire de produit -->
    <div class="container" style="display: none;" id="productFormContainer">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Product Information</div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        <form action="{{ route('product_info_save') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" class="form-control" id="description" name="description" required>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Save Product Info</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Product Information</div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                    <tr data-id="{{ $product->id }}">
                                        <td>{{ $product->id }}</td>
                                        <td>{{ $product->description }}</td>
                                        <td>{{ $product->price }}</td>
                                        <td>
                                            <button class="btn btn-primary btn-sm edit-btn" data-id="{{ $product->id }}" data-description="{{ $product->description }}" data-price="{{ $product->price }}">Edit</button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $product->id }}">Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Edit Product Modal -->
<div class="modal " id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
             
            </div>
            <div class="modal-body">
                <form id="editProductForm">
                    @csrf
                    <input type="hidden" id="editProductId">
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <input type="text" class="form-control" id="editDescription" name="description" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPrice" class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control" id="editPrice" name="price" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

    @endsection
@section('script')
    

<script>
    document.getElementById('showProductFormBtn').addEventListener('click', function() {
        var productFormContainer = document.getElementById('productFormContainer');
        if (productFormContainer.style.display === "none") {
            productFormContainer.style.display = "block";
            this.textContent = "Hide Product Info Form";
        } else {
            productFormContainer.style.display = "none";
            this.textContent = "Add Product Info";
        }
    });

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const description = this.getAttribute('data-description');
            const price = this.getAttribute('data-price');

            document.getElementById('editProductId').value = id;
            document.getElementById('editDescription').value = description;
            document.getElementById('editPrice').value = price;

            $('#editProductModal').modal('show');
        });
    });


    document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
        const row = this.closest('tr'); // Sélectionne la ligne <tr> la plus proche du bouton
        const id = row.getAttribute('data-id'); // Récupère l'ID depuis l'attribut data-id du <tr>
        
       
            $.ajax({
                url: `/product_delete/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    
                },
                success: function(response) {
                    if (response.success) {
                        row.remove(); // Supprime la ligne <tr> du tableau
                    } else {
                        alert(response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('La requête a échoué.', error);
                }
            });
      
    });
});

document.getElementById('editProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('editProductId').value;
    const description = document.getElementById('editDescription').value;
    const price = document.getElementById('editPrice').value;
 
    $.ajax({
                url: `/product_update/${id}`,
                type: 'POST',
                data: {
                    id:id,
                   
                    description: description,
                    price: price,
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    if (response.success) {
                        const row = $(`tr[data-id="${id}"]`);
                 row.find('td:eq(1)').text(description);
                 row.find('td:eq(2)').text(price); 

              
                row.find('.edit-btn').attr('data-description', description);
                row.find('.edit-btn').attr('data-price', price);
                   $('#editProductModal').modal('hide');




                    } else {
                        alert(response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('La requête a échoué.', error);
                }
            });
});
document.querySelector('body').addEventListener('hidden.bs.modal', (event) => {
    
    document.querySelector('body').removeAttribute('style');
 });

 
</script>

@endsection
