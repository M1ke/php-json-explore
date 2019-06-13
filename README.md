# Data structure explorer
## For JSON or other APIs

Sometimes you get a response from an API or other data source which is structured data
but the structure may not be obvious, e.g. 10 objects and their data differs slightly.
Documentation may help but often can be incomplete or not even present.

This library is a utility to take a block of data that can translate into a PHP associative
array and give you a breakdown of the possible keys that exist and base types that their
values can take.

### Install it

```
composer require m1ke/json-explore
```

### See an example of it working

Run `php example/example.php`
