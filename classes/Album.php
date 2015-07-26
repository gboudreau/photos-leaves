<?php
namespace PhotosLeaves;

class Album {
    var $name;
    var $owner;
    var $createdDate;
    var $files = array();
    var $query;

    public function __construct($metadata) {
        if (empty($metadata->name)) {
            throw new \Exception("'name' metadata property is required when creating an Album object.");
        }
        $this->name = $metadata->name;
        if (isset($metadata->owner)) {
            $this->owner = $metadata->owner;
        } else {
            $this->owner = Config::get("DEFAULT_OWNER");
        }
        if (isset($metadata->created_date)) {
            $this->createdDate = $metadata->created_date;
        } else {
            $this->createdDate = date('Y-m-d H:i:s');
        }
        if (isset($metadata->query)) {
            $this->query = $metadata->query;
            $this->getFilesWithQuery();
        } else {
            if (empty($metadata->files)) {
                $metadata->files = array();
            }
            $this->files = $metadata->files;
        }
    }

    private function getFilesWithQuery() {
        $this->files = array();
        $q = "SELECT DISTINCT file FROM (SELECT photos.*, people.email, tags.tag FROM photos LEFT JOIN photos_people pp ON (photos.id = pp.photo_id) LEFT JOIN people ON (pp.person_id = people.id) LEFT JOIN photos_tags pt ON (photos.id = pt.photo_id) LEFT JOIN tags ON (pt.tag_id = tags.id) WHERE " . $this->query . ") a";
        $this->files = DB::getAllValues($q);
    }

    public function getFiles() {
        return $this->files;
    }
}
