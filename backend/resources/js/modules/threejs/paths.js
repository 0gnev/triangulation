import * as THREE from 'three';

/**
 * Draws a straight path between two points on the surface of a sphere.
 * @param {THREE.Scene} scene - The Three.js scene.
 * @param {THREE.Vector3} start - Starting point.
 * @param {THREE.Vector3} end - Ending point.
 * @param {number} [radius=1] - Radius of the sphere.
 * @param {number} [color=0xffffff] - Color of the path.
 * @returns {THREE.Line} - The created straight path line.
 */
export function drawPath(scene, start, end, radius = 1.01, color = 0xffffff) {
    const startOnSphere = start.clone().normalize().multiplyScalar(radius);
    const endOnSphere = end.clone().normalize().multiplyScalar(radius);

    const points = [];
    const numPoints = 100;
    for (let i = 0; i <= numPoints; i++) {
        const t = i / numPoints; // Interpolation parameter
        const interpolatedPoint = new THREE.Vector3()
            .copy(startOnSphere)
            .lerp(endOnSphere, t)
            .normalize()
            .multiplyScalar(radius); // Ensure point stays on the sphere
        points.push(interpolatedPoint);
    }

    // Create geometry and material for the line
    const geometry = new THREE.BufferGeometry().setFromPoints(points);
    const material = new THREE.LineBasicMaterial({ color });

    // Create the line and add it to the scene
    const line = new THREE.Line(geometry, material);
    scene.add(line);

    return line;
}
