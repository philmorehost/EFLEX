<?php

/*

    HTML Purifier 4.4.0 - Standards Compliant HTML Filtering
    Copyright (C) 2006-2008 Edward Z. Yang

    This library is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public
    License as published by the Free Software Foundation; either
    version 2.1 of the License, or (at your option) any later version.

    This library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public
    License along with this library; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA

 */

if (!defined('HTMLPURIFIER_PREFIX')) {
    define('HTMLPURIFIER_PREFIX', __DIR__);
}

// We need to be able to collect errors, so we need our own error handler.
// The user can still change the error handler if they want.
// It's a little bit egregious to do this in a library, but worth it
// for the functionality.
if (version_compare(PHP_VERSION, '5', '>=')) {
    // We can't use E_STRICT unless we're in PHP 5, but we'd like to be
    // E_STRICT-compliant.
    error_reporting(E_ALL | E_STRICT);
} else {
    error_reporting(E_ALL);
}

require_once HTMLPURIFIER_PREFIX . '/HTMLPurifier.php';
require_once HTMLPURIFIER_PREFIX . '/HTMLPurifier.autoload.php';
require_once HTMLPURIFIER_PREFIX . '/HTMLPurifier/Config.php';
require_once HTMLPURIFIER_PREFIX . '/HTMLPurifier/ConfigSchema.php';
require_once HTMLPURIFIER_PREFIX . '/HTMLPurifier/Lexer.php';
require_once HTMLPURIFIER_PREFIX . '/HTMLPurifier/Generator.php';
require_once HTMLPURIFIER_PREFIX . '/HTMLPurifier/Context.php';
require_once HTMLPURIFIER_PREFIX . '/HTMLPurifier/Definition.php';
require_once HTMLPURIFIER_PREFIX . '/HTMLPurifier/ElementDef.php';
require_once HTMLPURIFIER_PREFIX . '/HTMLPurifier/ChildDef.php';
require_once HTMLPURIFIER_PREFIX . '/HTMLPurifier/AttrDef.php';
require_once HTMLPURIFIER_PREFIX . '/HTMLPurifier/AttrTypes.php';

// setup HTMLPurifier_Bootstrap
HTMLPurifier_Bootstrap::registerAutoload();

// setup single file specific workarounds
if (file_exists(HTMLPURIFIER_PREFIX . '/HTMLPurifier.safe-includes.php')) {
    require_once HTMLPURIFIER_PREFIX . '/HTMLPurifier.safe-includes.php';
}
