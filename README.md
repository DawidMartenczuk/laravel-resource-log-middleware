# laravel resources log middleware

This middleware allows you to register resources usage in 10 minute intervals in your laravel application. All your resources logs are stored in JSON file in storage path, called _resource_logs.json_.

## Installation

1. Put middleware in your App\Http\Middleware folder.
2. Add alias for middleware in \App\Http\Kernel.php file, in array:
```php
protected $routeMiddleware = [
```
by adding this line:
```php
'resources' => \App\Http\Middleware\LogResources::class
```
3. Add middleware to using by your application routes, I prefer to add it by group:

```php
Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['resources'] 
], function() {
  /* Your routes go here*/
});
```
## Additional utilities

If you want to clear your resources logs of outdated entries, you can schedule a task in your laravel application, to do it. For example I did it that way:
```php
$schedule->call(function () {
  /**
   * Open file with your logs, if don`t exists, end task
   */
  $file = storage_path('resource_logs.json');
  if(!file_exists($file)) {
    return true;
  }

  /**
   *  $x variable store a Carbon object with minimal date time of your logs entries, that can be ignored while deleting a outdated entries
   */
  $x = \Carbon\Carbon::now()->subHours(12);
  $changes = false;
  
  /**
   * Search for outdated entries in your log
   */
  $resources = json_decode(file_get_contents($file));
  foreach($resources as $k=>$r) {
    if(\Carbon\Carbon::parse($k.'0')->lessThan($x)) {
      unset($resources[$k]);
      $changes = true;
    }
  }

  /**
   * If file not changed, don`t write it - end task
   */
  if(!$changes) {
    return true;
  }

  /**
   * Write file with your purified logs
   */
  $f = fopen($file, 'w+');
  fwrite($f, json_encode($resources));
  fclose($f);
  return true;
})->hourly();
```

### Used skills

* PHP7
* Laravel framework
* Using MVC Architecture essentials (middleware)
