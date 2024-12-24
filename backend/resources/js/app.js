import { setupScene } from './modules/threejs/scene';
import { createEarth } from './modules/threejs/earth';
import { addMarkers } from './modules/threejs/markers';
import { handleForm } from './modules/form';
import { startAnimation } from './modules/threejs/helpers';

document.addEventListener('DOMContentLoaded', async () => {
    const { scene, camera, renderer, controls } = setupScene();
    const earthGroup = await createEarth();
    scene.add(earthGroup);
    const referenceMarkers = addMarkers(scene);
    handleForm(scene, referenceMarkers);
    startAnimation(renderer, scene, camera, controls, earthGroup);
});
