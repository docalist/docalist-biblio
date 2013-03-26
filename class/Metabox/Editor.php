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

class Editor extends Metabox {
    /**
     * @inheritdoc
     */
    public function __construct() {
        $box = new Fragment();

        //@formatter:off
        $box->label(__('Informations éditeur', 'docalist-biblio'));

        $box->table('editor')
            ->label(__('Editeur', 'docalist-biblio'))
            ->description(__('Editeur et lieu d\'édition.', 'docalist-biblio'))
            ->repeatable(true)
                ->input('name')
                ->label(__('Nom', 'docalist-biblio'))
                ->addClass('span5')
            ->parent()
                ->input('city')
                ->label(__('Ville', 'docalist-biblio'))
                ->addClass('span5')
            ->parent()
                ->select('country')
                ->label(__('Pays', 'docalist-biblio'))
                ->addClass('span2')
                ->options($this->taxonomy('dclcountry'));

        $box->table('collection')
            ->label(__('Collection', 'docalist-biblio'))
            ->description(__('Collection et numéro au sein de cette collection du document catalogué.', 'docalist-biblio'))
            ->repeatable(true)
                ->input('name')
                ->label('Nom')
                ->addClass('span9')
            ->parent()
                ->input('number')
                ->label(__('Numéro dans la collection', 'docalist-biblio'))
                ->addClass('span3');

        $box->table('edition')
            ->label(__('Mentions d\'édition', 'docalist-biblio'))
            ->description(__('Mentions d\'éditions (hors série, 2nde édition, etc.) et autres numéros du document (n° de rapport, de loi, etc.)', 'docalist-biblio'))
            ->repeatable(true)
                ->input('type')
                ->label(__('Mention', 'docalist-biblio'))
                ->addClass('span9')
            ->parent()
                ->input('value')
                ->label(__('Numéro', 'docalist-biblio'))
                ->addClass('span3');

        $box->input('isbn')
            ->label(__('ISBN', 'docalist-biblio'))
            ->addClass('span6')
            ->description(__('International Standard Book Number : identifiant unique pour les livres publiés.', 'docalist-biblio'));
        //@formatter:on

        $this->form = $box;
    }
}