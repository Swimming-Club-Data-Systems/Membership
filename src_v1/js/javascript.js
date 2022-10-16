/**
 * Imports
 */

// import $ from 'jquery';
// window.bootstrap = require('bootstrap/dist/js/bootstrap.bundle.js');
import * as bootstrap from "bootstrap";
window.bootstrap = bootstrap;
// eslint-disable-next-line no-unused-vars
import Chart from "chart.js";
// eslint-disable-next-line no-undef
const MarkdownIt = require("markdown-it");
const markdown = new MarkdownIt();

// window.jQuery = $;
// window.$ = $;
window.markdownParser = markdown;