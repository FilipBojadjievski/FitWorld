<?php
// Model/get_available_gyms.php

require_once('./Model/database.php');
require_once('./Model/gym_db.php');

// 1. Fetch all public facilities
$gyms_catalog = get_all_public_gyms($pdo);

// 2. Attach upcoming events using your teammate's model function
foreach ($gyms_catalog as $key => $gym) {
    $gyms_catalog[$key]['events'] = get_upcoming_events_by_gym($pdo, $gym['id']);
}

// 3. Render the clean view layout
include('./View/available_gyms_page.php');