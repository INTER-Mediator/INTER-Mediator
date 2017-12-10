<?php
require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'prefecture',
            'table' => 'not_available',
            'view' => 'postalcode',
            'aggregation-select'=>'MIN(id) AS pref_id, f7 AS pref',
            'aggregation-from'=>'postalcode',
            'aggregation-group-by'=>'f7',
            'records' => 10000,
            'maxrecords' => 10000,
            'key' => 'pref_id',
            'navi-control' => 'step',
            'before-move-nextstep'=>'doAfterPrefSelection'
        ),
        array(
            'name' => 'city',
            'table' => 'not_available',
            'view' => 'postalcode',
            'aggregation-select'=>'MIN(id) AS city_id, f8 AS city',
            'aggregation-from'=>'postalcode',
            'aggregation-group-by'=>'f8',
            'records' => 10000,
            'maxrecords' => 10000,
            'key' => 'city_id',
            'navi-control' => 'step-hide',
            'before-move-nextstep'=>'doAfterCitySelection'
        ),
        array(
            'name' => 'town',
            'table' => 'not_available',
            'view' => 'postalcode',
            'aggregation-select'=>'MIN(id) AS town_id, f9 AS town',
            'aggregation-from'=>'postalcode',
            'aggregation-group-by'=>'f9',
            'records' => 10000,
            'maxrecords' => 10000,
            'key' => 'town_id',
            'navi-control' => 'step-hide',
            'before-move-nextstep'=>'doAfterTownSelection'
        ),
        array(
            'name' => 'wrapup',
            'table' => 'not_available',
            'view' => 'postalcode',
            'records' => 10000,
            'maxrecords' => 10000,
            'key' => 'id',
            'navi-control' => 'step-hide',
            'before-move-nextstep'=>'doAfterLastSelection'
        ),
    ),
    array(
        'credit-including' => 'footer',
    ),
    array(
        'db-class' => 'PDO',
    ),
    false
);
