<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Plugin Name: Docalist Biblio
 * Plugin URI:  http://docalist.org
 * Plugin Type: Piklist
 * Description: Docalist : gestion de notices bibliographiques.
 * Version:     0.1
 * Author:      Docalist
 * Author URI:  http://docalist.org
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;
use Docalist\PluginManager; 

if (class_exists('Docalist\\PluginManager')) {
    PluginManager::load('Docalist\\Biblio\\Plugin', __DIR__);
}
