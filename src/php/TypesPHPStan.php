<?php

namespace INTERMediator;

/**
 * This class serves as a central registry for PHPStan type alias definitions used across the
 * INTER-Mediator project. It contains no runtime logic or properties — its sole purpose is to
 * host `@phpstan-type` annotations that other classes can import via `@phpstan-import-type`.
 *
 * @phpstan-type DBSpec array{'db-class': string, dsn: string, option: array<string, mixed>, database: string, user: string, password: string, server: string, port: string, protocol: string, datatype: string, 'cert-verifying': bool}
 * @phpstan-type FormatterDefinition array{field: string, 'converter-class': string, parameter: string|boolean}
 * @phpstan-type LocalContextDefinition array{key: string, value: string|boolean|integer}
 * @phpstan-type AliasesDefinition array<string, string>
 * @phpstan-type BrowserCompatibilityDefinition array<string, string>
 * @phpstan-type AuthenticationDefinition array{user: array<string>, group: array<string>, 'user-table': string, 'group-table': string, 'corresponding-table': string, 'challenge-table': string, authexpired: string|integer, storing: string, realm: string, 'email-as-username': bool, 'issuedhash-dsn': string, 'password-policy': string, 'enroll-page': string, 'reset-page': string, 'is-saml': bool, 'saml-builtin-auth': bool, 'is-required-2FA': bool, 'digits-of-2FA-Code': int, 'mail-context-2FA': string, 'expiring-seconds-2FA': int, 'passkey-only-on-auth': bool, 'add-class-authn': bool, 'passkey-error-alerting': bool, 'method-2FA': string, 'is-pass-through-2FA': bool}
 * @phpstan-type SMTPDefinition array{protocol: string, server: string, port: int, encryption: string, username: string, password: string}
 * @phpstan-type SlackDefinition array{token: string, channel: string}
 * @phpstan-type ImportDefinition array{'1st-line': string, 'skip-lines': int, format: string, 'use-replace': bool, encoding: string, 'convert-number': array<string>, 'convert-date': array<string>, 'convert-datetime': array<string>}
 * @phpstan-type TermsDefinition array<string, mixed>
 * @phpstan-type MessagingDefinition array{from: string, to: string, cc: string, bcc: string, subject: string, body: string, 'from-constant': string, 'to-constant': string, 'cc-constant': string, 'bcc-constant': string, 'subject-constant': string, 'body-constant': string, 'body-template': string, 'body-fields': string, 'f-option': bool, 'body-wrap': int, store: string, attachment: string, 'template-context': string}
 * @phpstan-type OptionDefinition array{separator: string, formatter: array<FormatterDefinition>, 'local-context': array<LocalContextDefinition>, aliases: array<AliasesDefinition>, 'browser-compatibility': array<BrowserCompatibilityDefinition>, transaction: string, authentication: AuthenticationDefinition, 'media-root-dir': string, smtp: SMTPDefinition, slack: SlackDefinition, 'credit-including': string, theme: string, 'app-locale': string, 'app-currency': string, import: ImportDefinition, terms: TermsDefinition}
 * @phpstan-type RelationshipDefinition array{'foreign-key': string, 'join-field': string, operator: string, portal: bool}
 * @phpstan-type QueryDefinition array{field: string, value: string|int|float|double|bool|null, operator: string}
 * @phpstan-type SortDefinition array{field: string, direction: string}
 * @phpstan-type DefaultValuesDefinition array{field: string, value: string|int|float|double|bool|null}
 * @phpstan-type ValidationDefinition array{field: string, rule: string, message: string, notify: string}
 * @phpstan-type ScriptDefinition array{'db-operation': string, situation: string, definition: string, parameter: string}
 * @phpstan-type GlobalDefinition array{'db-operation': string, field: string, value: string|int|float|double|bool|null}
 * @phpstan-type AuthorizationDefinition array{user: array<string>, group: array<string>, target: string, field: string, noset: bool}
 * @phpstan-type AuthenticationDataSourceDefinition array{'media-handling': bool, all: AuthorizationDefinition, load: AuthorizationDefinition, read: AuthorizationDefinition, update: AuthorizationDefinition, new: AuthorizationDefinition, create: AuthorizationDefinition, delete: AuthorizationDefinition}
 * @phpstan-type FileUploadDefinition array{field: string, context: string, container: string, 'file-name': string}
 * @phpstan-type ExpressionDefinition array{field: string, expression: string}
 * @phpstan-type ConfirmMessagesDefinition array{insert: string, delete: string, copy: string}
 * @phpstan-type ButtonNamesDefinition array{insert: string, delete: string, 'navi-detail': string, 'navi-back': string, copy: string}
 * @phpstan-type Record array<string, string|int|float|double|bool|null>
 * @phpstan-type RecordSet array<Record>
 * @phpstan-type ContextDefiniton array{name: string, table: string, view: string, count: string, source: string, portals: array<string>, records: int, maxrecords: int, paging: bool, key: string, sequence: string, relation: array<RelationshipDefinition>, query: array<QueryDefinition>, sort: array<SortDefinition>, 'default-values': array<DefaultValuesDefinition>, 'repeat-control': string, 'navi-control': string, 'navi-title': string, 'sync-control': string, validation: array<ValidationDefinition>, 'post-repeater': string, 'post-enclosure': string, 'post-query-stored': string, 'before-move-nextstep': string, 'just-move-thisstep': string, 'just-leave-thisstep': string, script: array<ScriptDefinition>, global: array<GlobalDefinition>, authentication: AuthenticationDataSourceDefinition, 'extending-class': string, 'numeric-fields': array<string>, 'time-fields': array<string>, 'protect-writing': array<string>, 'protect-reading': array<string>, 'db-class': string, dsn: string, option: string, database: string, user: string, password: string, server: string, port: string, protocol: string, datatype: string, 'cert-verifying': bool, cache: bool, 'post-reconstruct': bool, 'post-dismiss-message': string, 'post-move-url': string, 'soft-delete': bool|string, 'aggregation-select': string, 'aggregation-from': string, 'aggregation-group-by': string, data: RecordSet, 'appending-data': RecordSet, 'no-default-values-on-copy': bool, 'file-upload': array<FileUploadDefinition>, calculation: array<ExpressionDefinition>, 'button-names': ButtonNamesDefinition, 'confirm-messages': ConfirmMessagesDefinition, 'ignoring-field': array<string>, import: ImportDefinition, terms: TermsDefinition}
 */
class TypesPHPStan
{

}
