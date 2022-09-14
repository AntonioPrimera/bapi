# BAPI - Business API

## Scope

The scope of a BAPI is to encapsulate the business logic and to create reliable,
self-contained pieces of business functionality.

The difference between application logic and business logic is that while the application
logic deals with how the application works and interacts with the user (Controllers, Actions,
Routes, Front-End, UI, UX etc.), the business logic makes sure that the workflows specific
to the application are run in a reliable way.

## Why BAPIs?

If you have ever developed an application, you know that the logic tends to get more complicated
with each functionality you add and with each user requirement.

In order to be able to rely on the previously written bits of business logic whenever using
them in a more complex workflow (a list of steps), you should split them into atomic, reusable
business steps and make sure they are thoroughly tested. Each such step can be implemented in
a dedicated BAPI.

For example, in the case of a blog, a more complex flow would be to create a new blog post with
some media files, which have to be uploaded and checked by an admin.
During this flow, you would:
1. create the actual post (BAPI: CreateBlogPostBapi)
2. upload and link the media files (BAPI: UploadAndLinkPostMediaBapi)
3. check the post for flags like foul language (BAPI: CheckPostContentsBapi)
4. create a notification for an admin to check this blog post (BAPI: NotifyAdminBapi)
5. create notifications for all users who follow the thread (BAPI: NotifyThreadFollowersBapi)

Of course this is just to exemplify how you could split your business logic into BAPIs. You can
also implement such a functionality in other ways, with or without BAPIs.

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

This will create a new basic bapi class, in the **app/Bapis** folder of your Laravel app. If you 
wish to create a complex bapi class, with all the hooks and methods, you should use the `--full`
flag on the command above. If you want to always create complex bapi classes for your project,
without always using the `--full` flag, you can add the following setting to your .env file:

```dotenv
BAPI_GENERATOR_COMPLEX_BAPIS=true
```

By default, the base Bapi class, inherited by the generated Bapis is `AntonioPrimera\Bapi\Bapi`.
If you have another base class in your project, you can add it to your .env file like so:

```dotenv
BAPI_GENERATOR_BASE_CLASS="App\\Bapis\\Bapi"
```

The main business logic should go into the ***handle()*** method. Business validations should go
into the ***validate()*** method and authorizations should go into the ***authorize()*** method.
There are plenty of other hooks in the run lifecycle of the Bapi, where you can write your
business logic. I encourage you to use these hooks if necessary, rather than creating a huge
***handle()*** method.

### Implementing your Bapi & the Bapi run lifecycle

Whenever you instantiate the bapi, the ***setup()*** method is called, if implemented. By
default, the setup method is not implemented. If you call the run method statically (check
the chapter about running your bapi), an instance is created in the background, so the
setup method will always be called, if implemented.

When you run your bapi, the following methods will be called, in exactly this order:

1. validate()
2. prepareData()
3. beforeAuthorization()
4. authorize()
5. afterAuthorization()
6. beforeHandle()
7. handle(...)
8. afterHandle(mixed $handleResult)

In the end, the result of the ***afterHandle()*** method will be returned. The afterHandle
method is implemented in the Bapi and by default, it just returns the result of the handle
method. If you need, you can override the ***afterHandle()*** method and change the result
of the Bapi, before it is returned.

The arguments given to the bapi run method will be available throughout this entire lifecycle,
directly on the Bapi instance, using the argument name from the handle method.

For example, if you want to call an UpdatePostBapi like this...

```php
    UpdatePostBapi::run(Post $post, $title, $contents)
```

...you will have to implement this method signature as your ***handle()*** method, like this...

```php
    protected function handle(Post $post, $title, $contents)
```

...then, when running your bapi, via the ***run()*** method, the three arguments provided
will be available as instance attributes inside all the Bapi methods, and you could do
something like this in any of the methods...

```php
    return
        $this->post->title === $this->title
        && $this->post->contents === $this->contents;
```

### Running your Bapi

You can call your bapi using the ***run()*** method, either statically or as an instance method
after instancing your bapi. The run method doesn't exist in the Bapi and you should not
create a run method. This call is intercepted by the corresponding magic method and the bapi
run lifecycle is started. DO NOT CREATE a ***run()*** method in your bapi. The main business
logic should go into the ***handle()*** method.

You can also invoke the Bapi if you prefer.

