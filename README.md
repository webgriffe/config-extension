Webgriffe Config
================

Magento extension that improves config system.
Work in progress....

Installation
------------

Please, use [Magento Composer Installer](https://github.com/magento-hackathon/magento-composer-installer) and add `webgriffe/config-extension` to your dependencies. Also add this repository to your `composer.json`.

	"repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:webgriffe/config-extension.git"
        }
    ]
    
Then you have to enable in your Magento installation the improved Config Model. To do so, edit your `index.php` and define `config_model` option during `Mage::run()`:

	Mage::run($mageRunCode, $mageRunType, array('config_model' => 'Webgriffe_Config_Model_Config'));

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


