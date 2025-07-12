<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <title>CityVet</title>
  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen m-0 p-0">
  <main class="flex h-screen w-full">
    @include('layouts.sidebar')

    {{-- Main content area --}}
    <section class="flex-1 bg-[#eeeeee] px-[5rem] py-[3rem] overflow-y-auto -mb-1.5">
      @yield('content')
    </section>
  </main>
</body>
</html>
