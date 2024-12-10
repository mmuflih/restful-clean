# Restful Clean for Lumen and Laravel

Restful Clean is a PHP package designed to provide a clean and efficient structure for building RESTful applications using the Laravel Lumen framework or Laravel framework.

## Requirements

- PHP 8.2 or higher
- Laravel Lumen Framework 10.0 or higher

## Installation

To install Restful Clean, you can use Composer:

```bash
composer require mmuflih/restful-clean
```

## Usage

This package provides a set of traits and utilities to streamline the development of RESTful APIs. Here are some key features:

- Domain-specific traits for multi-tenant applications
- Integration with Telegram Bot SDK

## Directory Structure

Here is the complete directory structure of the Restful Clean package:

```
restful-clean/
│
├── src/
│   ├── app/
│   │   ├── Commands/
│   │   │   ├── MakeModelCommand.php
│   │   │   └── RouteCommand.php
│   │   ├── Context/
│   │   │   ├── ContextException.php
│   │   │   ├── Handler.php
│   │   │   ├── HasPaginate.php
│   │   │   ├── HasSQLPaginate.php
│   │   │   ├── PagedList.php
│   │   │   └── Reader.php
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       └── ApiController.php
│   │   ├── Jobs/
│   │   │   └── TelegramJob.php
│   │   └── Traits/
│   │       ├── CodeGenerator.php
│   │       ├── HasImage.php
│   │       └── LocalTimestampsTrait.php
│   └── ...
└── ...
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
