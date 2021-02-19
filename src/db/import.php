<?php
/**
 * TODO
 *  Open web/airports.php file
 *  Go through all airports in a loop and INSERT airports/cities/states to equivalent DB tables
 *  (make sure, that you do not INSERT the same values to the cities and states i.e. name should be unique i.e. before INSERTing check if record exists)
 */

/** @var \PDO $pdo */
require_once './pdo_ini.php';

foreach (require_once('../web/airports.php') as $item) {
    // Cities
    // To check if city with this name exists in the DB we need to SELECT it first
    $sth = $pdo->prepare('SELECT id FROM cities WHERE name = :name');
    $sth->setFetchMode(\PDO::FETCH_ASSOC);
    $sth->execute(['name' => $item['city']]);
    $city = $sth->fetch();

    // If result is empty - we need to INSERT city
    if (!$city) {
        $sth = $pdo->prepare('INSERT INTO cities (name) VALUES (:name)');
        $sth->execute(['name' => $item['city']]);

        // We will use this variable to INSERT airport
        $cityId = $pdo->lastInsertId();
    } else {
        // We will use this variable to INSERT airport
        $cityId = $city['id'];
    }

    // States
    // To check if state with this name exists in the DB we need to SELECT it first
    $sth = $pdo->prepare('SELECT id FROM states WHERE name = :name');
    $sth->setFetchMode(\PDO::FETCH_ASSOC);
    $sth->execute(['name' => $item['state']]);
    $state = $sth->fetch();

    // If result is empty - we need to INSERT city
    if (!$state) {
        $sth = $pdo->prepare('INSERT INTO states (name) VALUES (:name)');
        $sth->execute(['name' => $item['state']]);

        // We will use this variable to INSERT airport
        $stateId = $pdo->lastInsertId();
    } else {
        // We will use this variable to INSERT airport
        $stateId = $state['id'];
    }

    // Airports
    $sth = $pdo->prepare('INSERT INTO airports (name, code, city_id, state_id, address, timezone) 
                                   VALUES (:name, :code, :city_id, :state_id, :address, :timezone)');
    $sth->bindParam(':name', $item['name']);
    $sth->bindParam(':code', $item['code']);
    $sth->bindParam(':city_id', $cityId);
    $sth->bindParam(':state_id', $stateId);
    $sth->bindParam(':address', $item['address']);
    $sth->bindParam(':timezone', $item['timezone']);
    $sth->execute();
}