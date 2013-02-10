Soflomo\Common
===
`Soflomo\Common` is a small utility module with a few helper classes. Its purpose is to provide some plugin/helper classes to provide functionalities common for many different projects.

At this moment, it provides the following classes:

 * Accept controller plugin, to check specific content types in the request
 * Attachment controller plugin, to send a file as attachment in the response
 * Version view helper, to load the version of the application from git as a cache buster in public assets

Installation
---
`Soflomo\Common` is available through composer. Add "soflomo/common" to your composer.json list. During development of `Soflomo\Common`, you can specify the latest available version:

```
"soflomo/common": "dev-master"
```

Enable the module in your `config/application.config.php` file. Add an entry `Soflomo\Common` to the list of enabled modules.

Usage
---
Example of usage is listed below for every single helper class.

### Accept controller plugin
Say you perform a delete request to the uri `/books/123`. An AJAX request might expect a 200 OK with a body message, while for a normal request you want to redirect to `/books` again.

```
// Remove the book with id 123

if ($this->accept('application/json')) {
    return new JsonModel(array(
        'status' => 'success',
        'book'   => array(
            'id' => 123
        )
    ));
}

return $this->redirect()->toRoute('books');
```

### Attachment controller plugin
You have created a pdf file and you want to let the user download that file.

```
return $this->attachment()
            ->fromFile('data/invoice/123.pdf', 'Invoice 123.pdf', 'application/pdf');
```

Available methods are `fromFile($path, $name=null, $type=null, $disposition=null)` and `fromBlob($blob, $name, $type=null, $disposition=null)`. A `fromStream()` method is not implemented yet, but is planned in a future version.

### Version view helper
For all styles, images and javascript files, a new deployed version might be changed but is available under the same URI. You want to force a reload as a cache busting mechanism, but you do not want to update the version manually each time.

This view helper loads a version description from git with `git describe --always`. For a tag it will return the tag (e.g. `v.1.0.4). If no tag is checked out (for example, on your staging environment) it loads a mixed version (e.g. `v1.0.4-14-g2414721` or when nothing has been tagged `g2414721`). The version can be appended as query string: `css/style.css?v1.0.4`:

```
<?php echo $this->headLink()
                ->appendStylesheet($this->basePath() . 'css/style.css?' . $this->version())
```