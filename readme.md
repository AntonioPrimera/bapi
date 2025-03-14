# BAPI - Business API

BAPI stands for Business API and is like an Action on steroids:

- It encapsulates the **business logic** of your application and makes it reusable and testable.
- It ensures data consistency, by using **database transactions**.
- It allows you to do **authorization checks** before actually running the business logic.
- It allows you to do **business validations** before actually running the business logic.
- It encapsulates **best practices** for handling the business logic of your application.
- BAPIs are **self-contained** and **reusable** bits of business logic.

Just to clarify:

- business logic is the logic specific to your application. For example, if you
create an application where you handle car inventory, the business logic would be the logic
specific to cars, inventory, reporting on inventory, car manufacturers etc.
- business validation is different from user input validation. User input validation is
making sure that the user input is valid (e.g. the email is a valid email). Business
validation is making sure that the data makes sense in the context of the problem you are
trying to solve (e.g. the car parts are compatible with the car model).

For example a BAPI for adding a car part to the inventory would be called like this:

```php
AddCarPartToInventoryBapi::run(
    partCategory: $category,
    partType: $type,
    partManufacturer: $manufacturer,
    carMake: $make,
    carModel: $model,
    carYear: $year,
    storage: $storageToBeAddedTo,
);
```

The BAPIs are not meant to replace the controllers, but to be used by the controllers and other
methods handling the business logic of your application (e.g. Livewire forms, Jobs, Commands etc.).

## Why BAPIs?

If you have ever developed an application, you know that the logic tends to get more complicated
with each functionality you add and with each user requirement.

In order to be able to rely on the previously written bits of business logic whenever using
them in a more complex workflow (a list of steps), you should split them into atomic, reusable
business steps and make sure they are thoroughly tested. Each such step can be implemented in
a dedicated BAPI.

## Usage

### Installation

Import the bapi package through composer:

```bash
  composer require antonioprimera/bapi
```

### Creating a new Bapi

An artisan command to create a new Bapi will be available after installing the package.

For example, you can run the following artisan command in your console in order to create
a new Bapi in **app/Bapis/Posts/CreatePostBapi.php**:

```bash
php artisan make:bapi Posts/CreatePostBapi
```

### Advanced Bapi generation

#### Always create complex Bapis
This will create a new basic bapi class, in the **app/Bapis** folder of your Laravel app. If you 
wish to create a slightly more complex bapi class, with all the hooks and methods, you should use
the `--full` flag on the command above.

If you want to always create complex bapi classes for your project, without always using the 
`--full` flag every time, you can add the following setting to your .env file:

```dotenv
BAPI_GENERATOR_COMPLEX_BAPIS=true
```

#### Custom base class to be inherited by the generated Bapis

By default, the base Bapi class, inherited by the generated Bapis is `AntonioPrimera\Bapi\Bapi`.
If you have another base class in your project, you can add it to your .env file like so:

```dotenv
BAPI_GENERATOR_BASE_CLASS="App\\Bapis\\Bapi"
```

#### TDD: create a test file for your BAPI

You have several options to let the `make:bapi` command create a unit test for your new bapi:

- If you just want a simple test created, add the `--t` option to your command. The following
example will create the test file `test/Unit/Bapis/Posts/CreatePostBapiTest.php`:

```bash
php artisan make:bapi Posts/CreatePostBapi --t
```

- If you want to take control over the path and name of your unit test, you can add the
`--test TestPath/AndName` option your command. The following example will create the test file
`test/Unit/Posts/CreatePostBasicTest.php`.

```bash
php artisan make:bapi Posts/CreatePostBapi --test Posts/CreatePostBapiBasicTest
```

- If you always want to create a simple, default test for all your bapis, you can add the following
entry in your `.env` file, which will act like adding `--t` to all your `make:bapi` commands:

```dotenv
BAPI_GENERATOR_TDD=true
```

### Implementing your Bapi & the Bapi run lifecycle

