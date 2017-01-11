# TAHClient (Twilio Alternative HTTP)
---

A reference implementation of Twilio's [HttpClient Interface](https://github.com/twilio/twilio-php/blob/master/Twilio/Http/Client.php) that does not use cURL.

## Usage
---
When constructing an instance of `Twilio\Rest\Client`, specify an instance of `FileGetContentsClient` in the constructor for the `$httpClient` variable.

```php
use Twilio\Rest\Client;
use TAHClient\FileGetContentsClient;

$accountSid='ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
$authToken='your_auth_token';

$client=new Client($accountSid, $authToken, NULL, NULL, new FileGetContentsClient());

$client->account->messages->create('+15128675309', array(
    'from'=>'+15005550006',
    'body'=>'Hey Jenny, what's up?'));
```