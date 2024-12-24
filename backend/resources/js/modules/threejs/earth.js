// earth.js
import * as THREE from 'three';

/**
 * Creates Earth meshes (day, night, clouds) and adds them to a group.
 * @returns {Promise<THREE.Group>} - The group containing Earth meshes.
 */
export async function createEarth() {
    const group = new THREE.Group();

    const loader = new THREE.TextureLoader();

    // Load Textures
    const [
        earthMap,
        earthBump,
        earthSpec,
        cloudMap,
        cloudAlpha
    ] = await Promise.all([
        loadTexture(loader, '/textures/00_earthmap1k.jpg'),
        loadTexture(loader, '/textures/01_earthbump1k.jpg'),
        loadTexture(loader, '/textures/02_earthspec1k.jpg'),
        loadTexture(loader, '/textures/04_earthcloudmap.jpg'),
        loadTexture(loader, '/textures/05_earthcloudmaptrans.jpg')
    ]);

    // Geometry
    const detail = 12;
    const earthGeo = new THREE.IcosahedronGeometry(1, detail);

    // Day Earth
    const earthDayMat = new THREE.MeshPhongMaterial({
        map: earthMap,
        specularMap: earthSpec,
        bumpMap: earthBump,
        bumpScale: 0.04,
    });
    const earthDayMesh = new THREE.Mesh(earthGeo, earthDayMat);
    group.add(earthDayMesh);

    // Clouds
    const cloudsMat = new THREE.MeshStandardMaterial({
        map: cloudMap,
        alphaMap: cloudAlpha,
        transparent: true,
        opacity: 0.5,
        depthWrite: false,
    });
    const cloudsMesh = new THREE.Mesh(earthGeo, cloudsMat);
    cloudsMesh.scale.setScalar(1.01);
    group.add(cloudsMesh);

    return group;
}


/**
 * Loads a texture and returns a promise.
 * @param {THREE.TextureLoader} loader - The texture loader.
 * @param {string} path - The path to the texture.
 * @returns {Promise<THREE.Texture>} - The loaded texture.
 */
function loadTexture(loader, path) {
    return new Promise((resolve, reject) => {
        loader.load(
            path,
            (texture) => resolve(texture),
            undefined,
            (err) => reject(err)
        );
    });
}
