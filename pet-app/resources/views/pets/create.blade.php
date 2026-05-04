<!DOCTYPE html>
<html>
<head>
    <title>Add Pet</title>
</head>
<body>

<h1>Add New Pet</h1>

@if(session('success'))
    <p style="color:green">{{ session('success') }}</p>
@endif

@if($errors->any())
    <ul style="color:red">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('pets.store') }}">
    @csrf
    <input type="text" name="name" placeholder="Name" value="{{ old('name') }}"><br>
    <input type="text" name="type" placeholder="Type (cat/dog...)" value="{{ old('type') }}"><br>
    <input type="text" name="breed" placeholder="Breed" value="{{ old('breed') }}"><br>
    <input type="number" name="age" placeholder="Age" value="{{ old('age') }}"><br>
    <textarea name="description" placeholder="Description">{{ old('description') }}</textarea><br>
    <input type="text" name="image_path" placeholder="Image path" value="{{ old('image_path') }}"><br>
    <button type="submit">Add Pet</button>
</form>

</body>
</html>