Whenever you instantiate the bapi, the ***setup()*** method is called, if implemented. By
default, the setup method is not implemented. If you call the run method statically (check
the chapter about running your bapi), an instance is created in the background, so the
setup method will always be called, if implemented.

When you run your bapi, the following methods will be called, in exactly this order:

1. authorize()
2. validate()
3. handle(...)
4. processResult(mixed $result): mixed

The arguments provided when calling the BAPI **run** method **must be named arguments** and will be
available throughout the entire lifecycle, directly on the Bapi instance, using the argument
name from the handle method.

For example, if you want to call an UpdatePostBapi like this...

```php
    UpdatePostBapi::run(post: $post, title: $title, contents: $contents)
```

...your ***handle()*** method, must look something like this...

```php
    protected function handle(Post $post, $title, $contents)
```

...then, when running your bapi, via the ***run()*** method, the arguments will be available
as instance attributes inside all the Bapi methods, so you can use them like this...

```php
    return
        $this->post->title === $this->title
        && $this->post->contents === $this->contents;
```

In the end, the result of the ***handle()*** method will be provided as an argument to the
***processResult()*** method, which allows you to do any transformations and post-processing
of the result. The return value of the ***processResult()*** method will be returned by the
BAPI ***run(...)*** method. If you need, you can override the ***processResult()*** method
and change the result of the Bapi, before it is returned.

### Running your Bapi

You can call your bapi using the ***run()*** method, either statically or as an instance method
after instancing your bapi. The run method doesn't exist in the Bapi and you should not
create a run method. This call is intercepted by the corresponding magic method and the bapi
run lifecycle is started. **DO NOT CREATE a run() method** in your bapi. The main business
logic should go into the ***handle()*** method.

You can also invoke the Bapi if you prefer.

For example, if you have the UpdatePostBapi in the example above, you can call it in any of
the following ways.

```php
    //static method call
    UpdatePostBapi::run(post: $post, title: 'New title', body: 'Some contents');
```

```php
    //instance method call
    $updatePostBapi = new UpdatePostBapi();
    $updatePostBapi->run(post: $post, title: 'New title', body: 'Some contents');
```

```php
    //invoke
    $updatePostBapi = new UpdatePostBapi();
    $updatePostBapi(post: $post, title: 'New title', body: 'Some contents');
```

### Skipping the authorization check

Sometimes, when you have a more complex scenario, where a bapi calls other Bapis as part of
the business logic, you might want to do all necessary authorization checks in the complex
Bapi and run the other Bapis inside, without an authorization check (it might be just a
redundant check).

If you want to skip the authorization check, you can call the ***withoutAuthorizationCheck()***
method either statically or as an instance method.

For example, if you want to call the bapi in the previous example without running the
authorization check, you could do the following:

```php
    //static method call
    UpdatePostBapi::withoutAuthorizationCheck()
        ->run(post: $post, title: 'New title', body: 'Some contents');
```

```php
    //instance method call
    $updatePostBapi = new UpdatePostBapi();
    $updatePostBapi->withoutAuthorizationCheck();
    $updatePostBapi->run(post: $post, title: 'New title', body: 'Some contents');
```

While this is possible, it is risky, because Bapis should be atomic bits and pieces
of code and should be completely independent. Thus, if a bapi calls other Bapis, which in
turn call other Bapis and so on, it will be hard to ensure that every bapi covers all
necessary authorization checks. This also rises the risk for duplicated authorization logic.
There is no universal rule regarding the structure and authorization of your Bapis, so
just use common sense and make sure to test your Bapis thoroughly, otherwise you miss the
main benefit of the Bapis and might be better off using single file actions or just plain
php classes, because these are easier to implement and understand, and they contain less
magic.

Although you might never use it, a ***withAuthorizationCheck()*** method is available and can
be called to re-enable the authorization check if it was disabled previously for a Bapi
instance.

### Skipping DB transactions

If you want to run a BAPI without a database transaction, you can call the ***withoutDbTransaction()***
method either statically or as an instance method.

