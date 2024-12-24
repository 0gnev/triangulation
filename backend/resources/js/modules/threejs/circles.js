import * as THREE from 'three';
import { latLngToVector3 } from './helpers';

/**
 * Draws a circle on the globe representing a geodesic distance from a reference point.
 * @param {THREE.Scene} scene - The Three.js scene.
 * @param {THREE.Vector3} referencePoint - The position of the reference point on the globe.
 * @param {number} distanceKm - The geodesic distance in kilometers.
 * @param {number} color - The hexadecimal color code for the circle.
 */
export function drawCircle(scene, referencePoint, distanceKm, color = 0xffffff) {
    const earthRadius = 6371; // Earth's radius in kilometers

    const angularRadius = distanceKm / earthRadius;


    const segments = 64;


    const refVector = referencePoint.clone().normalize();


    let perpVector = new THREE.Vector3(0, 1, 0);
    if (refVector.equals(perpVector)) {
        perpVector = new THREE.Vector3(1, 0, 0);
    }
    perpVector.crossVectors(refVector, perpVector).normalize();


    const points = [];
    for (let i = 0; i <= segments; i++) {
        const theta = (i / segments) * 2 * Math.PI;
        const rotated = perpVector.clone().applyAxisAngle(refVector, theta);
        const point = refVector.clone().multiplyScalar(Math.cos(angularRadius))
            .add(rotated.multiplyScalar(Math.sin(angularRadius)));
        points.push(point.clone().multiplyScalar(1.01));
    }


    const circleGeometry = new THREE.BufferGeometry().setFromPoints(points);


    const circleMaterial = new THREE.LineBasicMaterial({
        color: color,
        transparent: true,
        opacity: 0.7,
        depthWrite: false,
    });


    const circle = new THREE.LineLoop(circleGeometry, circleMaterial);


    const refLat = THREE.MathUtils.radToDeg(Math.asin(refVector.y));
    const refLng = THREE.MathUtils.radToDeg(Math.atan2(refVector.z, refVector.x));
    circle.name = `Circle_${refLat}_${refLng}`;


    scene.add(circle);
}
