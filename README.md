# UPS Shipping add-on for Bison / Statamic

This plugin provides UPS services as shipping options for Bison (Statamic e-commerce) https://builtwithbison.com/

In `_config/add-ons/bison/bison.yaml` provide settings like following:

``
shipping_method: ups_shipping
shipping_options:
  01:
    label: UPS Next Day Air
  02:
    label: UPS Second Day Air
  03:
    label: UPS Ground
``

The numbers are UPS Service Codes that can be found in Appendix E of Rating Package - Developers Guide.

01: UPS Next Day Air®
02: UPS Second Day Air®
03: UPS Ground
12: UPS Three-Day Select®
13: UPS Next Day Air Saver®
14: UPS Next Day Air® Early
59: UPS Second Day Air A.M.®
65: UPS Saver

Then also provide settings in config file for add-on.

