<?php
/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    [
        [
            "name" => "testtable_auth",
            "view" => "testtable",
            "table" => "testtable",
            "key" => "id",
            "repeat-control" => "insert delete",
            "records" => 10000,
            "paging" => true,
            "sort" => [["field" => "dt1", "direction" => "DESC",],],
            "file-upload" => [["field" => "text1", "context" => "fileupload"],],
            "authentication" => ["media-handling" => true],
        ],
        [
            "name" => "fileupload",
            "key" => "id",
            "relation" => [["foreign-key" => "f_id", "join-field" => "id", "operator" => "="],],
        ],
    ],
    [
        "media-root-dir" => "/tmp",
        "authentication" => [
            "user" => ["user1", "user2"],
            "authexpired" => "10",
            "storing" => "credential",
        ],
    ],
    [
        'db-class' => 'PDO',
        'dsn' => getenv('GITHUB_ACTIONS')
            ? 'sqlite:/home/runner/work/INTER-Mediator/INTER-Mediator/sample.sq3'
            : 'sqlite:/var/db/im/sample.sq3',
    ],
    false
);
