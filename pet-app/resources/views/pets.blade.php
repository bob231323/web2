<!DOCTYPE html>
<html>
<head>
    <title>Pet Facts</title>

    <style>
        body {
            background: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .card {
            width: 350px;
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        img {
            width: 100%;
            border-radius: 10px;
        }
    </style>
</head>

<body>

<div class="card">
    <div id="fact">Loading...</div>
    <img id="image" src="">
    <div id="pet"></div>
</div>
<!--you only need this line from this file-->
<script src="{{ asset('js/API.js') }}"></script>

</body>
</html>
