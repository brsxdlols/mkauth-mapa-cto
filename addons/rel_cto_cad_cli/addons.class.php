<?php
// Reuse the installation's native addon bootstrap.
if (is_file('/opt/mk-auth/include/addons.class.php')) {
    require_once '/opt/mk-auth/include/addons.class.php';
} else {
    require_once __DIR__ . '/../caixas/addons.class.php';
}
