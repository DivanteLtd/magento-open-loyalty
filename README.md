## Overview

This module creates an integration between OpenLoyalty system and Magento. Integrates into:
* Registration
* Checkout
* Customer account

Allows to use discounts and reward point from OpenLoyalty depends on your current level.

## Installation details
 
* Copy all module files under app/code/community/Divante/OpenLoyalty
* Run install scripts
* Clear cache

## Possible issues
You can get error with nesting levels. First of all check log and if it's ok, then check the authorisation. Also POS maybe defined not properly.

## Additional information

It's possible to debug integration, just turn on this option in admin panel and look to file **var/log/openloyalty_debug.log**
