<?php

/**
 * The $airports variable contains array of arrays of airports (see airports.php)
 * What can be put instead of placeholder so that function returns the unique first letter of each airport name
 * in alphabetical order
 *
 * Create a PhpUnit test (GetUniqueFirstLettersTest) which will check this behavior
 *
 * @return string[]
 */
function getUniqueFirstLetters()
{
    global $pdo;

    $sth = $pdo->prepare('SELECT DISTINCT (LEFT(name, 1)) AS firstLetter FROM airports ORDER BY firstLetter');
    $sth->execute();
    return $sth->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * The function checks $_GET and adds into it new parameters
 *
 * The function returns string which includes new parameters
 *
 * @param array $get parameters from $_GET
 * @param array $link new parameters
 * @return string
 */
function getLink($get, array $link = [])
{
    $get_params = [];

    if (isset($get['filter_by_first_letter'])) {
        $get_params['filter_by_first_letter'] = $get['filter_by_first_letter'];
    }

    if (isset($get['filter_by_state'])) {
        $get_params['filter_by_state'] = $get['filter_by_state'];
    }

    if (isset($get['sort'])) {
        $get_params['sort'] = $get['sort'];
    }

    $get_params = array_replace($get_params, $link);

    $url = '';
    foreach ($get_params as $key => $param) {
        $url .= "&$key=$param";
    }

    return $url;
}

/**
 * The $airports variable contains array of arrays of airports (see airports.php). It may be changed by
 * 'filterByFirstLetter' or (and) 'filterByState' functions.
 *
 * The function builds SELECT query to DB with all filters / sorting / pagination
 * and set the result to $airports variable
 *
 * @param int $currentPage
 * @param int $from
 * @param int $airportsPerPage
 * @param array $get
 * @return array
 */
function processRequest(int $currentPage, int $from, int $airportsPerPage, $get)
{
    $firstLetter = "{$get['filter_by_first_letter']}%";
    $filterByState = "{$get['filter_by_state']}%";

    if (isset($get['sort'])) {
        $sort = 'ORDER BY ' . $get['sort'];
    } else {
        $sort = '';
    }

    global $pdo;

    $sth = $pdo->prepare('SELECT COUNT(*) AS count, a.name, a.code, s.name AS state, c.name AS city, a.address,
                                   a.timezone
                                   FROM airports a 
                                   LEFT JOIN states s ON a.state_id = s.id
                                   LEFT JOIN cities c ON a.city_id = c.id
                                   WHERE a.name LIKE :firstLetter && s.name LIKE :filterByState');
    $sth->bindParam(':firstLetter', $firstLetter, PDO::PARAM_STR);
    $sth->bindParam(':filterByState', $filterByState, PDO::PARAM_STR);
    $sth->execute();
    $airportsCount = $sth->fetchColumn();

    $pageQty = ceil($airportsCount / $airportsPerPage);

    if ($currentPage >= 1 && $currentPage <= $pageQty) {
        $sth = $pdo->prepare("SELECT a.name, a.code, s.name AS state, c.name AS city, a.address, a.timezone
                                   FROM airports a 
                                   LEFT JOIN states s ON a.state_id = s.id
                                   LEFT JOIN cities c ON a.city_id = c.id
                                   WHERE a.name LIKE :firstLetter && s.name LIKE :filterByState
                                   $sort
                                   LIMIT :from, :airportsPerPage");
        $sth->bindParam(':firstLetter', $firstLetter, PDO::PARAM_STR);
        $sth->bindParam(':filterByState', $filterByState, PDO::PARAM_STR);
        $sth->bindParam(':from', $from, PDO::PARAM_INT);
        $sth->bindParam(':airportsPerPage', $airportsPerPage, PDO::PARAM_INT);
        $sth->execute();
        $airports = $sth->fetchAll(PDO::FETCH_ASSOC);

        $airports['pageQty'] = $pageQty;

        return $airports;
    }
}
