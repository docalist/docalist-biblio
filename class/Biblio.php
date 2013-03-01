<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;
use Docalist\Plugin;

/**
 * Plugin de gestion de notices bibliographiques.
 */
class Biblio extends Plugin {
    /**
     * @inheritdoc
     */
     public function register() {
        // Configuration du plugin
        $this->add(new Settings);

        // Taxonomies
        $this->add(new Taxonomy\RefType);
        $this->add(new Taxonomy\RefGenre);
        $this->add(new Taxonomy\Country);
        $this->add(new Taxonomy\Language);
        $this->add(new Taxonomy\RefMedia);
        $this->add(new Taxonomy\RefRole);
        $this->add(new Taxonomy\RefTitle);

        // Custom Post Types
        $this->add(new References);
     }

}
