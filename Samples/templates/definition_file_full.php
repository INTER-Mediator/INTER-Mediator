<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'string',
            'table' => 'string',
            'view' => 'string',
            'records' => 'integer',
            'maxrecords' => 'integer',
            'paging' => 'boolean',
            'key' => 'string',
            'sequence' => 'string',
            'relation' => array(
                array(
                    'foreign-key' => 'string',
                    'join-field' => 'string',
                    'operator' => 'string',
                    'portal' => 'boolean'
                )
            ),
            'query' => array(
                array(
                    'field' => 'string',
                    'value' => 'scalar',
                    'operator' => 'string'
                )
            ),
            'sort' => array(
                array(
                    'field' => 'string',
                    'direction' => 'string'
                )
            ),
            'default-values' => array(
                array(
                    'field' => 'string',
                    'value' => 'scalar'
                )
            ),
            'repeat-control' => 'string(insert|delete|confirm-insert|confirm-delete)',
            'navi-control' => 'string(master|detail|master-hide|detail-top|detail-bottom)',
            'validation' => array(
                array(
                    'field' => 'string',
                    'rule' => 'string',
                    'message' => 'string',
                    'notify' => 'string(alert|inline|end-of-sibling)',
                )
            ),
            'post-repeater' => 'string',
            'post-enclosure' => 'string',
            'script' => array(
                array(
                    'db-operation' => 'string(read|update|new|create|delete)',
                    'situation' => 'string(pre|presort|post)',
                    'definition' => 'string'
                )
            ),
            'global' => array(
                array(
                    'db-operation' => 'string(read|update|new|create|delete)',
                    'field' => 'string',
                    'value' => 'scalar'
                )
            ),
            'soft-delete' => true,
            'authentication' => array(
                'media-handling' => 'boolean',
                'all' => array(
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string'
                ),
                'read' => array(
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string'
                ),
                'update' => array(
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string'
                ),
                'new' => array(
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string'
                ),
                'create' => array(
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string'
                ),
                'delete' => array(
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string'
                )
            ),
            'extending-class' => 'string',
            'protect-writing' => 'array',
            'protect-reading' => 'array',
            'db-class' => 'string',
            'dsn' => 'string',
            'option' => 'string',
            'database' => 'string',
            'user' => 'string',
            'password' => 'string',
            'server' => 'string',
            'port' => 'string',
            'protocol' => 'string',
            'datatype' => 'string',
            'cache' => 'boolean',
            'post-reconstruct' => 'boolean',
            'post-dismiss-message' => 'string',
            'post-move-url' => 'string',
            'file-upload' => array(
                array(
                    'field' => 'string',
                    'context' => 'string',
                    'container' => 'boolean',
                )
            ),
            'calculation' => array(
                array(
                    'field' => 'string',
                    'expression' => 'string',
                )
            ),
            'send-mail' => array(
                'read' => array(
                    'from' => 'string',
                    'to' => 'string',
                    'cc' => 'string',
                    'bcc' => 'string',
                    'subject' => 'string',
                    'body' => 'string',
                    'from-constant' => 'string',
                    'to-constant' => 'string',
                    'cc-constant' => 'string',
                    'bcc-constant' => 'string',
                    'subject-constant' => 'string',
                    'body-constant' => 'string',
                    'body-template' => 'string',
                    'body-fields' => 'string',
                    'f-option' => 'boolean',
                    'body-wrap' => 'integer',
                ),
                'new' => array(
                    'from' => 'string',
                    'to' => 'string',
                    'cc' => 'string',
                    'bcc' => 'string',
                    'subject' => 'string',
                    'body' => 'string',
                    'from-constant' => 'string',
                    'to-constant' => 'string',
                    'cc-constant' => 'string',
                    'bcc-constant' => 'string',
                    'subject-constant' => 'string',
                    'body-constant' => 'string',
                    'body-template' => 'string',
                    'body-fields' => 'string',
                    'f-option' => 'boolean',
                    'body-wrap' => 'integer',
                ),
                'create' => array(
                    'from' => 'string',
                    'to' => 'string',
                    'cc' => 'string',
                    'bcc' => 'string',
                    'subject' => 'string',
                    'body' => 'string',
                    'from-constant' => 'string',
                    'to-constant' => 'string',
                    'cc-constant' => 'string',
                    'bcc-constant' => 'string',
                    'subject-constant' => 'string',
                    'body-constant' => 'string',
                    'body-template' => 'string',
                    'body-fields' => 'string',
                    'f-option' => 'boolean',
                    'body-wrap' => 'integer',
                ),
                'update' => array(
                    'from' => 'string',
                    'to' => 'string',
                    'cc' => 'string',
                    'bcc' => 'string',
                    'subject' => 'string',
                    'body' => 'string',
                    'from-constant' => 'string',
                    'to-constant' => 'string',
                    'cc-constant' => 'string',
                    'bcc-constant' => 'string',
                    'subject-constant' => 'string',
                    'body-constant' => 'string',
                    'body-template' => 'string',
                    'body-fields' => 'string',
                    'f-option' => 'boolean',
                    'body-wrap' => 'integer',
                ),
            ),
        ),
    ),
    array(
        'separator' => 'string',
        'formatter' => array(
            array('field' => 'string',
                'converter-class' => 'string',
                'parameter' => 'string',
            ),
        ),
        'aliases' => array(
            'target' => 'string',
        ),
        'browser-compatibility' => array(
            'Chrome' => '1+',
            'FireFox' => '1+',
            'msie' => '8+',
            'Safari' => '1+',
            'Opera' => '1+',
            'Trident' => '4+',
        ),
        'transaction' => 'string(none|automatic)',
        'authentication' => array(
            'user' => 'array',
            'group' => 'array',
            'user-table' => 'string',
            'group-table' => 'string',
            'corresponding-table' => 'string',
            'challenge-table' => 'string',
            'authexpired' => 'string',
            'storing' => 'string',
            'realm' => 'string',
            'email-as-username' => 'boolean',
            'issuedhash-dsn' => 'string',
        ),
        'media-root-dir' => 'string',
        'media-context' => 'string',
        'smtp' => array(
            'server' => 'string',
            'port' => 'integer',
            'username' => 'string',
            'password' => 'string',
        ),
        'pusher' => array(
            'app_id' => 'string',
            'key' => 'integer',
            'secret' => 'string',
            'channel' => 'string',
        )
    ),
    array(
        'db-class' => 'string',
        'dsn' => 'string',
        'option' => 'array',
        'database' => 'string',
        'user' => 'string',
        'password' => 'string',
        'server' => 'string',
        'port' => 'string',
        'protocol' => 'string',
        'datatype' => 'string',
        'external-db' => array('#' => 'string'),
    ),
    //todo ## Set the debug level to false, 1 or 2.
    false
);
