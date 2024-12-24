import { latLngToVector3 } from './threejs/helpers';
import { drawCircle } from './threejs/circles';
import { createMarker } from './threejs/markers';

/**
 * Retrieves the CSRF token from the meta tag.
 * @returns {string} - The CSRF token.
 */
export function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

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
        clearVisualization(scene);

        const distanceA = parseFloat(document.getElementById('distanceA').value);
        const distanceB = parseFloat(document.getElementById('distanceB').value);
        const distanceC = parseFloat(document.getElementById('distanceC').value);

        if (!validateDistances(distanceA, distanceB, distanceC)) return;

        try {
            const response = await fetch('/api/triangulate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ distanceA, distanceB, distanceC }),
            });

            const data = await response.json();

            if (data.success) {
                displayResults(data.coordinates);
                visualizeTriangulation(scene, data.coordinates, referenceMarkers, [distanceA, distanceB, distanceC]);
            } else {
                displayError(data.message || 'An error occurred while calculating the position.');
            }
        } catch (error) {
            console.error('Error:', error);
            displayError('An unexpected error occurred.');
        }
    });
}

/**
 * Validates the distances entered by the user.
 * @param {number} distanceA - Distance from Point A
 * @param {number} distanceB - Distance from Point B
 * @param {number} distanceC - Distance from Point C
 * @returns {boolean} - Returns true if all distances are valid, otherwise false.
 */
function validateDistances(distanceA, distanceB, distanceC) {
    if (isNaN(distanceA) || distanceA < 0) {
        displayError('Please enter a valid non-negative distance for Point A.');
        return false;
    }
    if (isNaN(distanceB) || distanceB < 0) {
        displayError('Please enter a valid non-negative distance for Point B.');
        return false;
    }
    if (isNaN(distanceC) || distanceC < 0) {
        displayError('Please enter a valid non-negative distance for Point C.');
        return false;
    }
    return true;
}

/**
 * Displays the calculated coordinates in the UI.
 * @param {Array<Object>} coordinates - The calculated latitude and longitude.
 */
function displayResults(coordinates) {
    const resultDiv = document.getElementById('result');
    const coordinatesList = document.getElementById('coordinates-list');

    coordinatesList.innerHTML = '';

    if (Array.isArray(coordinates)) {
        coordinates.forEach((coord, index) => {
            const li = document.createElement('li');
            li.textContent = `Solution ${index + 1}: Latitude: ${coord.latitude.toFixed(6)}, Longitude: ${coord.longitude.toFixed(6)}`;
            coordinatesList.appendChild(li);
        });
    } else {
        const li = document.createElement('li');
        li.textContent = `Latitude: ${coordinates.latitude.toFixed(6)}, Longitude: ${coordinates.longitude.toFixed(6)}`;
        coordinatesList.appendChild(li);
    }

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

    resultDiv.style.display = 'none';
    errorDiv.style.display = 'none';
}

/**
 * Clears previous visualization elements from the scene.
 * @param {THREE.Scene} scene - The Three.js scene.
 */
function clearVisualization(scene) {
    scene.children.forEach((child) => {
        if (child.name && child.name.startsWith('Circle_')) {
            scene.remove(child);
        }
    });

    const existingIntersection = scene.getObjectByName('IntersectionPoint');
    if (existingIntersection) {
        scene.remove(existingIntersection);
    }

    const existingIntersection2 = scene.getObjectByName('IntersectionPoint2');
    if (existingIntersection2) {
        scene.remove(existingIntersection2);
    }
}

/**
 * Visualizes the triangulation by drawing circles and marking the intersection points.
 * @param {THREE.Scene} scene - The Three.js scene.
 * @param {Array<Object>} coordinates - The calculated latitude and longitude.
 * @param {Array<THREE.Mesh>} referenceMarkers - Array of reference point markers.
 * @param {Array<number>} distances - Distances from each reference point.
 */
function visualizeTriangulation(scene, coordinates, referenceMarkers, distances) {
    coordinates.forEach((coord, index) => {
        const newPos = latLngToVector3(coord.latitude, coord.longitude, 1.0);

        const intersectionMarker = createMarker(newPos, 0x00ff00, 0.012); // Green marker
        intersectionMarker.name = `IntersectionPoint${index > 0 ? index + 1 : ''}`;
        scene.add(intersectionMarker);
    });

    referenceMarkers.forEach((marker, index) => {
        const distance = distances[index]; // Distance in km
        drawCircle(scene, marker.position, distance, getColor(index));
    });
}

/**
 * Returns a distinct color based on the index.
 * @param {number} index - The index of the reference point.
 * @returns {number} - The hexadecimal color code.
 */
function getColor(index) {
    const colors = [0xff0000, 0x00ff00, 0x0000ff]; // Red, Green, Blue
    return colors[index % colors.length];
}
