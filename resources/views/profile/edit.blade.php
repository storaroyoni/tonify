@extends('layouts.app')

@section('content')
<div class="container">
    <div class="edit-profile-form">
        <h1>Edit Profile</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4">{{ old('bio', $user->bio) }}</textarea>
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                @if($user->profile_picture)
                    <div class="current-picture">
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Current Profile Picture">
                        <p>Current Profile Picture</p>
                    </div>
                @endif
            </div>

            <button type="submit" class="save-button">Save Changes</button>
        </form>
    </div>
</div>

<style>
.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.edit-profile-form {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

h1 {
    margin-bottom: 30px;
    color: #333;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500;
}

input[type="text"],
textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 16px;
}

textarea {
    resize: vertical;
    min-height: 100px;
}

.current-picture {
    margin-top: 10px;
}

.current-picture img {
    max-width: 150px;
    border-radius: 50%;
    margin-bottom: 10px;
}

.save-button {
    background: #d51007;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.save-button:hover {
    background: #b30d06;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 6px;
}

.alert-danger {
    background: #fee;
    color: #c33;
    border: 1px solid #fcc;
}
</style>
@endsection