For example, if you want to call the bapi in the previous example without running the
database transaction, you could do the following:

```php
    //static method call
    UpdatePostBapi::withoutDbTransaction()
        ->run(post: $post, title: 'New title', body: 'Some contents');
```

```php
    //instance method call
    $updatePostBapi = new UpdatePostBapi();
    $updatePostBapi->withoutDbTransaction();
    $updatePostBapi->run(post: $post, title: 'New title', body: 'Some contents');
```

While this is possible and necessary in some cases, it is risky, so you should use it with
caution.

If you want to completely disable the database transaction for a Bapi, you can set the
***$useDbTransaction*** property to false in the Bapi class, overriding the default value.

If you want to completely disable the database transaction for all Bapis, you can create a
new base class for your Bapis and set the ***$useDbTransaction*** property to false in that
base class. Then, you can set the ***BAPI_GENERATOR_BASE_CLASS*** environment variable to
point to your new base class, so that all Bapis will inherit from it.

### Validating the business data

While the controllers are responsible to validate user input data, these validations are
usually not enough for complex business processes. Business validations are usually more
complex and should be implemented together with the business logic, inside the Bapi, in the
**validate** method.

If the validation passes, the ***validate()*** method must return boolean true. Any other
return value, will be wrapped in a BapiValidationException, which will be thrown.

You can also throw a BapiValidationException directly from the ***validate()*** method.

#### BapiValidationIssue and the BapiValidationException

Whenever a Bapi validation issue occurs, you should generate a BapiValidationIssue instance,
which you can pass on to the thrown BapiValidationException.

Each BapiValidationIssue must contain the name of the attribute that generated the issue, its
value and the issue that occurred, as an issue code (e.g. "AGE-LT-18") as free text message (
e.g. "User is not of legal age!") or as a translation key (e.g. "exceptions.age.notLegal").

```php
    $bapiValidationIssue = new \AntonioPrimera\Bapi\Components\BapiValidationIssue(
        attributeName: 'companyName',      //the name of the attribute at fault
        attributeValue: 'Amazon UK',       //the value of the attribute
        errorMessage: 'not-unique',        //the issue that occurred
        errorCode: 'C:N:NU'                //optionally, an issue code
    );
```

After generating one or more bapi validation issues, you can either throw a
new BapiValidationException with these issues, or you can return an array of
BapiValidationIssue instances from the ***validate()*** method.

```php
    protected function validate()
    {
        $issues = [];
        
        //business validation - whether the company name is unique in the EU
        if ($this->comapnyNameIsNotUnique($this->company->name))
            $issues[] = new \AntonioPrimera\Bapi\Components\BapiValidationIssue(
                'companyName',
                $this->company->name,
                'not-unique',
                'C:N:NU'
            );
            
        //business validation - whether the country is registered in the EU
        if ($this->companyCountryNotValid($this->company->country))
            $issues[] = new \AntonioPrimera\Bapi\Components\BapiValidationIssue(
                'companyCountry',
                $this->company->country,
                'non-EU',
                'C:C:NEU'
            );
        
        //if any issues were found, throw a new BapiValidationException with these issues
        if ($issues)
            throw new \AntonioPrimera\Bapi\Exceptions\BapiValidationException($issues);
    }
```

By using these Bapi Validation Issues and the BapiValidationException, you can render a proper
response in the ***\App\Exceptions\Handler::register()*** method of your application.

Another way to render a universal response for your business validation exceptions is to
create a subclass of the BapiValidationException and implement the ***render()*** method. For
this, you can check the **Laravel documentation** on **Error Handling**

### Validating attributes

If you want to validate attributes and throw a ValidationException, like the form validation
does, you can add the ***ValidatesAttributes*** trait to your Bapi. This trait overrides the
default exception hadling mechanism in Bapis and transforms BapiValidationExceptions containing
BapiValidationIssues into ValidationExceptions, which are handled by the default Laravel
exception handler.

