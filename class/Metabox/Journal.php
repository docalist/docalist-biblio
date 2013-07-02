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

class Journal extends AbstractMetabox {
    /**
     * @inheritdoc
     */
    public function __construct() {
        $box = new Fragment();

        //@formatter:off
        $box->label(__('Journal / périodique', 'docalist-biblio'));

        $box->input('journal')
            ->label(__('Titre de périodique', 'docalist-biblio'))
            ->description(__('Nom du journal (revue, magazine, périodique, etc.) dans lequel a été publié le document.', 'docalist-biblio'))
            ->attribute('class', 'large-text');

        $box->input('issn')
            ->label(__('ISSN', 'docalist-biblio'))
            ->description(__('International Standard Serial Number : numéro international identifiant le périodique dont le nom figure dans le champ Journal.', 'docalist-biblio'));

        $box->input('volume')
            ->label(__('Numéro de volume', 'docalist-biblio'))
            ->description(__('Pour les publications en série, ce champ content le numéro de volume du fascicule. Pour les monographies, ce champ contient le numéro de tome du document référencé dans la notice.', 'docalist-biblio'));

        $box->input('issue')
            ->label(__('Numéro de fascicule', 'docalist-biblio'))
            ->description(__("Indique le numéro du fascicule de la revue dans lequel l'article a été publié.", 'docalist-biblio'));
        //@formatter:on

        $this->form = $box;
    }
}
