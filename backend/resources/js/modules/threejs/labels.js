import * as THREE from 'three';

/**
 * Creates a text label as a sprite.
 * @param {string} text - The text to display.
 * @param {THREE.Vector3} position - The position of the label.
 * @returns {THREE.Sprite} - The label sprite.
 */
export function createLabel(text, position) {
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    const fontSize = 64;
    context.font = `${fontSize}px Arial`;
    context.fillStyle = 'white';
    context.textAlign = 'center';
    context.fillText(text, canvas.width / 2, canvas.height / 2);

    const texture = new THREE.CanvasTexture(canvas);
    texture.needsUpdate = true;

    const spriteMaterial = new THREE.SpriteMaterial({ map: texture, transparent: true });
    const sprite = new THREE.Sprite(spriteMaterial);
    sprite.position.copy(position);
    sprite.scale.set(0.5, 0.25, 1);

    return sprite;
}
