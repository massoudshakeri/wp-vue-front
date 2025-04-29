# wp-vue-front

This WordPress plugin demonstrates how to integrate Vue.js components dynamically into WordPress pages.

## Features

- 📦 Dynamic loading of Vue components per page  
- 🔁 Sending POST requests to the server and handling responses  
- 🔐 Handling nonces for secure operations on table records  

## Installation

Like any standard WordPress plugin:

1. Clone this repository into the `wp-content/plugins` directory:

   ```bash
   git clone https://github.com/massoudshakeri/wp-vue-front.git wp-content/plugins/wp-vue-front
   ```

   Or download the ZIP file and extract it into `wp-content/plugins/wp-vue-front`.

2. Activate the plugin from the WordPress admin panel.

## Setup Vue Frontend

Navigate into the `vue-app` directory and install dependencies:

```bash
cd wp-vue-front/vue-app
npm install
```

### Development Mode (with Hot Reload and DevTools)

If WordPress is in debug mode (`define( 'WP_DEBUG', true );` in `wp-config.php`), you can run:

```bash
npm run serve
```

This enables Vue DevTools in your browser and provides a fast development experience.

### Production Mode (Build for Deployment)

If WordPress is **not** in debug mode, build the Vue app for production:

```bash
npm run build
```

This compiles and bundles the Vue components into static assets ready to be used by WordPress.

## Registering New Vue Components

All components are dynamically loaded via `vue-loader.js`. To add a new component:

1. Create your Vue component inside `vue-app/src/components`.
2. Register the new component in `vue-loader.js`.

## Using Vue Components in PHP

You can embed Vue components directly in your PHP templates by using a `div` with the `data-vue-component` attribute. You can also pass props via `data-vue-props`.

Example:

```php
<div
    data-vue-component="accounting-view"
    data-vue-props='<?= esc_attr(wp_json_encode(["entries" => $entries])) ?>'>
</div>
```

This will dynamically mount the `AccountingView.vue` component with the specified props.

---

Happy hacking! ✨
