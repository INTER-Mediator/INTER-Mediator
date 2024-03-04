# INTER-Mediator Unit Test with phpunit

The code with PHP can be tested by these test codes. They are besed on the phpunit.
Basically test suites are defined in .xml files here.

```
./vendor/bin/phpunit --bootstrap ./vendor/autoload.php \
  --configuration ./spec/INTER-Mediator-UnitTest/phpunit.xml
```

```
./vendor/bin/phpunit --bootstrap ./vendor/autoload.php \
  --configuration ./spec/INTER-Mediator-UnitTest/phpunit-fms.xml --process-isolation
```