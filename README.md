﻿# Modelfiler Package

The Modelfiler package allows you to easily manage file uploads associated with any Eloquent model in Laravel. This
package supports polymorphic relationships and provides flexible configuration options for file types.

## Installation

To install the Modelfiler package, you can include it in your `composer.json` or run:

```bash
composer require elseoclub/modelfiler
```

```bash
php artisan migrate
```

## Usage

### 1. Set Up Your Model

To use the Modelfiler package, you need to add the `WithModelFiler` trait to your model. For example, if you want to use
it in the `User` model:

```php
<?php

namespace App\Models;

use Elseoclub\Modelfiler\Traits\WithModelFiler;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use WithModelFiler;

    protected bool $acceptAnyFile = true; // To accept any file type

    protected static array $fileTypes = [
        'photo' => [
            'accept'   => '.jpg,.png,.jpeg,.gif,.svg',
            'storage'  => 'public',
            'max_size' => 200048, // Size in kilobytes
            'unique'   => true,
            'path'     => 'photos', // Define the folder path for storage
        ],
        'pictures' => [
            'accept'   => '.jpg,.png,.jpeg,.gif,.svg',
            'storage'  => 'public',
            'max_size' => 1000000, // Size in kilobytes
            'unique'   => false,
            'path'     => 'pictures', // Define the folder path for storage
        ],
    ];
}
```

### 2. File Type Configuration

In your model, define the `$fileTypes` array to specify the file types you want to store. Each file type should have the
following attributes:

- **accept**: The MIME types you want to allow (e.g., `.jpg,.png,.jpeg,.gif,.svg`).
- **storage**: Where the file will be stored (default is `local`).
- **max_size**: Maximum file size in kilobytes (default is `102400`).
- **unique**: Whether to overwrite the current file if it exists (default is `false`).
- **path**: The folder path where it will be stored. If not defined, the file will be stored in the folder
  `[ModelName]/[FileTypeName]`.

### 3. File Uploads

Once you have added the necessary setup to your model, you can easily add files. For example:

```php
$user->addFile($file, 'pictures'); // Add a file to the user
```

### 4. File Management

- To get all user files:

    ```php
    $user->files;
    ```

- To get all user picture files:

    ```php
    $user->files('pictures')->get();
    ```

- To paginate user picture files:

    ```php
    $user->files('pictures')->paginate(10);
    ```

- To delete a user file:

    ```php
    $user->deleteFile($file);
    ```

### 5. Accepted MIME Types

To get the accepted MIME types for use in a file input:

```php
$accept = User::fileAccept('pictures'); // Get accepted MIME types
```

You can then use it in your Blade template like this:

```html
<input type="file" accept="{{ $accept }}">
```

### 6. Validation Rules

To add validation rules for file uploads:

```php
$rules['file'] = User::fileRule('pictures', true); // where true is required | false not required
$this->validate($rules);
```

### Conclusion

The Modelfiler package simplifies the process of managing file uploads for your models, providing a robust and flexible
way to handle file types and storage options. For more details, refer to the package documentation or the source code.

If you have any questions or need further assistance, feel free to reach out!
