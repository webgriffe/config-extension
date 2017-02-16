Webgriffe Config
================

Magento extension that improves config system.

Installation
------------

Please, use [Magento Composer Installer](https://github.com/magento-hackathon/magento-composer-installer) and add `webgriffe/config-extension` to your dependencies.

	$ composer require webgriffe/config-extension
	    
The extension patches the main Magento's config model by putting another `Mage_Core_Model_Config` class in the `community` code pool.

We're aware that creating this kind of "monkey patch" is a Magento's coding smell but this is the only way to override Magento config logic in a reliable way.

Config override
---------------

Magento configuration is driven by database. This, sometimes, is overkill and forces us to maintain upgrade script to keep Magento envorinment aligned with features development.
So, this extension enables additional config file that overrides database configuration. The file must be at path `app/etc/config-override.xml`. For example:

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
	
Environment specific config
---------------------------

You can set different config based on current environment by defining several environment config files that must be placed in `app/etc` and must be named with the pattern `config-override-{env}.xml` where `{env}` is the current environment (for example `config-override-dev.xml` for `dev` environment or `config-override-prod.xml` for `prod` environment). Environment specific config ovverrides `app/etc/config-override.xml` config explained above. To specify the current environment you must set the environment variable `MAGE_ENVIRONMENT`. For example in Apache virtual host configuration or in the `.htaccess`file you can do:

	SetEnv MAGE_ENVIRONMENT "dev"
	

EcomDev_PHPUnit local.xml.phpunit laoding
-----------------------------------------

This extension also allows to load the `local.xml.phpunit` file of the [EcomDev_PHPUnit](https://github.com/EcomDev/EcomDev_PHPUnit) testing module. To enable this feature the `MAGE_LOAD_ECOMDEV_PHPUNIT_CONFIG` environment variable must be set to `1`.
Then using a library like [CgiHttpKernel](https://github.com/igorw/CgiHttpKernel) you can do full acceptance (or functional testing) on a test-dedicated database (which is also used by other EcomDev_PHPUnit's tests).

Overridden config values are shown in backend
---------------------------------------------

Overridden config values are shown in Magento's backend. Every config setting it's shown on its section. For example, if you have the following `config-override.xml` file:

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

When you'll go to `System -> Configuration -> General -> Design` you'll find the overridden config value shown and not editable.

![image](admin-screenshot.png)

This feature improves a lot the usability of this extension.

To Do
-----

* Dist files support: for example try to load `config-override.xml` but if it's not found try with `config-override.xml.dist` (PHPUnit/PHPCS style).
* Performance improvements
* Interdependent fields handling
* Password fields handling


