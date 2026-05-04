<!DOCTYPE html>
<html>
<head>
    <title>Pets List</title>
</head>
<body>

<h1>All Pets</h1>

@if(session('success'))
    <p style="color:green">{{ session('success') }}</p>
@endif

<a href="{{ route('pets.create') }}">Add New Pet</a>

<table border="1">
    <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Breed</th>
        <th>Age</th>
        <th>Description</th>
    </tr>
    @foreach($pets as $pet)
    <tr>
        <td>{{ $pet->name }}</td>
        <td>{{ $pet->type }}</td>
        <td>{{ $pet->breed }}</td>
        <td>{{ $pet->age }}</td>
        <td>{{ $pet->description }}</td>
    </tr>
    @endforeach
</table>

</body>
</html>