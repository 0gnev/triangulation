<!-- index.blade.php -->
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
            <label for="distanceA" class="font-bold block mt-3 text-gray-700">Distance from Point A (km):</label>
            <input type="number" id="distanceA" name="distanceA" required min="0" step="any"
                class="w-full p-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <label for="distanceB" class="font-bold block mt-3 text-gray-700">Distance from Point B (km):</label>
            <input type="number" id="distanceB" name="distanceB" required min="0" step="any"
                class="w-full p-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <label for="distanceC" class="font-bold block mt-3 text-gray-700">Distance from Point C (km):</label>
            <input type="number" id="distanceC" name="distanceC" required min="0" step="any"
                class="w-full p-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <button type="submit"
                class="mt-5 w-full p-3 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-300 text-lg">
                Calculate Position
            </button>
        </form>

        <div id="result" class="mt-5 p-3 rounded-md border border-green-600 bg-green-100 text-green-800"
            style="display: none;">
            <h2 class="font-bold">Calculated Coordinates:</h2>
            <ul id="coordinates-list" class="list-disc pl-5"></ul>
        </div>

        <div id="error" class="mt-5 p-3 rounded-md border border-red-600 bg-red-100 text-red-800"
            style="display: none;">
            <h2 class="font-bold">Error:</h2>
            <p id="error-message"></p>
        </div>
    </div>

</body>

</html>
