# INTER-Mediator spec Directory

## THe INTER-Mediator-UnitTest Directory

All unit tests of PHP code in this directory work with PHPUnit.
These tests start with the command "composer test."

## The jest-test-suite Directory

All unit tests of JavaScript code in this directory work with jest.
These tests start with the command "composer jest."

## The Manual-Tests Directory

This is the record of testing with manually.
The SAML based authentication feature has to test manually,
and occasionally tests are going to proceed.

## The run Directory

It contains the end-to-end test suite with WebdriverIO.
GitHub Action php.yml executes this.

## The composer7/package7 Files

These are seed files for correspondent ones. The "7" means for PHP 7.
Previously these have to prepare for both PHP 5 and 7, but PHP 5 is out of supporting
envrioment, The files contains "7" in that name is still remained.