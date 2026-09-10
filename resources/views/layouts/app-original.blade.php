<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Digital Invoicing')
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f6f8;
            color: #252525;
        }

        .header {
            background: #17202a;
            color: white;
            padding: 16px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header a {
            color: white;
            text-decoration: none;
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            background: #17202a;
            color: white;
            border: 0;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: #1769aa;
        }

        .btn-gray {
            background: #d6d6d6;
        }



        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #d6d6d6;
            border-radius: 5px;
        }

        label {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .success {
            background: #dff7e6;
            padding: 12px;
            margin-bottom: 15px;
        }

        .error {
            background: #fde2e2;
            padding: 12px;
            margin-bottom: 15px;
        }

    </style>

</head>

<body>

@if(auth()->check())

    <div class="header">

        <div>
            <strong>Digital Invoicing</strong>

            @isset($currentBusiness)
                &nbsp; — &nbsp;
                {{ $currentBusiness->name }}
            @endisset
        </div>

        <form method="POST"
              action="{{ route('logout') }}">

            @csrf

            <button class="btn">
                Logout
            </button>

        </form>

    </div>

@endif


<div class="container">

    @if(session('success'))

        <div class="success">
            {{ session('success') }}
        </div>

    @endif


    @if($errors->any())

        <div class="error">

            @foreach($errors->all() as $error)

                <div>{{ $error }}</div>

            @endforeach

        </div>

    @endif


    @yield('content')

</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

@stack('scripts')

</body>

</html>
