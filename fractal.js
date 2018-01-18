'use strict';

/*
* Require the path module
*/
const path = require('path');

/*
 * Require the Fractal module
 */
const fractal = module.exports = require('@frctl/fractal').create();

/*
 * Give your project a title.
 */
fractal.set('project.title', 'Fractal Twig Boilerplate');

/*
 * Tell Fractal where to look for components.
 */
//fractal.components.set('path', path.join(__dirname, 'patterns'));
fractal.components.set('path', __dirname + '/patterns');
//console.log(fractal.components.fullPath);

/*
 * Tell Fractal where to look for documentation pages.
 */
fractal.docs.set('path', path.join(__dirname, 'docs'));

/*
 * Tell the Fractal web preview plugin where to look for static assets.
 */
fractal.web.set('static.path', path.join(__dirname, 'dist'));
//fractal.web.set('static.mount', __dirname + 'dist');
fractal.web.set('builder.dest', path.join(__dirname, 'styleguide'));

const twigAdapter = require('@frctl/twig');
fractal.components.engine(twigAdapter);
fractal.components.set('ext', '.twig');
