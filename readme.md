# ACF Block Save To Meta [WIP]

Allows adding a setting to ACF fields to save the field's data to wp_postmeta. This is a work in progress, use at your own risk.

## Requirements

- [Composer](https://getcomposer.org/download/)

## Installation

Install via Composer:

```bash
$ composer require davidwebca/acf-block-save-to-meta
```

If your theme already uses composer, the filters will be automatically added thanks to the auto-loading and auto-instantiating class. Otherwise, if you're looking for a standalone solution, copy src/ACF_SaveToMeta.php to your theme's folder and include it in functions.php.

## Instructions

Every field you need to have saved to post meta needs to have its option checked to "yes" in the field's settings. Fields that don't have this option checked will be ignored and saved normally to the block's meta block in the post content.

## Known issues

Currently, this code always saves all the block's data as an array to avoid collision when multiple of the same block is used on the page. This was the easiest implementation I could whip out quickly, but it brings the problem of being unable to query the meta reliably since it's always serialized. Ideally there would be a check on the block's settings for "multiple" being true and adjust how it's saved by that. Or even add a custom table to link the meta to the block and post ID? 🤔

Another solution could be simply to never allow saving to meta when "multiple" isn't true.

## Bug Reports and contributions

All issues can be reported right here on github and I'll take a look at it. Make sure to give as many details as possible since I'm working full-time and will only look at them once in a while. Feel free to add the code yourself with a pull request.

## License

This code is provided under the [MIT License](https://github.com/log1x/sage-directives/blob/master/LICENSE.md).