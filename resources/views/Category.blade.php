 @extends('layout')
 @section('content')
 
 <!-- Header -->


<!-- Main Content -->
<div class="main-content  main-content-expanded" id="mainContent">
    <h2 class="page-title">List of Categories</h2>
    
    <div class="table-container">
        <div class="table-header">
            <div>
                <button class="btn btn-add text-white btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> Add a New Category
                </button>
                <button class="btn btn-export-pdf text-white btn-sm">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
                <button class="btn btn-export-excel text-white btn-sm">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
            <form action="{{ url('/Category') }}" method="GET" class="search-box">
                <div class="input-group mt-2">
                    <input
                        type="text"
                        name="search"
                        class="form-control form-control-sm"
                        placeholder="Search category..."
                        value="{{ request('search') }}"
                    >
                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>        </div>
        <!---message--->
        @if(session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($Categorys as $Category )
                    
                <tr>
                    <td>{{$Category->id}}</td>
                    <td> {{$Category->Category}}</td>
                    <td>
                        <button class="btn btn-primary btn-sm action-btn">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger btn-sm action-btn">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </td>
                </tr>
                @endforeach

               
            </tbody>
        </table>
        <div class="mt-3">
            {{ $Categorys->appends(['search' => request('search')])->links() }}
        </div>
        
        <div class="pagination-info">
            <div>
                Showing 1 to 4 of 4 entries
            </div>
            <div>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add Categories Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Add Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCategoryForm"  aaction="/Category" method="POST" enctype="multipart/form-data">
                @csrf
            <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="categoryName" name="category" placeholder="Enter category name" required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addCategoryForm" class="btn btn-success">Submit</button>
            </div>
        </form>

        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection