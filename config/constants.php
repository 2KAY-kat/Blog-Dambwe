<?php
session_start();

// For browser/URL paths (links, redirects, assets)
define('ROOT_URL', 'http://localhost/Blog-Dambwe/');

// For filesystem paths (include, require) this shit is more secure and its best since its faster 
define('ROOT_PATH', dirname(__DIR__));

define('DB_HOST', 'localhost');
define('DB_USER', 'dambwedb');
define('DB_PASS', 'admin2030db');
define('DB_NAME', 'blog');


//y0ucan+ju$+br3ak1n