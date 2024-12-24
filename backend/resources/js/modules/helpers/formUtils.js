/**
 * Retrieves the CSRF token from the meta tag.
 * @returns {string} - The CSRF token.
 */
export function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}
