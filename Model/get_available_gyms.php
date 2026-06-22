<?php

require_once('./Model/database.php');
require_once('./Model/gym_db.php');

$gyms_catalog = get_all_public_gyms($pdo);

foreach ($gyms_catalog as $key => $gym) {
    $gyms_catalog[$key]['events'] = get_upcoming_events_by_gym($pdo, $gym['id']);
}

include('./View/available_gyms_page.php');