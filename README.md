# Templify for PHP
Templify is a simple templating engine for PHP. It uses default PHP syntax but handles the logic of displaying, including and fetching the HTML code of template files which makes it very adaptable for any project.

## Installation
To install Templify, include it in your project using composer:
```json
{
    "require": {
        "jensostertag/templify": "1.0.0"
    }
}
```

## Usage
<details>
<summary><b>Setting the template base directory</b></summary>

Before you can use Templify, you have to specify the base directory where your template files are located. This is done by calling
```php
Templify::setConfig("TEMPLATE_BASE_DIR", __DIR__ . "/templates");
```
In this example, your template files would have to be located in the `📁 templates/` directory relative to the file where you're setting the base directory. You can also use absolute paths.
</details>

<details>
<summary><b>Display a template</b></summary>

To display a template (`template.php`) without binding any variables, call
```php
Templify::display("template.php");
```
This will display the template file `template.php` that has to be located in the template base directory you've set earlier. To have a better overview over your file structure, you can also organize your template files in subdirectories and prepend the path to the template file name.

To display a template and bind variables to it, you have to define the variables in an associative array with the keys as variable names and the values as corresponding values:
```php
$variables = [
    "foo" => "bar",
    "bar" => "foo"
];
```
Then, pass the array to the `display()` method:
```php
Templify::display("template.php", $variables);
```
In the template file, you can use them just like any other variable in PHP:
```html
<p><?php echo $foo; ?></p> <!-- bar -->
<p><?php echo $bar; ?></p> <!-- foo -->
```

Of course, you can define the variable array in the same line as you're passing it to the `display()` method, this is just for better readability.
</details>

<details>
<summary><b>Including other templates inside a template file</b></summary>

If you're developing a website where there is a lot of reused code, you can use Templify to include other template files inside a template file. This is done by calling
```php
Templify::include("template.php");
```
This will include the template file `template.php` from the `📁 includes/` directory that has to be located in the template base directory you've set earlier (let's say it was `📁 templates/`, the above code would include the `📄 templates/includes/template.php` template file). To have a better overview over your file structure, you can also organize your template files in subdirectories and prepend the path to the template file name.

Just like with the `display()` method, you can also bind variables to the included template file:
```php
Templify::include("template.php", ["foo" => "bar"]);
```
</details>

<details>
<summary><b>Fetching the HTML code of a template</b></summary>

Templify can also be used to fetch the HTML code of a template file instead of displaying it. This is done by calling
```php
$html = Templify::fetch("template.php", ["foo" => "bar"]);
```
and comes in handy if you want to send an email with a template as the body.
</details>
