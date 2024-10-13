@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Edit Building'])

{{-- Tambahkan wrapper dengan margin-top agar tidak tertutup --}}
<div class="container-fluid py-4">
    <div class="card mt-4 shadow-lg">
        <div class="card-header pb-0">
            <h5>Edit Building Details</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('building.update', $building->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="name" class="form-label">Building Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $building->name) }}">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description">{{ old('description', $building->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Building Image</label>
                    <input type="file" class="form-control" id="image" name="image">
                    @if ($building->image)
                        <img src="{{ asset('storage/' . $building->image) }}" alt="Building Image" class="img-thumbnail mt-2" style="max-width: 150px;">
                    @endif
                </div>

                <div class="mb-3">
                    <label for="path" class="form-label">Path (optional)</label>
                    <input type="text" class="form-control" id="path" name="path" value="{{ old('path', $building->path) }}">
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">Save</button>
                    <a href="{{ route('building.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
