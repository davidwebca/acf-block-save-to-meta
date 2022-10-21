# ACF Block Save To Meta [WIP]

Allows adding a setting to ACF fields to save the field's data to wp_postmeta. This is a work in progress, use at your own risk.

## WARNING

The official ACF team has mentionned that they would add this in core relatively soon. You can follow the related issue [here](https://github.com/AdvancedCustomFields/acf/issues/83).

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

Currently, this code doesn't check if multiple of the same block or if multiple fields have the same name. If multiple blocks have the option "Save to meta" enabled, or if multiple of these blocks have identical field names, the latest one added to the editor will be the prevalent data, thus the one that will be saved to meta.

A future solution might be added to never allow saving to meta when "multiple" isn't true, but it is currently impractical or impossible to implement.

Also, you'll have to use the regular "get_post_meta()" outside of the post's single (in loops etc.) to get the data because ACF currently looks up field values with "get_field" which passes through some location validation. Since our field is not associated to the post, but to a block on the post, it doesn't always return the right value, especially if you're migrating from a real post (meta) location to a block.

## Bug Reports and contributions

All issues can be reported right here on github and I'll take a look at it. Make sure to give as many details as possible since I'm working full-time and will only look at them once in a while. Feel free to add the code yourself with a pull request.

## License

This code is provided under the [MIT License](https://github.com/log1x/sage-directives/blob/master/LICENSE.md).