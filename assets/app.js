import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// Add to cart functionality
window.addToCart = function(productId) {
    // TODO: Implement actual cart functionality
    alert('Product added to cart! (Product ID: ' + productId + ')');
    console.log('Add to cart:', productId);
};
