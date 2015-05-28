<?php
namespace PhotosLeaves;

class PhotoTag {
    static $all_tags = array();

    public static function getIdsWithNames($tag_names) {
        $q = "INSERT IGNORE INTO tags (tag) VALUES ";
        $tags_to_add = array();
        foreach ($tag_names as $tag) {
            if (!isset(static::$all_tags[$tag])) {
                $k = count($tags_to_add);
                $q .= "(:$k),";
                $tags_to_add[] = $tag;
            }
        }
        if (count($tags_to_add) > 0) {
            $q = trim($q, ',');
            DB::execute($q, $tags_to_add);

            $q = "SELECT id, tag FROM tags WHERE tag IN (" . DB::inConditionQueryString($tags_to_add) . ")";
            $tags = DB::getAll($q, $tags_to_add, 'tag');
            static::$all_tags = array_merge(static::$all_tags, $tags);
        }

        $tag_ids = array();
        foreach ($tag_names as $tag_name) {
            $tag = static::$all_tags[$tag_name];
            $tag_ids[] = $tag->id;
        }

        return $tag_ids;
    }
}