Concretely, this means that if you add the ***ValidatesAttributes*** trait to your Bapi and
return a BapiValidationIssue or an array of BapiValidationIssues from the ***validate()***
method, a ValidationException will be thrown, which will add the validation issues to the
***$errors*** variable, which is available in the views.

For example, if you would want to validate the company name, you could do something like this...

```php
    use \AntonioPrimera\Bapi\Traits\ValidatesAttributes;
    
    protected function validate()
    {
        //business validation - whether the company name is unique
        if ($this->comapanyNameIsNotUnique($this->company))
            return new \AntonioPrimera\Bapi\Components\BapiValidationIssue(
                'companyName',
                $this->company->name,
                'Company name is not unique',
            );
            
        return true;
    }
```

... and then in your form, you would be able to display an error message for the company name,
like this:

```html
    @error('companyName')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
```

### Authentication & Actors

The Bapi instance offers the public `actor()` method, which is just a wrapper for the
`Auth::user()` method. 

```php
protected function authorize()
{
    return $this->actor() 
        && $this->can('some-action', $someModel);
}
```

## Calling a normal BAPI internally (from another BAPI)

Some Bapis should be called in controllers, maybe even blade views (e.g. when using Laravel Livewire),
and these Bapis should always have an authorization check and should usually run inside a DB Transaction.

Other Bapis can also be called internally, from inside another bapi, which already started a DB transaction
and handled the authorization check. When called from another bapi, a bapi should not run a DB transaction
and should not do any other authorization checks by default (unless specifically requested to).

In order to use a Bapi internally, you should remove the authorization check or have it return false (so
that when called from outside, it will fail) and always call it from another Bapi using the
`call(...)` method instead of `run(...)`.

For example, if you have a `CreatePostBapi` and you want to call a `CreatePostNotificationBapi` from
inside the `CreatePostBapi`, you should do something like this:

```php
    // Inside the CreatePostBapi
    protected function handle(Post $post, $title, $contents)
    {
        $post = Post::create([
            'title' => $title,
            'contents' => $contents
        ]);

        //the "call" method is used, instead of the "run" method
        CreatePostNotificationBapi::call(post: $post);
        
        return $post;
    }
```

If you want to call a bapi internally, but force the authorization check to run, you can use the
`callWithAuthorizationCheck(...)` method.

```php
    // Inside a removeTopicBapi
    protected function handle(Topic $topic): void
    {
        //remove all posts from the topic, checking remove authorization for each post
        foreach ($topic->posts as $post)
            DeletePostBapi::callWithAuthorizationCheck(post: $post);
            
        $topic->delete();
    }
```

## Creating BAPIs which can only be called internally

In some cases, you want some BAPIs to only be called by other BAPIs and never from Controllers,
Jobs, Commands, Blade views or other parts of your application. In order to achieve this, you can
extend the `InternalBapi` class instead of the `Bapi` class.

These exclusively internal BAPIs will not run an authorization check, will not run within a DB Transaction
and do not handle any Exceptions. They still offer validation and handling the result, using the same
lifecycle as the normal BAPIs.

You can create an internal bapi using the same artisan command, with the "-I" or "--internal" flag.

```bash
php artisan make:bapi Posts/CreatePostBapi --internal
# or
php artisan make:bapi Posts/CreatePostBapi -I
```

### Testing internal Bapis

Because internal bapis can only be called internally, you need a helper in order to test them. You can use
the `AntonioPrimera\Bapi\TestInternalBapi` class to test your internal Bapis.

For example, if you have an internal bapi called `CreatePostBapi` (inheriting the InternalBapi class), you
can test it like this:

```php
use AntonioPrimera\Bapi\TestInternalBapi;

//in your test file
$bapiResult = TestInternalBapi::run(
    bapi: CreatePostBapi::class,
    title: 'New post',
    contents: 'Some contents'
);
```

Note that you must provide the full class name of the internal bapi as the first argument, followed by the
**named arguments** you want to pass to the bapi. This helper will not run with classes other than Internal Bapis
or outside the testing environment.