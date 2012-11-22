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
 * Description: Docalist: bibliographic data manager.
 * Version:     0.1
 * Author:      Docalist
 * Author:      Daniel Ménard
 * Author URI:  http://docalist.org/
 * Domain Path: /languages
 * 
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;
use Docalist;

if (class_exists('Docalist')) {
    // Enregistre notre espace de noms
    Docalist::registerNamespace(__NAMESPACE__, __DIR__ . '/class');
    
    // Charge le plugin
    Docalist::load('Docalist\\Biblio\\Plugin', __FILE__);
}
