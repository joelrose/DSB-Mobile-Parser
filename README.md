# DSB-Mobile-Parser
Unofficial DSBmobile API written in PHP, which uses http://simplehtmldom.sourceforge.net/ to parse the DSB-Plans
## Usage:
```php
<?php

$dsbmobile = new DSB(*Your Username*, *Your Password*);

$plan = $dsbmobile->getJson($index);

if($plan == false)

...

?>
```

### DSB Class

  * DSB constructor.
  * @param $username
  * @param $password
  
```php
public function __construct($username, $password)
```

  * @return a Json object which contains information about your DSB
  
```php
public function getData()
```

  * @param if your DSB has multiple plans you should pass the number of the Table you want, otherwise its 0
  * @return false if the function fails otherwise it returns a Json object which contains your DSB plan
  
```php
public function getJson($index)
```


