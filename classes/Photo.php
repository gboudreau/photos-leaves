<?php
namespace PhotosLeaves;

class Photo {
    var $file;
    var $exif_data;

    public function __construct($file) {
        if (!file_exists($file)) {
            throw new \Exception("File not found: $file");
        }
        if (strrpos($file, '.jpg') != strlen($file)-4) {
            throw new \Exception("This can only work with JPEG (.jpg) files.");
        }
        $this->file = $file;
    }
    
    public function getMetadata() {
        if (empty($this->exif_data)) {
            $this->exif_data = exif_read_data($this->file);
        }
        $output = $this->exif_data['ImageDescription'];
        if (strpos($output, 'PL{') === 0) {
            $json = substr($output, 2);
            $data = json_decode($json);
        } else {
            $data = new \stdClass();
        }
        $data->file = trim(str_replace(Config::get('PHOTOS_DIR'), '', $this->file), '/');
        return $data;
    }

    public function saveMetadata($metadata) {
        $metadata = 'PL' . json_encode($metadata);
        $jpeg = new \lsolesen\pel\PelJpeg($this->file);
        $ifd0 = $jpeg->getExif()->getTiff()->getIfd();
        $entry = $ifd0->getEntry(\lsolesen\pel\PelTag::IMAGE_DESCRIPTION);
        $entry->setValue($metadata);
        $jpeg->saveFile($this->file);
    }

    public function saveInDB($metadata) {
        if (empty($metadata->id) && empty($metadata->file)) {
            throw new \Exception("'file' metadata property is required when saving a new photo in the database.");
        }

        $params = array();
        $updates = array();

        if (file_exists($this->file)) {
            if (empty($this->exif_data)) {
                $this->exif_data = exif_read_data($this->file);
            }
            unset($this->exif_data['FileName']);
            unset($this->exif_data['ImageDescription']);
            $updates[] = "exif_data = :exif_data";
            $params['exif_data'] = json_encode($this->exif_data);
        }

        if (empty($metadata->original_date) && !empty($this->exif_data)) {
            $date = NULL;
            if (!empty($this->exif_data['DateTimeOriginal'])) {
                $date = $this->exif_data['DateTimeOriginal'];
            }  else if (!empty($this->exif_data['DateTimeDigitized'])) {
                $date = $this->exif_data['DateTimeDigitized'];
            }  else if (!empty($this->exif_data['DateTime'])) {
                $date = $this->exif_data['DateTime'];
            }
            if (!empty($date)) {
                if (preg_match('/^(....).(..).(..) (..).(..).(..)$/', $date, $re)) {
                    $date = "$re[1]-$re[2]-$re[3] $re[4]:$re[5]:$re[6]";
                    $metadata->original_date = $date;
                }
            }
        }

        if (!empty($metadata->original_date)) {
            $updates[] = "original_date = :original_date";
            $params['original_date'] = $metadata->original_date;
        }
        if (!empty($metadata->upload_date)) {
            $updates[] = "upload_date = :upload_date";
            $params['upload_date'] = $metadata->upload_date;
        }
        if (!empty($metadata->id)) {
            $updates[] = "id = :id";
            $params['id'] = $metadata->id;
        }
        if (!empty($metadata->file)) {
            $updates[] = "file = :file";
            $params['file'] = $metadata->file;
        }
        if (!empty($metadata->title)) {
            $updates[] = "title = :title";
            $params['title'] = $metadata->title;
        }
        if (!empty($metadata->owner)) {
            $q = "INSERT IGNORE INTO people SET email = :email";
            $owner_id = DB::insert($q, $metadata->owner);
            if (empty($owner_id)) {
                $owner_id = DB::getFirstValue("SELECT id FROM people WHERE email = :email", $metadata->owner);
            }
            $updates[] = "owner = :owner";
            $params['owner'] = $owner_id;
        }

        if (empty($metadata->upload_batch_id)) {
            $metadata->upload_batch_id = Config::get('UPLOAD_BATCH_ID');
        }
        $updates[] = "upload_batch_id = :upload_batch_id";
        $params['upload_batch_id'] = $metadata->upload_batch_id;

        $q = "INSERT INTO photos SET " . implode(", ", $updates) . " ON DUPLICATE KEY UPDATE " . implode(", ", $updates);
        if (!empty($metadata->id)) {
            DB::execute($q, $params);
            $photo_id = $metadata->id;
            $metadata->upload_date = DB::getFirstValue("SELECT upload_date FROM photos WHERE id = :photo_id", $photo_id);
        } else {
            $photo_id = DB::insert($q, $params);
            $metadata->upload_date = date('Y-m-d H:i:s');
        }
        $metadata->id = $photo_id;

        $this->saveMetadata($metadata);

        if (!empty($metadata->people)) {
            DB::execute("DELETE FROM photos_people WHERE photo_id = :photo_id", $photo_id);

            $people_ids = Person::getIdsWithNames($metadata->people);

            $params = $people_ids;
            $params['photo_id'] = $photo_id;

            $q = "INSERT INTO photos_people (photo_id, person_id) VALUES ";
            foreach ($people_ids as $k => $person_id) {
                $q .= "(:photo_id, :$k),";
            }
            $q = trim($q, ',');
            DB::insert($q, $params);
        }

        if (!empty($metadata->tags)) {
            DB::execute("DELETE FROM photos_tags WHERE photo_id = :photo_id", $photo_id);

            $tag_ids = PhotoTag::getIdsWithNames($metadata->tags);

            $params = $tag_ids;
            $params['photo_id'] = $photo_id;

            $q = "INSERT INTO photos_tags (photo_id, tag_id) VALUES ";
            foreach ($tag_ids as $k => $tag_id) {
                $q .= "(:photo_id, :$k),";
            }
            $q = trim($q, ',');
            DB::insert($q, $params);
        }
    }
}
