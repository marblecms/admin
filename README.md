# Marble CMS

<table>
<tr>
  <td width="33%"><img src="Screenshot1.png" alt="Dashboard"></td>
  <td width="33%"><img src="Screenshot2.png" alt="Item edit"></td>
  <td width="33%"><img src="Screenshot3.png" alt="Blueprint editor"></td>
</tr>
<tr>
  <td width="33%"><img src="Screenshot4.png" alt="Media library"></td>
  <td width="33%"><img src="Screenshot5.png" alt="Tree navigation"></td>
  <td width="33%"><img src="Screenshot6.png" alt="Field types"></td>
</tr>
</table>

A Laravel CMS package built around a flexible Blueprint + Field Type system. Define your content types visually, manage a hierarchical content tree, and deliver content via Blade templates or a headless JSON API.

## [→ Full Documentation](https://github.com/marblecms/admin/wiki)

## Requirements

- PHP 8.2+
- Laravel 11+
- MySQL 8+

## Installation

Use the [demo repository](https://github.com/marblecms/demo) as your starting point — it comes pre-configured with Docker, routing, and Blade templates.

```bash
git clone --recurse-submodules https://github.com/marblecms/demo
cd demo
cp .env.example .env
docker compose up -d
docker compose exec app php artisan marble:install
```

`marble:install` runs migrations, seeds the initial content tree, registers built-in field types, and publishes admin assets.

Default login after install:

```
Email:    admin@admin
Password: admin
```

## License

MIT
