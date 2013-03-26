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

namespace Docalist\Biblio\Metabox;

use Docalist\Metabox, Docalist\Forms\Fragment;

class Authors extends Metabox {
    /**
     * @inheritdoc
     */
    public function __construct() {
        $box = new Fragment();

        //@formatter:off
        $box->label(__('Auteurs', 'docalist-biblio'));

        $box->table('author')
            ->label('Personnes')
            ->repeatable(true)
                ->input('name')
                ->label('Nom')
                ->addClass('span5')
            ->parent()
                ->input('firstname')
                ->label('Prénom')
                ->addClass('span4')
            ->parent()
                ->select('role')
                ->label('Rôle')
                ->options($this->taxonomy('dclrefrole'))
                ->addClass('span3');

        $box->table('organisation')
            ->label('Organismes')
            ->repeatable(true)
                ->input('name')
                ->label('Nom')
                ->addClass('span5')
            ->parent()
                ->input('city')
                ->label('Ville')
                ->addClass('span3')
            ->parent()
                ->select('country')
                ->label('Pays')
                ->options($this->taxonomy('dclcountry'))
                ->addClass('span2')
            ->parent()
                ->select('role')
                ->label('Rôle')
                ->options($this->taxonomy('dclrefrole'))
                ->addClass('span2');

        //@formatter:on

        $this->form = $box;
    }
}