import * as THREE from 'three';
import { latLngToVector3 } from './helpers';
import { createLabel } from './labels';

/**
 * Adds reference markers to the EarthGroup.
 * @param {THREE.Group} earthGroup - The Earth mesh group.
 * @returns {Array<THREE.Mesh>} - Array of marker meshes.
 */
export function addMarkers(earthGroup) {
    const referencePoints = [
        { name: 'A', lat: 50.110889, lng: 8.682139 },   // Frankfurt
        { name: 'B', lat: 39.048111, lng: -77.472806 }, // Washington D.C.
        { name: 'C', lat: 45.849100, lng: -119.714000 }, // Portland
    ];

    const markers = [];

    referencePoints.forEach((point) => {
        const position = latLngToVector3(point.lat, point.lng);
        const marker = createMarker(position, 0xff0000, 0.01); // Red markers
        marker.name = point.name;
        earthGroup.add(marker);
        markers.push(marker);

        // Create and add label
        const labelPosition = position.clone().normalize().multiplyScalar(1.05);
        const label = createLabel(point.name, labelPosition);
        earthGroup.add(label);
    });

    return markers;
}

/**
 * Creates a spherical marker.
 * @param {THREE.Vector3} position - Position of the marker.
 * @param {number} color - Color of the marker.
 * @param {number} size - Size of the marker.
 * @returns {THREE.Mesh} - The marker mesh.
 */
export function createMarker(position, color = 0xff0000, size = 0.02) {
    const geometry = new THREE.SphereGeometry(size, 8, 8);
    const material = new THREE.MeshBasicMaterial({ color });
    const marker = new THREE.Mesh(geometry, material);
    marker.position.copy(position);
    return marker;
}
