# Custom Fields for Backpack 4.*

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

This package provides custom fields type for the [Backpack for Laravel](https://backpackforlaravel.com/) administration panel. The ```_toggle``` fields allows admins to **_toggle_ another fields as dependency, in a prettier way**. The ```_array_ajax``` fields adds select from array not **_relationships_** only

> **This package has been created to make it easy for developers to share their custom fields with the Backpack community. You can use this package to get fields type, sure. But you can also fork it, to create a Backpack addon. For more information on how to do this, check out Backpack's addon docs.**

## Screenshots

![Backpack Toggle Field Addon](https://i.imgur.com/n4cB8sJ.gif)

## Installation

Via Composer

``` bash
composer require mahmoud-mosaad/backpack-fields
```

## Usage

Inside your custom CrudController:

```php
$this->crud->addField([
    'name' => 'from_place',
    'type'  => 'select_from_array_toggle',
    'label' => 'From Places',
    'options' => [
        1 => 'First',
        2 => 'Second',
        3 => 'Third',
        4 => 'Fourth',
        100 => 'Other',
    ],
    'allows_null' => false,
    'show_when' => [
        100 => [
            'from_place_other'
        ]
    ],
]);
$this->crud->addField([
    'name' => 'from_place_other',
    'label' => trans('backpack::base.other'),
    'type' => 'text',
]);
```

**Step 1.** Copy-paste the blade file to your directory:
```bash
# create the fields directory if it's not already there
mkdir -p resources/views/vendor/backpack/crud/fields

# copy the blade file inside the folder we created above
# if using windows replace cp with copy
cp vendor/mahmoud-mosaad/backpack-fields/src/resources/views/crud/fields/* resources/views/vendor/backpack/crud/fields/
```

**Step 3.** Uninstall this package. Since it only provides fields and you're no longer using those files, it makes no sense to have the package installed:
```bash
composer remove mahmoud-mosaad/backpack-fields
```

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/mahmoud-mosaad/backpack-fields.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/mahmoud-mosaad/backpack-fields.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/mahmoud-mosaad/backpack-fields
[link-downloads]: https://packagist.org/packages/mahmoud-mosaad/backpack-fields
[link-author]: https://www.linkedin.com/in/mahmoudmosaad50/
