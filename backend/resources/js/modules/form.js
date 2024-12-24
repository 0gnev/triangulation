import { latLngToVector3 } from './threejs/helpers';
import { drawPath } from './threejs/paths';
import { createMarker } from './threejs/markers';

/**
 * Retrieves the CSRF token from the meta tag.
 * @returns {string} - The CSRF token.
 */
export function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

let currentNewMarker = null;
let currentPaths = [];

/**
 * Handles form submission and interaction logic.
 * @param {THREE.Scene} scene - The Three.js scene.
 * @param {Array<THREE.Mesh>} referenceMarkers - Array of reference point markers.
 */
export function handleForm(scene, referenceMarkers) {
    const form = document.getElementById('triangulation-form');

    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        hideResultsAndErrors();

        const latitude = parseFloat(document.getElementById('latitude').value);
        const longitude = parseFloat(document.getElementById('longitude').value);

        if (!validateCoordinates(latitude, longitude)) return;

        try {
            const response = await fetch('/api/triangulate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ latitude, longitude }),
            });

            const data = await response.json();

            if (data.success) {
                displayResults(data.distances);
                visualizeTriangulation(scene, latitude, longitude, referenceMarkers);
            } else {
                displayError(data.message || 'An error occurred while calculating distances.');
            }
        } catch (error) {
            console.error('Error:', error);
            displayError('An unexpected error occurred.');
        }
    });
}

/**
 * Validates latitude and longitude values.
 * @param {number} latitude - The latitude value to validate.
 * @param {number} longitude - The longitude value to validate.
 * @returns {boolean} - Returns true if both values are valid, otherwise false.
 */
function validateCoordinates(latitude, longitude) {
    if (isNaN(latitude) || latitude < -90 || latitude > 90) {
        displayError('Please enter a valid latitude value between -90 and 90.');
        return false;
    }
    if (isNaN(longitude) || longitude < -180 || longitude > 180) {
        displayError('Please enter a valid longitude value between -180 and 180.');
        return false;
    }
    return true;
}

/**
 * Displays the calculated distances in the UI.
 * @param {Array<Object>} distances - Array of distance objects from the backend.
 */
function displayResults(distances) {
    const resultDiv = document.getElementById('result');
    const distancesList = document.getElementById('distances-list');

    distancesList.innerHTML = ''; // Clear previous entries

    distances.forEach((item) => {
        const li = document.createElement('li');
        li.textContent = `Distance to Point ${item.reference_point}: ${item.distance_km} km`;
        distancesList.appendChild(li);
    });

    resultDiv.style.display = 'block';
}

/**
 * Displays an error message in the UI.
 * @param {string} message - The error message to display.
 */
function displayError(message) {
    const errorDiv = document.getElementById('error');
    const errorMessageP = document.getElementById('error-message');

    errorMessageP.textContent = message;
    errorDiv.style.display = 'block';
}

/**
 * Hides the results and error sections in the UI.
 */
function hideResultsAndErrors() {
    const resultDiv = document.getElementById('result');
    const errorDiv = document.getElementById('error');
    const distancesList = document.getElementById('distances-list');

    resultDiv.style.display = 'none';
    errorDiv.style.display = 'none';
    distancesList.innerHTML = '';
}

/**
 * Visualizes the triangulation by adding a new marker and drawing paths to reference points.
 * @param {THREE.Scene} scene - The Three.js scene.
 * @param {number} lat - Latitude of the new point.
 * @param {number} lng - Longitude of the new point.
 * @param {Array<THREE.Mesh>} referenceMarkers - Array of reference point markers.
 */
function visualizeTriangulation(scene, lat, lng, referenceMarkers) {
    if (currentNewMarker) {
        scene.remove(currentNewMarker);
        currentNewMarker = null;
    }

    currentPaths.forEach(path => {
        scene.remove(path);
    });
    currentPaths = [];

    const newPos = latLngToVector3(lat, lng, 1.0);

    currentNewMarker = createMarker(newPos, 0x00ff00, 0.012); // Green marker
    scene.add(currentNewMarker);

    referenceMarkers.forEach((marker) => {
        const refPos = marker.position.clone().normalize();
        const newPosOnSphere = newPos.clone().normalize();

        const path = drawPath(scene, newPosOnSphere, refPos, 1.01, 0xffee00); // Bright yellow arcs
        currentPaths.push(path);
    });
}
