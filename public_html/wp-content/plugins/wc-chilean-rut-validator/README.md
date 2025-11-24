# WC Chilean RUT Validator

Simple Chilean RUT (Rol Único Tributario) validation for WooCommerce.

## Features

- ✅ RUT validation (Module 11 algorithm)
- ✅ Auto-formatting (adds dots and dash)
- ✅ Spanish translations
- ✅ HPOS compatible
- ✅ Simple and lightweight

## Installation

1. Upload `wc-chilean-rut-validator` folder to `/wp-content/plugins/`
2. Activate the plugin
3. Done! RUT field will appear in checkout

## How it works

- **Adds RUT field** to checkout (required)
- **Validates RUT** using Module 11 algorithm
- **Auto-formats** RUT on blur (12.345.678-9)
- **Saves** in two formats:
  - `billing_rut`: 12345678-9 (for SII/invoicing)
  - `billing_rut_formatted`: 12.345.678-9 (for display)

## Usage

### Get RUT from order

```php
$order = wc_get_order( $order_id );

// For SII/invoicing (no dots)
$rut = $order->get_meta( 'billing_rut' );
// Returns: 12345678-9

// For display (with dots)
$rut_formatted = $order->get_meta( 'billing_rut_formatted' );
// Returns: 12.345.678-9
```

### Validate RUT programmatically

```php
$validator = wc_chilean_rut_validator()->validator;

$is_valid = $validator->validate( '12.345.678-9' );
// Returns: true/false

$normalized = $validator->normalize( '12.345.678-9' );
// Returns: 12345678-9

$formatted = $validator->format( '123456789' );
// Returns: 12.345.678-9
```

## Requirements

- WordPress 5.8+
- WooCommerce 6.0+
- PHP 7.4+

## License

GPL v3 or later

## Author

Simple and effective RUT validation for Chilean e-commerce.
