# Configure Clean URLs on CloudPanel (No .htaccess Needed!)

## Why `.htaccess` Doesn't Work on CloudPanel
CloudPanel uses **Nginx + Varnish**, not Apache. Therefore, `.htaccess` files are completely ignored.  
You need to use **Nginx configuration** instead.

---

## Step 1: Login to CloudPanel
Login to your CloudPanel.  
Go to **Sites** and **Manage** the site you want to configure for the `.htaccess` equivalent setup, as shown in **picture 1.png**.

![Description of image](./images/picture1.png)

---

## Step 2: Open the Vhost Editor
Once opened, click on **Vhost** as shown in **picture 2.png**.

![Description of image](./images/picture2.png)

---

## Step 3: Locate the Server Block
Scroll down to the server block as shown in **picture 3.png**:

```nginx
server {
  listen 8080;
  // rest of the code
}
```

![Description of image](./images/picture3.png)

---

## Step 4: Replace with Updated Configuration
Replace it with this block:

```nginx
server {
  listen 8080;
  listen [::]:8080;
  server_name yourdomain.com www.yourdomain.com;
  {{root}}
  include /etc/nginx/global_settings;
  
  # Custom routing for clean URLs
  location /your-app-folder/ {
    try_files $uri $uri/ /your-app-folder/router.php?$query_string;
  }
  
  try_files $uri $uri/ /index.php?$args;
  index index.php index.html;

  location ~ \.php$ {
    include fastcgi_params;
    fastcgi_intercept_errors on;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    try_files $uri =404;
    fastcgi_read_timeout 3600;
    fastcgi_send_timeout 3600;
    fastcgi_param HTTPS "on";
    fastcgi_param SERVER_PORT 443;
    fastcgi_pass 127.0.0.1:{{php_fpm_port}};
    fastcgi_param PHP_VALUE "{{php_settings}}";
  }

  if (-f $request_filename) {
    break;
  }
}
```

> **Note:** Replace `/your-app-folder/` with your actual application folder path (e.g., `/Logcheap/Version_1.0/`).

---

## Step 5: Create `router.php` in Your Root Directory
Now, in your root directory, create a PHP file named **`router.php`** and have all your clean URLs defined in it:

```php
<?php
// router.php - Clean URL Router for CloudPanel
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Remove your app's base path
$path = str_replace('/your-app-folder', '', $path);
$path = trim($path, '/');

// Define routes
$routes = [
    '' => 'index.php',
    'home' => 'index.php',
    'settings' => 'settings.php',
    'signup' => 'signup.php',
    'login' => 'login.php',
    'products' => 'products.php',
    'about' => 'about.php',
    'contact' => 'contact.php',
];

// Route handling
if (isset($routes[$path])) {
    include $routes[$path];
} elseif (file_exists($path . '.php')) {
    include $path . '.php';
} else {
    http_response_code(404);
    include 'index.php'; // or 404.php
}
?>
```

---

## Step 6: Update Your Application Links

### HTML
```html
<!-- Old way -->
<a href="settings.php">Settings</a>

<!-- New way -->
<a href="settings">Settings</a>
<a href="products">Products</a>
<a href="about">About</a>
```

### JavaScript
```javascript
// Old way
settingsLink.href = "./settings.php";

// New way
settingsLink.href = "./settings";
```

---

## Step 7: Test Your Clean URLs
Example URLs:
- `https://yourdomain.com/your-app-folder/settings`
- `https://yourdomain.com/your-app-folder/products`
- `https://yourdomain.com/your-app-folder/about`

---

## Troubleshooting

### 404 Errors on Clean URLs
- ✅ Verify `router.php` exists in the correct location
- ✅ Check that paths match your folder structure
- ✅ Ensure Nginx configuration was saved

### Assets (CSS/JS) Not Loading
- ✅ Use absolute paths: `/your-app-folder/assets/style.css`
- ✅ Or update `router.php` to handle assets

### Infinite Redirects
- ✅ Avoid conflicting route definitions
- ✅ Check for PHP redirect loops

---

## Key Points to Remember
✅ **Do**  
- Use Nginx configuration  
- Create `router.php` for routing  
- Test thoroughly  

❌ **Don’t**  
- Rely on `.htaccess`  
- Expect Apache rewrite rules to work  
