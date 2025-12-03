<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hackathon Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#DCBAA] min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Masthead -->
        <div class="mb-6 flex justify-center">
            <img src="{{ asset('logo/masthead.png') }}" alt="Masthead" class="max-w-full h-auto"
                style="max-height: 120px;">
        </div>
        @if(session('success'))
            <div class="bg-[#E6FFA0] border border-green-600 text-green-900 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-500 text-red-800 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</body>

</html>