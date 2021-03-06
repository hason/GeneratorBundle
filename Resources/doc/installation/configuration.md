# Configuration
---------------------------------------

[go back to Table of contents][back-to-index]

[back-to-index]: https://github.com/symfony2admingenerator/GeneratorBundle/blob/master/Resources/doc/documentation.md#1-installation

### 1. Global configurations

_TODO_

### 2. Cache configuration

`generator_cache`: __default__: `null` __type__: `string` (service name extending `Doctrine\Common\Cache\CacheProvider`)

By default, for each request matching an Admingenerated controller, the `ControllerListener` will iterate over
the filesystem to find which right generator.yml and the right `Generator` have to be used to build generated
files. This process could take some time. Thanks to this configuration, you can precise a cache provider to bypass
this process once all files are generated. The service name defined here need to extend the class
`Doctrine\Common\Cache\CacheProvider`.

Example:

```yaml
services:
    global_cache.provider:
        class: %doctrine.orm.cache.apc.class%
        public: false
        calls:
            - [ setNamespace, [ 'my_namespace' ] ]

admingenerator_generator:
    generator_cache: global_cache.provider

```

### 3. Twig section

Default configuration is:

```yaml
admingenerator_generator:
    twig:
        use_form_resources: true
        use_localized_date: false
        date_format: Y-m-d
        datetime_format: Y-m-d H:i:s
        localized_date_format: medium
        localized_datetime_format: medium
        number_format:
            decimal: 0
            decimal_point: .
            thousand_separator: ,
```

`use_form_resources`

By default, `AdmingeneratorGeneratorBundle` adds its own form theme to your application based on files 
`AdmingeneratorGeneratorBundle:Form:fields.html.twig` and `AdmingeneratorGeneratorBundle:Form:widgets.html.twig`. 
Depending on value of `admingenerator_generator.twig.use_form_resources` parameter and `twig.form.resources` one, 
you can modify this behavior:

* if `admingenerator_generator.twig.use_form_resources` is false, nothing will be changed to `twig.form.resources` value;
* if `admingenerator_generator.twig.use_form_resources` is true and `twig.form.resources` doesn't contain 
`AdmingeneratorGeneratorBundle:Form:fields.html.twig`, resources `AdmingeneratorGeneratorBundle:Form:fields.html.twig` 
and `AdmingeneratorGeneratorBundle:Form:widgets.html.twig` will be into `twig.form.resources` right after 
`form_div_layout.html.twig`. If `form_div_layout.html.twig` is not in `twig.form.resources` values will be unshifted;
* if `AdmingeneratorGeneratorBundle:Form:fields.html.twig` is already in `twig.form.resources` nothing will be changed;

This permits you to control how `AdmingeneratorGeneratorBundle` modify form theming in your application. If you want to 
use another bundle for form theming (like `MopaBoostrapBundle`) you should probably define this parameter as false.

> **Note:** take care that if you are in this case, don't forget to add `AdmingeneratorGeneratorBundle:Form:widgets.html.twig` 
if you don't provide your own implementation.

*To complete*

### 4. Full configuration

```yaml
admingenerator_generator:
    ## Global
    use_doctrine_orm: false
    use_doctrine_odm: false
    use_propel: false
    overwrite_if_exists: false
    base_admin_template: AdmingeneratorGeneratorBundle::base_admin.html.twig
    dashboard_welcome_path: ~
    login_path: ~
    logout_path: ~
    exit_path: ~
    generator_cache: ~
    ## Twig and Templates
    twig:
        use_form_resources: true
        use_localized_date: false
        date_format: Y-m-d
        datetime_format: Y-m-d H:i:s
        localized_date_format: medium
        localized_datetime_format: medium
        number_format:
            decimal: 0
            decimal_point: .
            thousand_separator: ,
    templates_dirs: []
    stylesheets: [] # array of {path: path_to_stylesheet, media: all}
    javascripts: [] # array of {path: path_to_javascript, route: route_name, routeparams: [value1, value2]}
    form_types:
        doctrine_orm:
            datetime:     datetime 
            vardatetime:  datetime 
            datetimetz:   datetime 
            date:         datetime 
            time:         time 
            decimal:      number 
            float:        number 
            integer:      integer 
            bigint:       integer 
            smallint:     integer 
            string:       text 
            text:         textarea
            entity:       entity 
            collection:   collection 
            array:        collection 
            boolean:      checkbox 
        doctrine_odm:
            datetime:     datetime 
            timestamp:    datetime 
            vardatetime:  datetime 
            datetimetz:   datetime 
            date:         datetime 
            time:         time 
            decimal:      number 
            float:        number 
            int:          integer 
            integer:      integer 
            int_id:       integer 
            bigint:       integer 
            smallint:     integer 
            id:           text 
            custom_id:    text 
            string:       text 
            text:         textarea 
            document:     document 
            collection:   collection 
            hash:         collection 
            boolean:      checkbox 
        propel:
            TIMESTAMP:    datetime 
            BU_TIMESTAMP: datetime 
            DATE:         date 
            BU_DATE:      date 
            TIME:         time 
            FLOAT:        number 
            REAL:         number 
            DOUBLE:       number 
            DECIMAL:      number 
            TINYINT:      integer 
            SMALLINT:     integer 
            INTEGER:      integer 
            BIGINT:       integer 
            NUMERIC:      integer 
            CHAR:         text 
            VARCHAR:      text 
            LONGVARCHAR:  textarea 
            BLOB:         textarea 
            CLOB:         textarea 
            CLOB_EMU:     textarea 
            model:        model 
            collection:   collection 
            PHP_ARRAY:    collection 
            ENUM:         choice 
            BOOLEAN:      checkbox 
            BOOLEAN_EMU:  checkbox 
    filter_types:
        doctrine_orm:
            text:          text
            boolean:       choice
            collection:    entity
        doctrine_odm:
            hash:          text
            text:          text
            boolean:       choice
            collection:    document
        propel:
            BOOLEAN:       choice
            BOOLEAN_EMU:   choice
            collection:    model
```
