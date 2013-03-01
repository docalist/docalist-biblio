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
use Docalist\PostType;

/**
 * Gère le type de contenu "références bibliographiques"
 */
class References extends PostType {
    /**
     * @inheritdoc
     */
    protected $id = 'dclref';
public function NU__destruct(){
    global $dma,$occa;
    asort($occa);
    echo $dma;
    echo '<pre>', var_export($occa,true), '</pre>';
    die();
}
    /**
     * @inheritdoc
     */
    protected function options() {
        return array(
            'labels' => $this->setting('ref.labels'),
            'public' => true,
            'rewrite' => array(
                'slug' => $this->setting('ref.slug'),
                'with_front' => false,
            ),
            'capability_type' => 'post',
            'supports' => array(
//                'title',
                'editor',
//                'thumbnail',
            ),
            'supports' => false,
            'has_archive' => true,
        );
    }

    /**
     * @inheritdoc
     */
    protected function registerMetaboxes() {
        remove_meta_box('slugdiv', $this->id(), 'normal');
        $this->add(new Metabox\Type);
        $this->add(new Metabox\Title);
        $this->add(new Metabox\Authors);
        $this->add(new Metabox\Journal);
        $this->add(new Metabox\Biblio);
        $this->add(new Metabox\Editor);
        $this->add(new Metabox\Event);
        $this->add(new Metabox\Topics);
        $this->add(new Metabox\Management);
    }

}
