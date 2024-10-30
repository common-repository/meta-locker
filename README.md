## 1. APIs reference

### 1.1. Endpoints

All endpoints must be appended with the slug: `/wp-json/{plugin_slug}`.

#### 1.1.1. `/v1/key`

This endpoint to retrieve the Public Key.

`GET`
- Authorization: `None`
- Body Params: `None`

Example: `curl https://meta.wpclevel.com/wp-json/metalocker/v1/key`

#### 1.1.2. `/v1/license`

This endpoint to interact with licensing.

`GET`
- Authorization: `None`
- Body Params: `None`

Example: `curl https://meta.wpclevel.com/wp-json/metalocker/v1/license`

`POST`

- Authorization: `Bearer Token`
- Body Params:
    - `action`: `activate` | `deactivate`

Example: `curl https://meta.wpclevel.com/wp-json/metalocker/v1/license -d action=activate`

#### 1.1.2. `/v1/data`

This endpoint to fetch data.

`GET`

- Authorization: `Bearer Token`
- Body Params:
    - `start_date`: `Y-m-d H:i:s`
    - `end_date`: `Y-m-d H:i:s`

Example: `curl https://meta.wpclevel.com/wp-json/metalocker/v1/data -d start_date="2022-04-18 18:05:36"`

### 1.2. Bearer Token Authorization

For all endpoints that requires `Bearer Token` authorization method. The value of authorization token must be a JWT with these claims:

```
[
    'exp' => {1_hour}
    'website' => {the_target_website_url}
]
```

## 2. Data Reference

### 2.1. Tables

The plugin creates a custom table named `metalocker_sessions` with below schema:

```
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
ip VARCHAR(32) NOT NULL DEFAULT '',
agent VARCHAR(126) NOT NULL DEFAULT '',
link VARCHAR(255) NOT NULL DEFAULT '',
email VARCHAR(126) NOT NULL DEFAULT '',
balance VARCHAR(32) NOT NULL DEFAULT '',
wallet_type VARCHAR(16) NOT NULL DEFAULT '0',
wallet_address VARCHAR(126) NOT NULL DEFAULT '',
visited_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY  (id)
```

### 2.2. Settings

All settings on the Settings page are stored in the [Options Table](https://codex.wordpress.org/Database_Description#Table:_wp_options)

### 2.3 Data Flow

Data collected from users will be saved into the `metalocker_sessions` and pushed to the central server by a cron at around 12:00 everyday.
