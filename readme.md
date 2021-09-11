#BAPI - Business API

##Scope

The scope of a BAPI is to encapsulate the business logic and to create reliable,
self-contained pieces of business functionality.

The difference between application logic and business logic is that while the application
logic deals with how the application works and interacts with the user (Controllers, Actions,
Routes, Front-End, UI, UX etc.), the business logic makes sure that the workflows specific
to the application are run in a reliable way.

##Why BAPIs?

If you have ever developer an application, you know that the logic tends to get more complicated
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
also implement such a functionality in other ways, with or witout BAPIs.

##Usage

###Installation

Import the bapi package through composer:

```bash
  composer require antonioprimera/bapi
```

###Creating a new Bapi

An artisan command to create a new Bapi will be available after installing the package.

For example, in order to create a new "CreatePostBapi", you can run the following artisan
command in your console:

```bash
    php artisan make:bapi Bapis/Posts/CreatePostBapi
```

This will create a new bapi class, with all the hooks and methods. You should delete everything
you don't need.

The main business logic should go into the "handle" method. Business validations should go
into the "validate" method and authorizations should go into the "authorize" method. There
are plenty of other hooks in the run lifecycle of the Bapi, where you can write your business
logic. I encourage you to use these hooks if necessary, rather than creating a huge "handle"
method.

###Implementing your Bapi & the Bapi run lifecycle

Whenever you instantiate the bapi, the "setup" method is called, if implemented. By default,
the setup method is not implemented. If you call the run method statically (check the chapter
about running your bapi), an instance is created in the background, so the setup method
will always be called, if implemented.

When you run your bapi, the following methods will be called, in exactly this order:

1. validateData()
2. prepareData()
3. beforeAuthorization()
4. authorize()
5. afterAuthorization()
6. beforeHandle()
7. handle(...)
8. afterHandle(mixed $handleResult)

In the end, the result of the "afterHandle" method will be returned. The afterHandle method
is implemented in the Bapi and by default, it just returns the result of the handle method.
If you need, you can override the "afterHandle" method and change the result of the Bapi,
before it is returned.

The arguments given to the bapi run method will be available throughout this entire lifecycle,
directly on the Bapi instance, using the argument name from the handle method.

For example, if you want to call an UpdatePostBapi like this...

```php
    UpdatePostBapi::run(Post $post, $title, $contents)
```

...you will have to implement this method signature as your **handle** method, like this...

```php
    protected function handle(Post $post, $title, $contents)
```

...then, when running your bapi, via the **run** method, the three arguments provided will be
available as instance attributes inside all the Bapi methods, and you could do something
like this in any of the methods...

```php
    return
        $this->post->title === $this->title
        && $this->post->contents === $this->contents;
```

###Running your Bapi

You can call your bapi using the **run** method, either statically or as an instance method
after instancing your bapi. The run method doesn't exist in the Bapi and you should not
create a run method. This call is intercepted by the corresponding magic method and the bapi
run lifecycle is started. DO NOT CREATE a **run** method in your bapi. The main business
logic should go into the **handle** method.

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

###Skipping the authorization check

Sometimes, when you have a more complex scenario, where a bapi calls other Bapis as part of
the business logic, you might want to do all necessary authorization checks in the complex
Bapi and run the other Bapis inside, without an authorization check (it might be just a
redundant check).

If you want to skip the authorization check, you can call the **withoutAuthorizationCheck()**
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

Although you might never use it, a **withAuthorizationCheck()** method is available and can
be called to re-enable the authorization check if it was disabled previously for a Bapi
instance.

##Known issues / quirks

###1. Arguments given to the constructor

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