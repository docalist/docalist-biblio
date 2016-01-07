<?php
namespace Docalist\Biblio\Entity;

require_once __DIR__ . '/EntityTestCase.php';
class ReferenceTest extends EntityTestCase {

    protected function checkType(Reference $ref) {
        $this->checkIs($ref, 'ref'          , 'int');
        $this->checkIs($ref, 'genre'        , 'string*');
        $this->checkIs($ref, 'media'        , 'string*');
        $this->checkIs($ref, 'author'       , 'Docalist\Biblio\Field\Author*');
        $this->checkIs($ref, 'organisation' , 'Docalist\Biblio\Field\Organisation*');
        $this->checkIs($ref, 'title'        , 'string');
        $this->checkIs($ref, 'othertitle'   , 'Docalist\Biblio\Field\OtherTitle*');
        $this->checkIs($ref, 'translation'  , 'Docalist\Biblio\Field\Translation*');
        $this->checkIs($ref, 'date'         , 'string');
        $this->checkIs($ref, 'journal'      , 'string');
        $this->checkIs($ref, 'issn'         , 'string');
        $this->checkIs($ref, 'volume'       , 'string');
        $this->checkIs($ref, 'issue'        , 'string');
        $this->checkIs($ref, 'language'     , 'string*');
        $this->checkIs($ref, 'pagination'   , 'string');
        $this->checkIs($ref, 'format'       , 'string');
        $this->checkIs($ref, 'isbn'         , 'string');
        $this->checkIs($ref, 'editor'       , 'Docalist\Biblio\Field\Editor*');
        $this->checkIs($ref, 'edition'      , 'Docalist\Biblio\Field\Edition*');
        $this->checkIs($ref, 'collection'   , 'Docalist\Biblio\Field\Collection*');
        $this->checkIs($ref, 'event'        , 'Docalist\Biblio\Field\Event');
        $this->checkIs($ref, 'degree'       , 'Docalist\Biblio\Field\Degree');
        $this->checkIs($ref, 'abstract'     , 'Docalist\Biblio\Field\AbstractField*');
        $this->checkIs($ref, 'topic'        , 'Docalist\Biblio\Field\Topic*');
        $this->checkIs($ref, 'note'         , 'Docalist\Biblio\Field\Note*');
        $this->checkIs($ref, 'link'         , 'Docalist\Biblio\Type\Link*');
        $this->checkIs($ref, 'doi'          , 'string');
        $this->checkIs($ref, 'relations'    , 'Docalist\Biblio\Field\Relation*');
        $this->checkIs($ref, 'owner'        , 'string*');
        $this->checkIs($ref, 'creation'     , 'Docalist\Biblio\Field\DateBy');
        $this->checkIs($ref, 'lastupdate'   , 'Docalist\Biblio\Field\DateBy');
        $this->checkIs($ref, 'status'       , 'string*');
    }

    public function testEmptyRef() {
        $this->checkType(new Reference());
    }
    public function testFullRef() {
        $ref = new Reference();

        $ref->ref=12;
        $ref->type = 'article';

        $ref->genre=array('essai', 'fiction');  // set
        $ref->media[]='papier';                 // append
        $ref->media[]='web';

        // set
        $ref->author = array(array('name'=>'Tau', 'firstname'=>'Albert'), array('name'=>'Vala', 'firstname'=>'Eve', 'role' => 'trad'));

        return $this->checkType($ref);
        // append
        $ref->organisation[] = array('name'=>'docalist', 'city'=>'Rennes', 'country' => 'fra');
        $ref->organisation[] = array('name'=>'artwai', 'city'=>'Rennes', 'country' => 'fra', 'role' => 'ed');

        $ref->title = 'A good title';
        $ref->othertitle[] = array('type' => 'dossier', 'title' => 'titre du dossier');
        $ref->translation[] = array('language' => 'fre', 'title' => 'Un bon titre');
        $ref->journal = 'Ma revue';
        $ref->issn = '1234-567x';
        $ref->volume = '2013';
        $ref->issue = '8';
        $ref->language = array('fre', 'eng');
        $ref->pagination = array('3', '10-12');
        $ref->format = array('tabl.', 'réf bib');
        $ref->isbn = '1-234-5678-9012x';
        $ref->editor[] = array('name'=>'docalist', 'city'=>'Rennes', 'country' => 'fra');
        $ref->editor[] = array('name'=>'artwai', 'city'=>'Rennes', 'country' => 'fra');
        $ref->edition[] = array('type'=>'edition', 'value'=>'2');
        $ref->edition[] = array('type'=>'reportnumber', 'value' => 'b412');
        $ref->collection[] = array('name' => 'Collection savoir', 'number' => '475');
        $ref->collection[] = array('name' => 'Informatique', 'number' => 'z30');
        $ref->event = array('title'=>'Colloque', 'date' => '2013', 'place' => 'Paris', 'number' => '5');
        $ref->degree = array('level' => 'licence', 'title' => 'licence informatique');
        $ref->abstract[] = array('language' => 'fre', 'content' => 'résumé français');
        $ref->abstract[] = array('language' => 'eng', 'content' => 'english abstract');
        $ref->topic[] = array('type' => 'geo', 'terms' => array('france', 'bretagne'));
        $ref->topic[] = array('type' => 'theso', 'terms' => array('informatique', 'GUI'));
        $ref->topic[] = array('type' => 'free', 'terms' => array('interface'));
        $ref->note[] = array('type' => 'public', 'content' => 'Professionnels');
        $ref->note[] = array('type' => 'access', 'content' => 'Disponible sur Cairn');
        $ref->link[] = array('type' => 'fulltext', 'url' => 'http://xyz', 'label' => 'consulter', 'date' => '20130821');
        $ref->link[] = array('type' => 'thumbnail', 'url' => 'http://uvw');
        $ref->doi = '1234567890';
        $ref->relations[] = array('type' => 'see', 'ref' => array(10208, 13477, 11206));
        $ref->relations[] = array('type' => 'neweditionof', 'ref' => array(7605));
        $ref->owner = array('dm', 'docalist');


        $ref->creation->date = '20130821'; //-> warning :Creating default object from empty value
        $ref->creation->by = 'dm';

        $ref->lastupdate = array('date' => '20130822', 'by' => 'md');

        $this->checkType($ref);
    }
}
