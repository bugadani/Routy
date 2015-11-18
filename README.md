Routy
=============

Routy is a request routing library for PHP. It is designed to be simple to use.

Basic usage
-----------

The `Router` class is the central element of the library that provides a simple API for most of the simple use-cases.
Defining a route is as easy as calling the method with the same name as the route's HTTP method and passing the path as a parameter.

    $router = new Routy\Router();
    $router->get('this/path');

Available methods are:

 * `get($path)`
 * `post($path)`
 * `put($path)`
 * `delete($path)`
 * `head($path)`

You can also define parameters in routes by wrapping a name in curly braces (e.g. `{parameterName}`).
A parameter consists a name and a pattern. By default, a parameter matches anything that is not a forward slash character (`/`),
but this can be overridden in the parameter definition by adding the desired pattern after the name, separated by a colon (`;`). The parameter definition follows the syntax of PHP's built-in PREG engine.
Example route that matches words as parameter: `hello/{name:\w+}`.

*Note*: Routes are identified by their parameters' patterns, not their names. Because of this, two routes may be similar in structure if they have different parameter patterns.

Routing the request is done with `match(Request $request)` or `matchCurrentRequest`. This will either throw an exception or return a `Match` instance that holds information about the matched route.

Using `add`, a route can be defined for multiple HTTP methods at once. To do this, pass the desired method names separated by a pipe ('|') character as the first argument.

    $router->add('GET|POST', 'some/path');

### Options

The above methods return an instance of `Routy\Initializers\RouteInitializerInterface` which can be used to set names
to the routes, specify a callback that will be called if a route is matched or to set extra information.

A more full route definition looks like this:

    $router->get('some/path')
           ->name('demoRoute')
           ->onMatch(function($name) { echo 'Hello ' . $name; })
           ->extras(['name' => 'World']);

 * `setName($name)`
 * `onMatch(callback)`: Because using the `Match` object that is returned by `match` can be cumbersome, a callback function can be supplied for each route that will be called when the route is matched.
 * `extras(array $extras)`: Sets extra information to the route in the form of a key-value pair.

*Note*: adding routes for multiple methods create multiple separate routes. Because one route name can not belong to multiple routes,
when using add to define multiple routes, setting the name using the returned Initializer will throw an exception.

### Route callbacks

Using `onMatch` a callback can be defined for the routes. This is the easiest way to decide, which route was matched and what action should be taken.
By default, the callback will receive the matched parameters (and extras set on the route) in the parameter list. Parameters are passed by their names and are actually optional.

    $callback = function($name = 'World') {echo 'Hello, ' . $name . '!';};
    $router->get('hello)')->onMatch($callback);
    $router->get('hello/me)')->extras('name' => 'Daniel')->onMatch($callback);
    $router->get('hello/{name)')->onMatch($callback);

The mapping of the parameters are done by an instance of `Routy\Invokers\DelegateInterface` and the default
behaviour can be overridden by supplying a subclass of this interface to `onMatch` that wraps the callback function.

### Generating URL-s

Using named routes, Routy can also generate URLs via the `to($routeName, array $parameters)` method. All of the route's parameters
must be supplied as the second argument, and extra ones will be appended to the URL as further GET parameters.

    $router->get('hello/{name}')->name('hello');
    echo $router->to('hello', ['name' => 'World', 'extra' => 'hi']); //prints "hello/World?extra=hi"

### Resources

Routy supports [Rails-like](http://guides.rubyonrails.org/routing.html#resource-routing-the-rails-default) resources.
A resource can be defined by using `resource($name)`, or `resources($singularName, $pluralName)`.