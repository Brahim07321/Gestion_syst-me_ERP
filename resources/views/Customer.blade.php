@extends('layout')
@section('content')
    <div class="col-md-10 main-content  main-content-expanded" id="mainContent">
        <h2>List of Customers</h2>
        <div class="top-bar d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                    <i class="fas fa-plus"></i> Add Customer
                </button>
                <button class="btn btn-danger"><i class="fas fa-file-pdf me-2"></i>Export PDF</button>
                <button class="btn btn-primary"><i class="fas fa-file-excel me-2"></i>Export Excel</button>

                
            </div>
            <!-- Add Customer Modal -->
            <style>
                :root {
                    --sidebar-width: 250px;
                    --primary-color: #2ecc71;
                    --secondary-color: #3498db;
                }

                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background-color: #f8f9fa;
                }

                .sidebar {
                    width: var(--sidebar-width);
                    height: 100vh;
                    background-color: #2c3e50;
                    position: fixed;
                    color: white;
                }

                .main-content {
                    margin-left: var(--sidebar-width);
                    padding: 20px;
                }

                .table-header {
                    display: auto;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 20px;
                }

                .customer-actions .btn {
                    padding: 0.25rem 0.5rem;
                    font-size: 0.875rem;
                }
            </style>
            <div class="modal fade" id="addCustomerModal" tabindex="1" aria-labelledby="addCustomerModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="addCustomerModalLabel">
                                <i class="fas fa-user-plus me-2"></i>Add New Customer
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <form action="/Customer" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="customerName" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="name" id="customerName"  required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="customerAddress" class="form-label">Address</label>
                                        <input type="text" class="form-control" id="customerAddress" name="address"  required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="customerContact" class="form-label">Contact Number</label>
                                        <input type="tel" class="form-control" id="customerContact" name="contact"  pattern="[0-9]{10}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>                        </form>
                    </div>

                </div>
            </div>


            <!-- Main Content -->

            <form class="d-flex me-4">


                <div class="search-box">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" placeholder="Search...">
                        <button class="btn btn-outline-secondary btn-sm" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- message -->
        @if (session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <!-- Customer Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <td>{{ $customer->id }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->address }}</td>
                            <td>{{ $customer->contact }}</td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#editCustomerModal">
                                    <i class="fas fa-edit me-2"></i>
                                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-info">
                                        Edit
                                    </a>
                                    
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>


            </table>
        </div>


        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-end">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>

        <!-- Import Customer Data Section -->
        <div class="mt-5">
            <h4>Import Customer Data</h4>
            <form>
                <div class="mb-3">
                    <label for="importFile" class="form-label">Choose File:</label>
                    <input type="file" class="form-control" id="importFile">
                </div>
                <button type="submit" class="btn btn-secondary">Import</button>
            </form>
        </div>
    </div>
@endsection
