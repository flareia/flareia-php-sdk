# flareia/php-sdk

SDK PHP para enviar exceptions, traces e logs ao [FlareIA](https://flareia.com.br).

## Instalar

```bash
composer require flareia/php-sdk
```

## Uso

```php
use FlareIA\Sdk\FlareIA;
use FlareIA\Sdk\FlareIAConfig;

$config = FlareIAConfig::make(getenv('FLAREIA_API_KEY'))
    ->useDefaults()
    ->applicationPath(__DIR__);

$flareIA = FlareIA::make($config);
$flareIA->start();

// … aplicação …

$flareIA->end();
```

Defina `FLAREIA_API_KEY` no ambiente (chave do projeto no painel).

Documentação: [flareia.com.br/docs](https://flareia.com.br/docs).
