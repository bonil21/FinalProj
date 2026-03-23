import './bootstrap.js';

import './styles/app.css';

/**
 * Submit add-to-cart via a programmatically built form (same CSRF + POST as Twig forms).
 * Use from Stimulus or inline onclick only when a real form is not present.
 */
window.addToCart = function (productId, csrfToken) {
    const id = parseInt(String(productId), 10);
    if (!id) {
        return;
    }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/cart/add/product/' + id;

    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = csrfToken || '';
    form.appendChild(token);

    document.body.appendChild(form);
    form.submit();
};
