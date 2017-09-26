# Instagram-PHP-API
At first I apologize for my bad english. My mother tongue is PHP ;)

Usage
-----
```php
<?php
include_once dirname(__FILE__) . '/src/instagram.php';

// LOGIN (Fill in your own account data)
$instagram = new Instagram("username", "password");

//Get user info
$instagram->getuserdata("username"); //let empty to get info of recent user

//upload image
$instagram->uploadimg("path/to/image.jpg", "description"); //file MUST be jpg!
?>
```
