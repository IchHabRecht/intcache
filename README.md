# TYPO3 Extension intcache

[![Latest Stable Version](https://img.shields.io/packagist/v/ichhabrecht/intcache.svg)](https://packagist.org/packages/ichhabrecht/intcache)
[![Build Status](https://img.shields.io/travis/IchHabRecht/intcache/master.svg)](https://travis-ci.org/IchHabRecht/intcache)
[![StyleCI](https://github.styleci.io/repos/190002031/shield?branch=master)](https://github.styleci.io/repos/190002031)

Turn uncachable page objects into cacheable links

Pages that contain uncachable content elements (INT-objects, uncached plugin content) are delivered with a no-cache
header to the user. This extension replaces the uncachable content elements and provides urls to fetch the content
asynchronous.

The content is replaced either as
- div-container for JavaScript/Ajax processing
- SSI block for NGINX or Apache processing
- ESI block for Varnish processing

## Installation

Simply install the extension with Composer or the [Extension Manager](https://extensions.typo3.org/extension/intcache/).

`composer require ichhabrecht/intcache`

## Usage

- include the provided static TypoScript of the intcache extension

### JavaScript

- for each element a div-container with classes `intcache intcache-item intcache-link` is rendered
- the source url is provided as `data-src` attribute
- you need to provide a script that iterates over all div's and fetches the content from provided urls

### Server Side Includes

- enable SSI support in your NGINX configuration

```
location ~ \.php$ {
    ssi on;
}
```

- change TypoScript to use SSI rendering

```
lib.intcache.format = ssi
```

### Edge Side Includes

- enable ESI support in your Varnish configuration

```
sub vcl_backend_response {
    set beresp.do_esi = true;
}
```

- change TypoScript to use ESI rendering

```
lib.intcache.format = esi
```

## Additional configuration

### intcache handling

To be able to deactivate the intcache handling on certain sites and/or domains, you can explicitly disable it using
the TypoScript setup `config.intcache = 0`.

### Templates

Simply set the `templateRootPath` TypoScript *constant* to provide an additional template path. 

```
lib.intcache.view.templateRootPath = EXT:extension/Resources/Private/Templates/Intcache/
```

By default the files `Intcache.ajax`, `Intcache.esi` or `Intcache.ssi` are used for rendering (according to your current
format setting). You may want to change the `lib.intcache.format` TypoScript setting to add your own format. 

### Content

To be able the see the current content without any further processing, the content is rendered if the TYPO3 *Development*
application context is enabled.

### Cache timeout

By default all urls send a `no-cache` header to the user. You can define own cache timeouts by using `cache_timeout`
TypoScript configuration.

Example configuration for COA_INT objects:
```
page.5 = COA_INT
page.5 {
    cache_timeout = 500 // cache this content 500 seconds
    10 = TEXT
    10.wrap = <p>|</p>
    10.value = Hello world
}
```

Example configuration for plugins:
```
tt_content.list.20.[pluginName].cache_timeout = 300
```
