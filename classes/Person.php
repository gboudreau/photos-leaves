<?php
namespace PhotosLeaves;

class Person {
    static $people = array();

    public static function getIdsWithNames($people_names) {
        $q = "INSERT IGNORE INTO people (email) VALUES ";
        $people_to_add = array();
        foreach ($people_names as $person_name) {
            if (!isset(static::$people[$person_name])) {
                $k = count($people_to_add);
                $q .= "(:$k),";
                $people_to_add[] = $person_name;
            }
        }
        if (count($people_to_add) > 0) {
            $q = trim($q, ',');
            DB::execute($q, $people_to_add);

            $q = "SELECT id, email FROM people WHERE email IN (" . DB::inConditionQueryString($people_to_add) . ")";
            $people = DB::getAll($q, $people_to_add, 'email');
            static::$people = array_merge(static::$people, $people);
        }

        $people_ids = array();
        foreach ($people_names as $person_name) {
            $person = static::$people[$person_name];
            $people_ids[] = $person->id;
        }

        return $people_ids;
    }
}
