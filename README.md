# DSB-Mobile-Parser
Unofficial DSBmobile API written in PHP
## Usage:
```php
<?php

$dsbmobile = new DSB(*Your Username*, *Your Password*);

$dsbmobile->getData();

?>
```

### DSB Class
Constructs the Object and initializes the Username and Password
```php
public function __construct($username, $password)
```
@Return - Returns a JSON onject, which contains everything about your DSB
```php
public function getData()
```