For example, if you have the UpdatePostBapi in the example above, you can call it in any of
the following ways.

```php
    //static method call
    UpdatePostBapi::run($post, 'New title', 'Some contents');
```

```php
    //instance method call
    $updatePostBapi = new UpdatePostBapi();
    $updatePostBapi->run($post, 'New title', 'Some contents');
```

```php
    //invoke
    $updatePostBapi = new UpdatePostBapi();
    $updatePostBapi($post, 'New title', 'Some contents');
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
        ->run($post, 'New title', 'Some contents');
```

```php
    //instance method call
    $updatePostBapi = new UpdatePostBapi();
    $updatePostBapi->withoutAuthorizationCheck()
        ->run($post, 'New title', 'Some contents');
```

While this is possible, it is very risky, because Bapis should be atomic bits and pieces
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

### Validating the business data

While the controllers are responsible to validate user input data, these validations are
usually not enough for complex business processes. Business validations are usually more
complex and should be implemented together with the business logic, inside the Bapi, in the
**validate** method.

If there is any issue in the validation process, you can just return false from the
***validate()*** method, in which case, a simple BapiValidationException will be thrown.

If there are one or more validation issues, and you want to send a proper response, informing
the user about the issues, you can use **BapiValidationIssue** instances (described below).
You can return a **BapiValidationIssue** instance, an array or collection of
**BapiValidationInstances** from the ***validate()*** method, in which case, the Bapi
will throw a new **BapiValidationException**, containing the issues returned by the
***validate()*** method.

If you want to take matters into your own hands, you can generate the **BapiValidationIssue**
instances and throw a **BapiValidationException** containing these issues, from within the
***validate()*** method.

#### BapiValidationIssue and the BapiValidationException

Whenever a Bapi validation issue occurs, you should generate a BapiValidationIssue instance,
which you can pass on to the thrown BapiValidationException.

Each BapiValidationIssue must contain the name of the attribute that generated the issue, its
value and the issue that occurred, as an issue code (e.g. "AGE-LT-18") as free text message (
e.g. "User is not of legal age!") or as a translation key (e.g. "exceptions.age.notLegal").

```php
    $bapiValidationIssue = new \AntonioPrimera\Bapi\Components\BapiValidationIssue(
        'companyName',          //the name of the attribute at fault
        'Amazon UK',            //the value of the attribute
        'not-unique',           //the issue that occurred
        'C:N:NU'                //optionally, an issue code
    );
```

After generating one or more bapi validation issues, you can throw a BapiValidationException
containing all these issues. The exception constructor can receive a single BapiValidationIssue
or a list (array, Collection etc.) of BapiValidationIssues.

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

### Authentication & Actors

The BAPI uses an underlying `AntonioPrimera\Bapi\Actor` class to handle the authenticated actor.
This class is a wrapper for the authenticated user and implements the Authenticatable interface. 
It forwards all attribute handling and method calls to the underlying model (the authenticated user).

It implements two useful methods: `isAuthenticated()` and `isGuest()`.

The Bapi instance offers the public `actor()` method, which will lazily create an Actor instance,
wrapping the authenticated user, so you can do something like this in your Bapi authorization
method:

```php
protected function authorize()
{
    return $this->actor()->isAuthenticated()
        && $this->can('some-action', $someModel);
}
```

The Actor instance is sprinkled with some syntactic sugar:

- you can retrieve the underlying model via `$actor->getModel()` method, as an attribute
`$actor->model` or `$actor->user`
- you can check whether there is an authenticated actor via `$actor->isGuest()` and
`$actor->isAuthenticated()` methods, or via attributes with the same names `$actor->isGuest`
and `$actor->isAuthenticated`

## Known issues / quirks

### 1. Arguments given to the constructor

When you instantiate a bapi, all arguments of the handle method are made available as
instance attributes and their values are either null or their default values from the
method signature.

During the constructor and the **setup()** method, called in the constructor, these values are
available (because the bapi was not yet called with the actual data).

**The issue:**

If you provide any arguments to the constructor, these will be assigned to the
instance attributes corresponding to the **handle(...)** method (based on their order),
without doing any type checks.

**Solutions / Workarounds:**

1. Do not provide any arguments to the constructor
2. OR override the constructor and give it the same signature as the handle method
3. OR do not use these attributes in the **setup()** method if you have one