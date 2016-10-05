# BEA Critical CSS #

## Description ##

Load CriticalCSS files for WP Themes

## Important to know ##

Usage
-----

Add theme support in theme with the list of stylesheets to connect with this mu-plugin.

**Basic**

    <?php
    
    add_action( 'after_setup_theme', 'bea_setup' );
    function bea_setup() {
        /** Critical CSS */
        add_theme_support( 'bea-critical-css', [ 'theme-style' ] );
    }

## Changelog ##

### 1.0.0
* 05 October 2016
* Initial
