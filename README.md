Webgriffe Config
================

Magento 1.x extension that improves config system.

Installation
------------

Please, use [Magento Composer Installer](https://github.com/magento-hackathon/magento-composer-installer) and add `webgriffe/config-extension` to your dependencies.

	$ composer require webgriffe/config-extension
	    
The extension patches the main Magento's config model by putting another `Mage_Core_Model_Config` class in the `community` code pool.

We're aware that creating this kind of "monkey patch" is a Magento's coding smell but this is the only way to override Magento config logic in a reliable way.

Config override
---------------

Magento configuration is driven by database. This, sometimes, is overkill and forces us to maintain upgrade script to keep Magento envorinment aligned with features development.
So, this extension enables additional config file that overrides database configuration. The file must be at path `app/etc/config-override.xml.dist`. For example:

```xml
<?xml version="1.0"?>
<config>
	<default>
    	<general>
        	<locale>
            	<code>en_US</code>
            </locale>
	    </general>
    </default>
    <stores>
    	<it_it>	    			
			<general>
        		<locale>
    	        	<code>it_IT</code>
	            </locale>
    	    </general>
    	</it_it>
    </stores>
</config>
```
	
The extension tries also to load the *non-dist* file at `app/etc/config-override.xml` which, if present, extends the `app/etc/config-override.xml.dist`. In this way you can put the `app/etc/config-override.xml.dist` file under version control to share that configuration with others but ignore the `app/etc/config-override.xml` to have it only on your machine.
	
Environment specific config
---------------------------

You can set different config based on current environment by defining several environment config files that must be placed in `app/etc` and must be named with the pattern `config-override-{env}.xml.dist` where `{env}` is the current environment (for example `config-override-dev.xml` for `dev` environment or `config-override-prod.xml.dist` for `prod` environment). Environment specific config ovverrides `app/etc/config-override.xml.dist` config explained above. To specify the current environment you must set the environment variable `MAGE_ENVIRONMENT`. For example in Apache virtual host configuration or in the `.htaccess`file you can do:

	SetEnv MAGE_ENVIRONMENT "dev"
	
Even with environment specific configuration you can use *dist* and *non-dist* files. So you can have `config-override-dev.xml.dist` which can be extended by `config-override-dev.xml`.
	

EcomDev_PHPUnit local.xml.phpunit laoding
-----------------------------------------

This extension also allows to load the `local.xml.phpunit` file of the [EcomDev_PHPUnit](https://github.com/EcomDev/EcomDev_PHPUnit) testing module. To enable this feature the `MAGE_LOAD_ECOMDEV_PHPUNIT_CONFIG` environment variable must be set to `1`.
Then using a library like [CgiHttpKernel](https://github.com/igorw/CgiHttpKernel) you can do full acceptance (or functional testing) on a test-dedicated database (which is also used by other EcomDev_PHPUnit's tests). Have a look at [Webgriffe's Functional Test Trait](https://github.com/webgriffe/functional-test-trait) for more information.

Overridden config values are shown in backend
---------------------------------------------

Overridden config values are shown in Magento's backend. Every config setting it's shown on its section. For example, if you have the following `config-override.xml` file:

```xml
<?xml version="1.0"?>
<config>
	<default>
    	<design>
        	<package>
            	<name>my-package-name</name>
          </package>
	    </design>
    </default>
</config>
```	

When you'll go to `System -> Configuration -> General -> Design` you'll find the overridden config value shown and not editable.

![image](admin-screenshot.png)

This feature improves a lot the usability of this extension.

To Do
-----

* Performance improvements
* Interdependent fields handling
* Password/encrypted fields handling


