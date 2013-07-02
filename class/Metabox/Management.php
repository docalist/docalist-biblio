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

use Docalist\AbstractMetabox, Docalist\Forms\Fragment;

class Management extends AbstractMetabox {
    /**
     * @inheritdoc
     */
    public function __construct() {
        $box = new Fragment();

        //@formatter:off
        $box->label(__('Informations de gestion', 'docalist-biblio'));

        $box->input('ref')
            ->label(__('Numéro de référence', 'docalist-biblio'))
            ->addClass('span2')
            ->description(__('Numéro unique identifiant la notice.', 'docalist-biblio'));

        $box->input('owner')
            ->label(__('Propriétaire de la notice', 'docalist-biblio'))
            ->addClass('span2')
            ->description(__('Personne ou centre de documentation qui a produit la notice.', 'docalist-biblio'))
            ->repeatable(true);

        $box->table('creation')
            ->label(__('Date de création', 'docalist-biblio'))
            ->description(__('Date de création de la notice.', 'docalist-biblio'))
                ->input('date')
                ->label(__('Le', 'docalist-biblio'))
                ->addClass('span2')
            ->parent()
                ->input('by')
                ->label(__('Par', 'docalist-biblio'))
                ->addClass('span2');

        $box->table('lastupdate')
            ->label(__('Dernière modification', 'docalist-biblio'))
            ->description(__('Date de dernière mise à jour de la notice.', 'docalist-biblio'))
                ->input('date')
                ->label(__('Le', 'docalist-biblio'))
                ->addClass('span2')
            ->parent()
                ->input('by')
                ->label(__('Par', 'docalist-biblio'))
                ->addClass('span2');

        //@formatter:on

        $this->form = $box;
    }
}