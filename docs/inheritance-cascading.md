<!---
title: Overview
author: Caffeinated
-->

#### Inheritance system basics

**Load priority**
[`Active Theme View Folder`]() **>** [`Parent Theme View Folder`(if set)]() **>** [`Default Theme View Folder`]() **>** [`Default Laravel View Folder`]()

If you understand that, skip these points. Otherwise, more details about this:
- If you open the `index.blade.php` file, you see it @extends layout.
- If there was a `layout.blade.php` file in the same folder, it would use that one (duhh).
- However, that's not the case right now. So the Theme manager will start looking in other theme directories if they have the file (with the same relative path).
- It will first check the parent theme of 'example/main', defined in the theme.php file (or not, its optional).
- If its not there either, it will check the default theme. Which in this case, has the layout.blade.php file.
- If by any chance, the default folder doesn't have that file either, it will lastly check the standard Laravel view folder for that file.

The same goes for loading Views, Assets, etc.

#### Cascade system basics
To put it simply, every theme can have "sub-themes". Inside a theme folder, you notice the `namespaces` and `packages` folder. 

##### To create a namespace  
For example: 
- create the `lingo` folder inside the `namespaces` folder of the `example/main` theme. 
- Inside that folder, create the `assets` and `views` folder.
- Create a `myview.blade.php` inside the `view` folder
```php
View::make('lingo::myview')
```

- Create a `subdir/otherview.blade.php` inside the `view` folder
```php
View::make('lingo::subdir.otherview')
```

##### To create package
A package need to be in 2 directories. 
- So create the `foo/bar` folder inside the `packages` folder of the `example/main` theme.
- Inside that folder, create the `assets` and `views` folder.
- Create a `hakker.blade.php` inside the `view` folder
```php
View::make('foo/bar::hakker')
```

- Create a `subdir/otherhakker.blade.php` inside the `view` folder
```php
View::make('foo/bar::subdir.otherhakker')
```
