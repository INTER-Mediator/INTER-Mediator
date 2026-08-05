<?php

namespace INTERMediator;

/**
 * This class serves as a central registry for PHPStan type alias definitions used across the
 * INTER-Mediator project. It contains no runtime logic or properties — its sole purpose is to
 * host `@phpstan-type` annotations that other classes can import via `@phpstan-import-type`.
 *
 * @phpstan-type DBSpec array{'db-class': string|null, dsn: string|null, option: array<string|null, mixed>|null, 
 *     database: string|null, user: string|null, password: string|null, server: string|null, port: string|null, 
 *     protocol: string|null, datatype: string|null, 'cert-verifying': bool|null}
 * @phpstan-type FormatterDefinition array{field: string|null, 'converter-class': string|null, parameter: string|boolean|null}
 * @phpstan-type LocalContextDefinition array{key: string|null, value: string|boolean|integer|null}
 * @phpstan-type AliasesDefinition array<string|null, string>
 * @phpstan-type BrowserCompatibilityDefinition array<string|null, string>
 * @phpstan-type AuthenticationDefinition array{user: array<string>|null, group: array<string>|null, 
 *     'user-table': string|null, 'group-table': string|null, 'corresponding-table': string|null, 
 *     'challenge-table': string|null, authexpired: string|integer|null, storing: string|null, 
 *     realm: string|null, 'email-as-username': bool|null, 'issuedhash-dsn': string|null, 
 *     'password-policy': string|null, 'enroll-page': string|null, 'reset-page': string|null, 
 *     'is-saml': bool|null, 'saml-builtin-auth': bool|null, 'is-required-2FA': bool|null, 
 * 'digits-of-2FA-Code': int|null, 'mail-context-2FA': string|null, 'expiring-seconds-2FA': int|null, 
 * 'passkey-only-on-auth': bool|null, 'add-class-authn': bool|null, 'passkey-error-alerting': bool,
 * 'method-2FA': string|null, 'is-pass-through-2FA': bool|null}
 * @phpstan-type SMTPDefinition array{protocol: string|null, server: string|null, port: int|null, 
 *     username: string|null, password: string|null}
 * @phpstan-type SlackDefinition array{token: string|null, channel: string|null}
 * @phpstan-type ImportDefinition array{'1st-line': string|null, 'skip-lines': int|null, format: string|null, 
 * 'use-replace': bool|null, encoding: string|null, 'convert-number': array<string>|null, 
 * 'convert-date': array<string>|null, 'convert-datetime': array<string>|null}
 * @phpstan-type TermsDefinition array<string|null, mixed>
 * @phpstan-type MessagingDefinition array{from: string|null, to: string|null, cc: string|null, bcc: string|null, 
 *     subject: string|null, body: string|null, 'from-constant': string|null, 'to-constant': string|null, 
 *     'cc-constant': string|null, 'bcc-constant': string|null, 'subject-constant': string|null, 
 *     'body-constant': string|null, 'body-template': string|null, 'body-fields': string|null, 
 *     'f-option': bool|null, 'body-wrap': int|null, store: string|null, attachment: string|null, 'template-context': string|null}
 * @phpstan-type OptionDefinition array{separator: string|null, formatter: array<FormatterDefinition>|null, 
 *     'local-context': array<LocalContextDefinition>|null, aliases: array<AliasesDefinition>|null, 
 *     'browser-compatibility': array<BrowserCompatibilityDefinition>|null, transaction: string|null, 
 *     authentication: AuthenticationDefinition|null, 'media-root-dir': string|null, smtp: SMTPDefinition|null, 
 *     slack: SlackDefinition|null, 'credit-including': string|null, theme: string|null, 'app-locale': string|null, 
 *     'app-currency': string|null, import: ImportDefinition|null, terms: TermsDefinition|null}
 * @phpstan-type RelationshipDefinition array{'foreign-key': string|null, 'join-field': string|null, operator: string|null, portal: bool|null}
 * @phpstan-type ForeignFieldAndValueDefinition array{'field': string, 'value': string|int|float|double|bool|null}
 * @phpstan-type QueryDefinition array{field: string|null, value: string|int|float|double|bool|null, operator: string|null}
 * @phpstan-type SortDefinition array{field: string|null, direction: string|null}
 * @phpstan-type DefaultValuesDefinition array{field: string|null, value: string|int|float|double|bool|null|null}
 * @phpstan-type ValidationDefinition array{field: string|null, rule: string|null, message: string|null, notify: string|null}
 * @phpstan-type ScriptDefinition array{'db-operation': string|null, situation: string|null, definition: string|null, parameter: string|null}
 * @phpstan-type GlobalDefinition array{'db-operation': string|null, field: string|null, value: string|int|float|double|bool|null|null}
 * @phpstan-type AuthorizationDefinition array{user: array<string>|null, group: array<string>|null, target: string|null, field: string|null, noset: bool|null}
 * @phpstan-type AuthenticationDataSourceDefinition array{'media-handling': bool|null, 
 *     all: AuthorizationDefinition|null, load: AuthorizationDefinition|null, read: AuthorizationDefinition|null, 
 *     update: AuthorizationDefinition|null, new: AuthorizationDefinition|null, create: AuthorizationDefinition|null, 
 *     delete: AuthorizationDefinition|null}
 * @phpstan-type FileUploadDefinition array{field: string|null, context: string|null, container: string|bool|null, 'file-name': string|null|null}
 * @phpstan-type ExpressionDefinition array{field: string|null, expression: string|null}
 * @phpstan-type ConfirmMessagesDefinition array{insert: string|null, delete: string|null, copy: string|null}
 * @phpstan-type ButtonNamesDefinition array{insert: string|null, delete: string|null, 'navi-detail': string|null, 'navi-back': string|null, copy: string|null}
 * @phpstan-type Record array<string|null, string|int|float|double|bool|null>
 * @phpstan-type RecordSet array<Record>
 * @phpstan-type ContextDefiniton array{name: string|null, table: string|null, view: string|null, count: string|null,
 *     source: string|null, portals: array<string>|null, records: int|null, maxrecords: int|null, paging: bool|null, key: string|null,
 *     sequence: string|null, relation: array<RelationshipDefinition>|null, query: array<QueryDefinition>|null,
 *     sort: array<SortDefinition>|null, 'default-values': array<DefaultValuesDefinition>|null,
 *     'repeat-control': string|null, 'navi-control': string|null, 'navi-title': string|null, 'sync-control': string|null,
 *     validation: array<ValidationDefinition>|null, 'post-repeater': string|null, 'post-enclosure': string|null,
 *     'post-query-stored': string|null, 'before-move-nextstep': string|null, 'just-move-thisstep': string|null,
 *     'just-leave-thisstep': string|null, script: array<ScriptDefinition>|null, global: array<GlobalDefinition>|null,
 *     authentication: AuthenticationDataSourceDefinition|null, 'extending-class': string|null,
 *     'numeric-fields': array<string>|null, 'time-fields': array<string>|null, 'protect-writing': array<string>|null,
 *     'protect-reading': array<string>|null, 'db-class': string|null, dsn: string|null, option: string|null,
 *     database: string|null, user: string|null, password: string|null, server: string|null, port: string|null,
 *     protocol: string|null, datatype: string|null, 'cert-verifying': bool|null, cache: bool|null,
 *     'post-reconstruct': bool|null, 'post-dismiss-message': string|null, 'post-move-url': string|null,
 *     'soft-delete': bool|string|null, 'aggregation-select': string|null, 'aggregation-from': string|null,
 *     'aggregation-group-by': string|null, data: RecordSet|null, 'appending-data': RecordSet|null,
 *     'no-default-values-on-copy': bool|null, 'file-upload': array<FileUploadDefinition>|null,
 *     calculation: array<ExpressionDefinition>|null, 'button-names': ButtonNamesDefinition|null,
 *     'confirm-messages': ConfirmMessagesDefinition|null, 'ignoring-field': array<string>|null,
 *     import: ImportDefinition|null, terms: TermsDefinition|null}
 */
class TypesPHPStan
{
}
