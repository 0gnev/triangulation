import * as THREE from 'three';

/**
 * Converts latitude and longitude to a Vector3 position on the globe.
 * @param {number} lat - Latitude in degrees.
 * @param {number} lng - Longitude in degrees.
 * @param {number} [radius=1] - Radius of the globe.
 * @returns {THREE.Vector3} - The corresponding Vector3 position.
 */
export function latLngToVector3(lat, lng, radius = 1) {
    const phi = THREE.MathUtils.degToRad(lat - 90);
    const theta = THREE.MathUtils.degToRad(-lng);

    // Spherical to Cartesian conversion
    const x = radius * Math.sin(phi) * Math.cos(theta);
    const y = radius * Math.cos(phi);
    const z = radius * Math.sin(phi) * Math.sin(theta);

    return new THREE.Vector3(x, y, z);
}
/**
 * Starts the animation loop for rendering the scene.
 * @param {THREE.WebGLRenderer} renderer - The renderer.
 * @param {THREE.Scene} scene - The scene.
 * @param {THREE.Camera} camera - The camera.
 * @param {OrbitControls} controls - The orbit controls.
 * @param {THREE.Group} earthGroup - The Earth mesh group.
 */
export function startAnimation(renderer, scene, camera, controls, earthGroup) {
    function animate() {
        requestAnimationFrame(animate);

        controls.update();

        renderer.render(scene, camera);
    }
    animate();
}
