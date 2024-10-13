@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Building Management'])

{{-- Side Navigation for Buildings --}}
<div class="sidenav">
    <ul>
        {{-- Default Menu --}}
        <li>
            <a href="/home">Home</a>
        </li>
        <li>
            <a href="{{ route('building.index') }}">All Buildings</a>
        </li>
    </ul>
</div>

{{-- Main Content --}}
<div class="container-fluid py-4">
    <div class="row">
        {{-- List of Existing Buildings --}}
        @foreach ($buildings as $building)
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card shadow-lg">
                    <a href="{{ $building->path ?? '#' }}">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-3 text-center">
                                    @if($building->image)
                                        <img src="{{ asset('storage/'.$building->image) }}" alt="{{ $building->name }}" class="img-fluid rounded-circle" style="height: 50px; width: 50px;">
                                    @else
                                        <div class="icon icon-shape bg-gradient-primary text-center rounded-circle">
                                            <i class="fa-solid fa-building text-lg opacity-10" aria-hidden="true" style="color: white"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-9">
                                    <div class="numbers">
                                        <p class="text-sm mb-2 text-uppercase font-weight-bold">{{ $building->description ?? 'Description' }}</p>
                                        <h5 class="font-weight-bolder">{{ $building->name }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <div class="card-footer text-end">
                        <a href="{{ route('building.edit', $building->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('building.destroy', $building->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this building?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Add New Building Form --}}
    <div class="row mt-4">
        <div class="col-lg-5 mb-lg-0 mb-4">
            <div class="card z-index-2 h-100 shadow-lg">
                <div class="card-header pb-0 pt-3 bg-transparent">
                    <h6 class="text-capitalize">Add New Building</h6>
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('building.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="name">Building Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter building name" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="description">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Enter building description">
                        </div>
                        <div class="form-group mt-3">
                            <label for="image">Building Image</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <div class="form-group mt-3">
                            <label for="path">Path</label>
                            <div class="input-group">
                                <input type="text" name="path" class="form-control" id="path" placeholder="Enter path">
                                <button type="button" class="btn btn-outline-primary" id="generatePath">Auto</button>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Add Building</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    document.getElementById('generatePath').addEventListener('click', function() {
        let buildingName = document.querySelector('input[name="name"]').value; // Get building name from input `name`
        if (buildingName) {
            // Convert the building name to a URL-friendly format
            let formattedName = buildingName.trim().toLowerCase().replace(/[^a-z0-9\s]/g, '').replace(/\s+/g, '-');
            // Create an automatic path with the desired format
            document.getElementById('path').value = `/dashboard-${formattedName}`;
        } else {
            alert('Please enter the building name first.');
        }
    });
</script>
@endpush

@endsection