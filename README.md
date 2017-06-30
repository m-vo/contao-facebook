contao-facebook
===============
Facebook integration for Contao 4. This bundle supports the following things
that can be enabled independently:
 
##### Facebook Posts and Events
 Facebook posts and events get imported automatically (polling). The backend
 features a section where single posts/events can be disabled. 
  
 There are two Content Elements to display a number of posts/events in the
 frontend. High resolution images get downloaded into the folder
 ``files/facebook``. Make sure to make this folder public if you use the
 default templates so that image that do not get treated by the asset
 management will get displayed.
 
 Note that the largest size of images that will be downloaded will be smaller
 or equal the maximum size set in the Contao settings. 

##### OpenGraph Tags
 OpenGraph tags can be enabled foreach page root in the backend. If enabled
  ``<og>`` tags are generated in the head section that among others will output
  the page title, page description if set or one ore more images. Images can be
  selected for each page and apply for all child pages that have no images set.
    
    
    
    
Installation
------------

#### Step 1: Download the Bundle  

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require mvo/contao-facebook
```

#### Step 2: Enable the Bundle

Skip this point if you are using the managed edition of Contao.

Enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new \Mvo\ContaoFacebook\MvoContaoFacebookBundle(),
        );

        // ...
    }

    // ...
}
```
 
#### Step 3: Configure the Bundle

To setup the import of posts and events create or update your
``app/config/config.yml`` with the following content:

```yml
mvo_contao_facebook:
  import_enabled: true
  app_id: <your_facebook_app_id>
  app_secret: <your_facebook_app_secret>
  access_token: <your_access_token>
  fb_page_name: <your_facebook_page_name_or_id> 
```
You have to create a facebook app for the integration to work. Copy the app's app
id and app secret into the config. To create an access token open up facebook's 
[Graph API Explorer](https://developers.facebook.com/tools/explorer/) select your
app in the app drop down menu and then 'Request app access token' from the
drop down menu below.


You might as well overwrite the maximum number of posts that will get imported
as well as the minimum cache time in seconds to limit the amount of API calls.
 The following values are the defaults:
```yml
  number_of_posts: 15
  minimum_cache_time: 250
```


#### Step 4: Make sure the contao cron job is set up
The import system gets triggered by the internal 'minutely cron job'. Disable
the periodic command scheduler to make sure the import only gets triggered by a 
real cron job with the ``_contao/cron`` route and not during regular site
visits.

To force import the data use the option in the backend.  




