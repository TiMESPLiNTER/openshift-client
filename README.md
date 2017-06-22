# Simple OpenShift PHP Client

Provides a simple standalone client and a wrapper for the rather verbose [universityofadelaide/openshift-restclient-php](https://github.com/universityofadelaide/openshift-restclient-php) generated by [Swagger codegen](https://github.com/swagger-api/swagger-codegen).

## Getting started

Require the client using composer:

```
composer require universityofadelaide/openshift-client dev-master
```

## Usage

Create an instance of the `Client` class by providing an OpenShift API URL and authentication token:

```php
use UniversityOfAdelaide\OpenShift\Client;

...

$client = new Client('https://192.168.64.2:8443/api/v1/', 'big_secret_token_hash', 'project');
```

## How to Test

Create a `test.php` or similar file in the root of the project. 

Add the following to this file:

```php
require_once __DIR__ . './../../autoload.php';

use UniversityOfAdelaide\OpenShift\Client;

$host = 'https://pathToOpenshift.host';
$token = 'yourOpenShiftToken';
$namespace = 'project';

// Get the arguments required
$client = new Client($host, $token, $namespace, TRUE);

// Attempt to create a secret.
$response = $client->createSecret('superSecret', ['username' => 'pied_piper', 'pass', 'middleout']);

```

## How to test with phpunit and minishift

Ensure that you have the oc command available.

The token will expire every 24 hours, if the token is expired you will need to login again.
```bash
oc login -u developer -p developer
```

```bash
# Assumes myproject (default) is available.
# From the /vendor/universityofadelaide/openshift-client directory
../../bin/phpunit tests/ClientTest.php $(minishift console --url) $(oc whoami -t) myproject client_test.json
```

## Actual deployment

Manually create a mysql container in openshift, make note of the db name, username and password, and put those into the client_test.json envVars section.

### Test clean up scripts 

Remove all objects created during tests : 
```bash
# Assuming all the names for items created contain 'pied'
name=pied; for type in dc bc is svc pvc route pods job cronjob secrets; do for item in $(oc get "${type}" | grep ${name} | awk '{ print $1 }'); do oc delete ${type} ${item}; done; done
```

## Todo

- Complete implementation of interface.
- Improve test coverage, test the response json object rather that just the status code.  
