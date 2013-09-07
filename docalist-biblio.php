<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * Copyright (C) 2012,2013 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Plugin Name: Docalist Biblio
 * Plugin URI:  http://docalist.org/
 * Description: Docalist: bibliographic data manager.
 * Version:     0.2
 * Author:      Daniel Ménard
 * Author URI:  http://docalist.org/
 * Text Domain: docalist-biblio
 * Domain Path: /languages
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;
use Docalist, Docalist\Autoloader;

if (class_exists('Docalist')) {
    // Enregistre notre espace de noms
    Autoloader::register(__NAMESPACE__, __DIR__ . '/class');

    // Charge le plugin
    Docalist::load('Docalist\Biblio\Plugin', __FILE__);
}