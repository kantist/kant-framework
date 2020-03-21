<p align="center"><img src="https://kant.ist/images/kant.png" width="300"></p>

<p align="center">
<a href="https://packagist.org/packages/kantist/kant-framework"><img src="https://poser.pugx.org/kantist/kant-framework/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/kantist/kant-framework"><img src="https://poser.pugx.org/kantist/kant-framework/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/kantist/kant-framework"><img src="https://poser.pugx.org/kantist/kant-framework/license" alt="License"></a>
</p>

## About Kant Framework

Kant Framework is a lightweight, fast and purpose-built php framework. It is designed to create an API for microservice architectures. You can develop api services that comply with JSONAPI standards. It doesn't contain thousands of files and libraries you don't need. You can easily grasp and start developing right away. Its main features are:

- Simple, fast routing engine without any configuration
- Advanced loader class for model, library etc.
- Advanced class for parameters like JSONAPI (page, filter etc.)
- Running with composer and ready to use autoloader
- You can choose cache type (apc, file, mem or redis)
- You can choose session engine (file or db)
- And other features to make your job easier

## Install Kant Framework

Create project via `composer`;

```
$ composer create-project kantist/kant-framework
```

or you can use directly download.

## Configuration For Kant Framework

Kant Framework is ready to run in every directory you install. Here are the configurations you need to make before starting work:

`/system/config/api.php` In this section, you can make your database configurations and edit your settings such as session,  cache etc. The `default.php` file in the same directory contains the default settings, if the same settings are found in `api.php`, the `api.php` settings will apply. The missing settings run via `default.php`

You can update the `/composer.json` file when you want to install a new package dependency. You do not need to re-import autoload files. It will be installed automatically.

When the project you have developed is ready for broadcasting, It is enough to change `ENVIRONMENT` in `/index.php` to `production`, in order to close errors and other debuggers.

## Creating a New Endpoint

### Controller Architecture

To create a new endpoint, simply create a new file in the `/controller` directory. If you wish, you can organize your controllers by dividing them into folders.

The camel-cased architect is used when giving the names of the controller classes;

```
Controller{Foldername}{Filename} // i.e. ControllerAccountMember
```

If you have a file or folder that uses an underscore;

```
folder_name/file_name
```

```php
ControllerFolderNameFileName
```

You must link class names to the Controller class with extend;

```php
class ControllerAccountMember extends Controller {}
```

### Model Architecture

Creating a `model` file is similar to the `controller`

```php
class ModelAccountMember extends Model {}
```

### Rooter

The method created at the end of all of these can be automatically routed. Your link looks like this;

```
folder_name/file_name/function_name
```

If the controller has a function named `index`, you can run this function directly without the need for a function name parameter. `index` is a default function for controller.

## Database Classes

Kant Framework provides an advanced database class for your database operations. You can easily write all your queries without fear of SQL injection. To use database function, just write `$this->db`

### Query Handling

Unfortunately, we do not have a special method for the `SELECT` process yet. You have to use the direct query handling system for selection and other complex queries.

```php
$query = $this->db->query("SELECT * FROM table WHERE id > 1");

return $query->row;
// return selected row as an array

return $query->rows;
// return selected rows as an object

return $query->num_rows;
// return selected number of rows as a integer
```

### Escape

You can use escaper for your values.

```php
$this->db->escape('string');
```

You can use escaper for your identifiers (table name, column etc.).

```php
$this->db->escape_identifiers('string');
```

### Insert Operation

You must use for insert operations;

```php
$params = array(
  'id' => 5,
  'text' => 'test'
);

$this->db->insert('table', $params);
```

If you want to get insert id;

```php
$this->db->getLastId();
```

### Update Operation

You must use for update operations;

```php
$params = array(
  'text' => 'test_edit'
);

$where = array(
  'id' => 5, // For equals
  'text !=' => 'test' // other parameter you can write
);

$this->db->update('table', $params, $where);
```

The where parameter running only with `AND`. If you want to write custom query for where;

```php
$where = 'id = 5 OR text = test';
```

### Delete Operation

You must use for delete operations;

```php
$where = array(
  'id' => 5, // For equals
  'text !=' => 'test' // other parameter you can write
);

$this->db->delete('table', $where);
```

The where parameter running only with `AND`. If you want to write custom query for where;

```php
$where = 'id = 5 OR text = test';
```
