<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Triangulation Calculator</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="m-0 overflow-hidden bg-black font-sans">
    <div class="absolute top-5 left-5 bg-white/85 p-5 rounded-lg shadow-lg max-w-xs z-10">
        <h1 class="text-center text-gray-800 font-bold text-lg mb-5">Triangulation Calculator</h1>
        <form id="triangulation-form">
            <label for="latitude" class="font-bold block mt-3 text-gray-700">Latitude of New Point:</label>
            <input type="number" id="latitude" name="latitude" required min="-90" max="90" step="any"
                class="w-full p-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <label for="longitude" class="font-bold block mt-3 text-gray-700">Longitude of New Point:</label>
            <input type="number" id="longitude" name="longitude" required min="-180" max="180" step="any"
                class="w-full p-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <button type="submit"
                class="mt-5 w-full p-3 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-300 text-lg">
                Calculate Distances
            </button>
        </form>

        <div id="result" class="mt-5 p-3 rounded-md border border-green-600 bg-green-100 text-green-800"
            style="display: none;">
            <h2 class="font-bold">Distances:</h2>
            <ul id="distances-list" class="list-none pl-0"></ul>
        </div>

        <div id="error" class="mt-5 p-3 rounded-md border border-red-600 bg-red-100 text-red-800"
            style="display: none;">
            <h2 class="font-bold">Error:</h2>
            <p id="error-message"></p>
        </div>
    </div>

</body>

</html>
